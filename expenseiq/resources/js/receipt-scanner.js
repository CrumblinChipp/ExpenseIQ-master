
// Receipt scanning flow (everything up to the AI step happens in the browser):
//   1. "Before you scan" tips
//   2. The user picks a photo and drags a box around the items, the date and the total
//   3. The crop is cleaned up (lighting evened out, contrast boosted) so it is easy to read
//   4. Tesseract.js reads it (only confident lines are kept)
//   5. The recognized TEXT (never the image) is sent to Livewire's parseReceipt(), where
//      Gemini Flash-Lite picks out the items and suggests categories
//   6. The overlay closes and the expense form is already filled in

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Tesseract rates every line of text from 0 to 100. Lines below this are usually noise
// (creases, shadows, background texture) and are dropped before anything goes to the AI.
// Raise it if junk still gets through, lower it if real (faded) lines get dropped.
const MIN_LINE_CONFIDENCE = 55;

// Image cleanup settings
const TARGET_WIDTH = 1800;   // OCR reads best when the text is about this big
const MAX_UPSCALE = 3;       // small crops are enlarged, but never more than this
const MAX_PIXELS = 8000000;  // keeps phones from running out of memory
const PAPER_GAMMA = 1.6;     // higher = faded ink gets darker (and so does noise)

const GUIDE_KEY = 'receiptGuideSeen';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('receiptScanner', () => ({
        // overlay
        open: false,
        step: 'guide', // 'guide' | 'crop' | 'scan'
        busy: false,
        done: false,
        error: '',
        dontShowAgain: false,

        // crop step
        cropUrl: null,
        sel: null, // selected area as fractions of the photo: { x0, y0, x1, y1 }
        dragging: false,
        dragOrigin: null,

        // scan step
        previewUrl: null, // the cleaned-up image Tesseract reads
        stages: [
            { key: 'prepare', label: 'Cleaning up the photo' },
            { key: 'engine', label: 'Loading the text reader' },
            { key: 'read', label: 'Reading the text' },
            { key: 'items', label: 'Finding the items' },
        ],
        stageIndex: 0, // active step (stages.length = all steps finished)
        detail: '',
        progress: 0,
        elapsed: 0,
        timer: null,
        found: [],
        summary: null,

        // internals
        worker: null,
        cancelled: false,

        // ------------------------------------------------------------ opening / guide

        startFlow() {
            this.resetState();

            // Returning users who ticked "don't show again" go straight to the file picker.
            if (this.guideSeen()) {
                this.$refs.receiptFile.click();
                return;
            }

            this.open = true;
            this.step = 'guide';
        },

        showGuide() {
            this.resetState();
            this.open = true;
            this.step = 'guide';
        },

        chooseFile() {
            if (this.dontShowAgain) this.rememberGuide();
            this.$refs.receiptFile.click();
        },

        guideSeen() {
            try {
                return localStorage.getItem(GUIDE_KEY) === '1';
            } catch (_) {
                return false;
            }
        },

        rememberGuide() {
            try {
                localStorage.setItem(GUIDE_KEY, '1');
            } catch (_) {}
        },

        // ------------------------------------------------------------ choosing and cropping

        onFileChosen(event) {
            const file = event.target.files[0];
            event.target.value = ''; // lets the user pick the same file again later

            if (!file) return;

            this.resetState();
            this.open = true;

            if (!file.type.startsWith('image/')) {
                this.step = 'scan';
                this.error = 'Please choose an image file (JPG or PNG).';
                return;
            }

            this.cropUrl = URL.createObjectURL(file);
            this.step = 'crop';
        },

        pointFromEvent(e) {
            const rect = this.$refs.cropImg.getBoundingClientRect();

            return {
                x: Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width)),
                y: Math.min(1, Math.max(0, (e.clientY - rect.top) / rect.height)),
            };
        },

        cropStart(e) {
            this.dragging = true;
            this.dragOrigin = this.pointFromEvent(e);
            this.sel = null;

            if (e.currentTarget.setPointerCapture) {
                e.currentTarget.setPointerCapture(e.pointerId);
            }
        },

        cropMove(e) {
            if (!this.dragging) return;

            const p = this.pointFromEvent(e);
            const o = this.dragOrigin;

            this.sel = {
                x0: Math.min(o.x, p.x),
                y0: Math.min(o.y, p.y),
                x1: Math.max(o.x, p.x),
                y1: Math.max(o.y, p.y),
            };
        },

        cropEnd() {
            this.dragging = false;

            // Ignore accidental taps and tiny boxes.
            if (this.sel && (this.sel.x1 - this.sel.x0 < 0.08 || this.sel.y1 - this.sel.y0 < 0.04)) {
                this.sel = null;
            }
        },

        scanSelection(whole = false) {
            const img = this.$refs.cropImg;
            const nw = img.naturalWidth;
            const nh = img.naturalHeight;
            const s = whole || !this.sel ? { x0: 0, y0: 0, x1: 1, y1: 1 } : this.sel;

            return this.runScan(img, {
                x: Math.round(s.x0 * nw),
                y: Math.round(s.y0 * nh),
                w: Math.max(1, Math.round((s.x1 - s.x0) * nw)),
                h: Math.max(1, Math.round((s.y1 - s.y0) * nh)),
            });
        },

        // ------------------------------------------------------------ the scan itself

        async runScan(img, region) {
            this.step = 'scan';
            this.busy = true;
            this.error = '';
            this.elapsed = 0;
            this.timer = setInterval(() => this.elapsed++, 1000);

            try {
                // 1) Clean up the photo
                this.setStage(0, 'Cropping, evening out the lighting and boosting contrast');
                await sleep(50); // let the screen update before the heavy work

                const canvas = this.prepare(img, region);
                await this.showProcessed(canvas);
                if (this.cancelled) return;

                // 2) Load Tesseract (the first scan also downloads the language data)
                this.setStage(1, 'Starting up');
                const { createWorker } = await import('tesseract.js');
                this.worker = await createWorker('eng', 1, {
                    logger: (m) => this.onTesseract(m),
                });
                if (this.cancelled) return;

                try {
                    await this.worker.setParameters({
                        tessedit_pageseg_mode: '6', // one block of text: right for a cropped receipt
                        preserve_interword_spaces: '1',
                        user_defined_dpi: '300',
                    });
                } catch (e) {
                    console.warn('Could not set Tesseract parameters:', e);
                }

                // 3) Read the text
                this.setStage(2, 'Recognizing the text');
                const { data } = await this.worker.recognize(canvas, {}, { blocks: true });
                await this.worker.terminate();
                this.worker = null;
                if (this.cancelled) return;

                // Keep only the lines Tesseract is reasonably sure about
                const lines = this.goodLines(data);
                const text = lines.length ? lines.map((line) => line.text.trim()).join('\n') : data.text;

                // Draw boxes around those lines (runs alongside the AI step)
                this.showLines(lines, canvas);

                // 4) Ask the server (Gemini Flash-Lite) to pick out the items
                this.setStage(3, 'Asking the AI to pick out the items');
                const result = await this.callServer(text);
                if (this.cancelled) return;

                if (!result || !result.ok) {
                    this.error = (result && result.message) || "Couldn't read the items from this receipt.";
                    return;
                }

                // Reveal the items one by one, then go back to the form
                this.stageIndex = this.stages.length;
                this.detail = '';
                this.progress = 0;

                for (const item of result.items) {
                    this.found.push(item);
                    await sleep(260);
                }

                this.summary = {
                    total: result.total,
                    printed_total: result.printed_total,
                    matches: result.matches,
                };
                this.done = true;

                await sleep(2200);
                this.close();
            } catch (e) {
                console.error('Receipt scan failed:', e);

                if (!this.cancelled) {
                    this.error =
                        e && e.message === 'timeout'
                            ? 'The AI step is taking too long. Please try again in a moment.'
                            : 'Something went wrong while reading the receipt. Please try another photo.';
                }
            } finally {
                clearInterval(this.timer);
                this.busy = false;

                if (this.worker) {
                    try {
                        await this.worker.terminate();
                    } catch (_) {}
                    this.worker = null;
                }
            }
        },

        setStage(index, detail) {
            this.stageIndex = index;
            this.detail = detail;
            this.progress = 0;
        },

        // Tesseract reports what it's doing; show it on the right step.
        onTesseract(m) {
            if (this.cancelled) return;

            const pct = Math.round((m.progress || 0) * 100);

            if (m.status === 'recognizing text') {
                this.stageIndex = 2;
                this.progress = pct;
                return;
            }

            if (this.stageIndex <= 1) {
                this.stageIndex = 1;
                this.progress = pct;
                this.detail = this.friendly(m.status);
            }
        },

        friendly(status) {
            const names = {
                'loading tesseract core': 'Loading the text reader',
                'initializing tesseract': 'Starting the text reader',
                'loading language traineddata': 'Downloading language data (first scan only)',
                'initializing api': 'Almost ready',
            };

            return names[status] || status;
        },

        // Calls Livewire, but never waits forever.
        callServer(text) {
            const timeout = new Promise((_, reject) =>
                setTimeout(() => reject(new Error('timeout')), 110000)
            );

            return Promise.race([this.$wire.parseReceipt(text), timeout]);
        },

        // ------------------------------------------------------------ OCR results

        // Drops low-confidence lines and lines with no real letters or digits.
        goodLines(data) {
            const all = this.extractLines(data);

            const kept = all.filter(
                (line) =>
                    line.bbox &&
                    (line.confidence ?? 0) >= MIN_LINE_CONFIDENCE &&
                    /[A-Za-z0-9]{2,}/.test(line.text || '')
            );

            console.info(`Receipt OCR: kept ${kept.length} of ${all.length} lines (confidence >= ${MIN_LINE_CONFIDENCE})`);

            return kept;
        },

        // Works with both Tesseract.js v5 (data.lines) and v6 (data.blocks -> paragraphs -> lines).
        extractLines(data) {
            if (Array.isArray(data.lines) && data.lines.length) return data.lines;

            const lines = [];
            (data.blocks || []).forEach((block) =>
                (block.paragraphs || []).forEach((paragraph) =>
                    (paragraph.lines || []).forEach((line) => lines.push(line))
                )
            );

            return lines;
        },

        // Highlights each kept line of text on the preview, one after another.
        async showLines(lines, canvas) {
            try {
                const overlay = this.$refs.overlay;

                if (!overlay || !lines.length) return;

                overlay.width = canvas.width;
                overlay.height = canvas.height;

                const ctx = overlay.getContext('2d');
                ctx.lineWidth = Math.max(2, canvas.width / 400);
                ctx.strokeStyle = '#F5C000';
                ctx.fillStyle = 'rgba(245, 192, 0, 0.18)';

                const delay = Math.min(30, 1500 / lines.length);

                for (const line of lines) {
                    if (this.cancelled || !this.open) return;

                    const { x0, y0, x1, y1 } = line.bbox;
                    ctx.fillRect(x0, y0, x1 - x0, y1 - y0);
                    ctx.strokeRect(x0, y0, x1 - x0, y1 - y0);

                    await sleep(delay);
                }
            } catch (e) {
                // Purely cosmetic, never let it break the scan.
                console.warn('Could not draw the text boxes:', e);
            }
        },

        // ------------------------------------------------------------ image cleanup

        // Crops the chosen area, scales it to a good size for OCR, then cleans it up.
        prepare(img, region) {
            let scale = Math.min(MAX_UPSCALE, TARGET_WIDTH / region.w);

            if (region.w * scale * region.h * scale > MAX_PIXELS) {
                scale = Math.sqrt(MAX_PIXELS / (region.w * region.h));
            }

            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(region.w * scale));
            canvas.height = Math.max(1, Math.round(region.h * scale));

            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, region.x, region.y, region.w, region.h, 0, 0, canvas.width, canvas.height);

            this.enhance(canvas);

            return canvas;
        },

        // Makes the paper evenly white and the ink dark:
        //   1. grayscale
        //   2. measure how bright the paper is around every spot (shadows, uneven light)
        //   3. divide by that, so shadows and gradients disappear
        //   4. stretch the contrast and darken faded ink
        enhance(canvas) {
            const w = canvas.width;
            const h = canvas.height;
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            const image = ctx.getImageData(0, 0, w, h);
            const px = image.data;
            const total = w * h;

            // 1) Grayscale
            const gray = new Uint8ClampedArray(total);
            for (let i = 0, p = 0; i < total; i++, p += 4) {
                gray[i] = 0.299 * px[p] + 0.587 * px[p + 1] + 0.114 * px[p + 2];
            }

            // 2) Average brightness around each spot, worked out on a 4x smaller copy (much faster)
            const BLOCK = 4;
            const sw = Math.max(1, Math.ceil(w / BLOCK));
            const sh = Math.max(1, Math.ceil(h / BLOCK));
            const sums = new Float32Array(sw * sh);
            const counts = new Uint16Array(sw * sh);

            for (let y = 0; y < h; y++) {
                const row = Math.floor(y / BLOCK) * sw;
                for (let x = 0; x < w; x++) {
                    const idx = row + Math.floor(x / BLOCK);
                    sums[idx] += gray[y * w + x];
                    counts[idx]++;
                }
            }

            const iw = sw + 1;
            const integral = new Float64Array(iw * (sh + 1));
            for (let y = 1; y <= sh; y++) {
                let rowSum = 0;
                for (let x = 1; x <= sw; x++) {
                    const i = (y - 1) * sw + (x - 1);
                    rowSum += sums[i] / counts[i];
                    integral[y * iw + x] = integral[(y - 1) * iw + x] + rowSum;
                }
            }

            const radius = Math.max(6, Math.round(sw / 8));
            const paper = new Float32Array(sw * sh);
            for (let y = 0; y < sh; y++) {
                const y0 = Math.max(0, y - radius);
                const y1 = Math.min(sh - 1, y + radius);
                for (let x = 0; x < sw; x++) {
                    const x0 = Math.max(0, x - radius);
                    const x1 = Math.min(sw - 1, x + radius);
                    const sum =
                        integral[(y1 + 1) * iw + (x1 + 1)] -
                        integral[y0 * iw + (x1 + 1)] -
                        integral[(y1 + 1) * iw + x0] +
                        integral[y0 * iw + x0];
                    paper[y * sw + x] = sum / ((x1 - x0 + 1) * (y1 - y0 + 1));
                }
            }

            // 3) Divide by the paper brightness
            const flat = new Uint8ClampedArray(total);
            const hist = new Uint32Array(256);
            for (let y = 0; y < h; y++) {
                const row = Math.floor(y / BLOCK) * sw;
                for (let x = 0; x < w; x++) {
                    const idx = y * w + x;
                    const local = Math.max(30, paper[row + Math.floor(x / BLOCK)]);
                    flat[idx] = Math.min(255, (gray[idx] / local) * 235);
                    hist[flat[idx]]++;
                }
            }

            // 4) Stretch the contrast: darkest 1% becomes black, the paper becomes white
            const percentile = (p) => {
                const target = total * p;
                let acc = 0;
                for (let v = 0; v < 256; v++) {
                    acc += hist[v];
                    if (acc >= target) return v;
                }
                return 255;
            };

            const ink = percentile(0.01);
            const bright = Math.max(percentile(0.92), ink + 20);

            const lut = new Uint8ClampedArray(256);
            for (let v = 0; v < 256; v++) {
                const t = Math.min(1, Math.max(0, (v - ink) / (bright - ink)));
                lut[v] = Math.pow(t, PAPER_GAMMA) * 255;
            }

            for (let i = 0, p = 0; i < total; i++, p += 4) {
                const v = lut[flat[i]];
                px[p] = v;
                px[p + 1] = v;
                px[p + 2] = v;
                px[p + 3] = 255;
            }

            ctx.putImageData(image, 0, 0);
        },

        // Shows the cleaned-up image in the overlay (the text boxes line up with it).
        async showProcessed(canvas) {
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.9));

            if (!blob) return;

            if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = URL.createObjectURL(blob);
        },

        // ------------------------------------------------------------ housekeeping

        resetState() {
            this.open = false;
            this.step = 'guide';
            this.busy = false;
            this.done = false;
            this.error = '';
            this.sel = null;
            this.dragging = false;
            this.stageIndex = 0;
            this.detail = '';
            this.progress = 0;
            this.elapsed = 0;
            this.found = [];
            this.summary = null;
            this.cancelled = false;

            if (this.cropUrl) {
                URL.revokeObjectURL(this.cropUrl);
                this.cropUrl = null;
            }

            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
            }

            const overlay = this.$refs.overlay;
            if (overlay) overlay.getContext('2d').clearRect(0, 0, overlay.width, overlay.height);
        },

        cancel() {
            this.cancelled = true;

            if (this.worker) {
                try {
                    this.worker.terminate();
                } catch (_) {}
            }

            this.close();
        },

        close() {
            this.open = false;
        },
    }));
});