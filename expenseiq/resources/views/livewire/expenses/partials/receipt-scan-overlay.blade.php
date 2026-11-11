<style>
    @keyframes receipt-scan {
        from { top: 0; }
        to   { top: calc(100% - 3px); }
    }

    .receipt-scan-line {
        position: absolute;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #F5C000, transparent);
        box-shadow: 0 0 14px 5px rgba(245, 192, 0, 0.55);
        animation: receipt-scan 2s ease-in-out infinite alternate;
    }

    @keyframes receipt-item-in {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: none; }
    }

    .receipt-item-in {
        animation: receipt-item-in 0.3s ease-out;
    }
</style>

{{-- wire:ignore: Livewire must not re-render this block, Alpine controls it --}}
<div
    wire:ignore
    x-show="open"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">

    <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[32px] bg-white p-8 shadow-2xl">

        {{-- ============================================================ STEP 1: TIPS --}}
        <div x-show="step === 'guide'">

            <h3 class="text-2xl font-bold text-[#5D4300]">
                Before you scan
            </h3>

            <p class="mt-1 text-[#6C6C6C]">
                A clear photo of just the right part of the receipt gives much better results.
            </p>

            <div class="mt-6 grid grid-cols-1 gap-8 md:grid-cols-5">

                {{-- Picture of a good photo --}}
                <div class="flex justify-center md:col-span-2">

                    <svg viewBox="0 0 220 300" class="h-72 w-auto" role="img" aria-label="Example of a well-framed receipt">

                        {{-- table --}}
                        <rect x="0" y="0" width="220" height="300" rx="16" fill="#E9E4D8"/>

                        {{-- receipt --}}
                        <rect x="40" y="10" width="140" height="280" fill="#FFFFFF" stroke="#D8D2C2"/>

                        {{-- header (leave out) --}}
                        <rect x="70" y="24" width="80" height="8" rx="3" fill="#CFCFCF"/>
                        <rect x="55" y="40" width="110" height="5" rx="2" fill="#E2E2E2"/>
                        <rect x="55" y="50" width="110" height="5" rx="2" fill="#E2E2E2"/>

                        {{-- the part to keep --}}
                        <rect x="46" y="68" width="128" height="148" fill="#FFF6DA" stroke="#2F9E44" stroke-width="2.5" stroke-dasharray="6 4" rx="4"/>
                        <rect x="54" y="78" width="60" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="136" y="78" width="30" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="54" y="94" width="72" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="136" y="94" width="30" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="54" y="110" width="52" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="136" y="110" width="30" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="54" y="126" width="66" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="136" y="126" width="30" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="54" y="150" width="46" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="120" y="150" width="46" height="5" rx="2" fill="#6B6B6B"/>
                        <rect x="54" y="176" width="40" height="6" rx="2" fill="#3B3B3B"/>
                        <rect x="126" y="176" width="40" height="6" rx="2" fill="#3B3B3B"/>

                        {{-- footer (leave out) --}}
                        <rect x="55" y="232" width="110" height="5" rx="2" fill="#E2E2E2"/>
                        <rect x="55" y="244" width="110" height="5" rx="2" fill="#E2E2E2"/>
                        <rect x="75" y="262" width="70" height="5" rx="2" fill="#E2E2E2"/>

                    </svg>

                </div>

                {{-- Tips --}}
                <ul class="space-y-4 md:col-span-3">

                    <li class="flex gap-3">
                        <i class="fa-solid fa-circle-check mt-1 text-green-600"></i>
                        <p class="text-[#4D3900]">
                            <span class="font-semibold">Only what matters.</span>
                            Frame the <span class="font-semibold">items and prices</span>, the
                            <span class="font-semibold">date</span> and the <span class="font-semibold">total</span>.
                            Leave out the logo, address and footer text. You can also drag a box around that part in the next step.
                        </p>
                    </li>

                    <li class="flex gap-3">
                        <i class="fa-solid fa-circle-check mt-1 text-green-600"></i>
                        <p class="text-[#4D3900]">
                            <span class="font-semibold">Flat and plain.</span>
                            Smooth out the creases and lay the receipt on a plain surface, not a patterned or wooden one.
                        </p>
                    </li>

                    <li class="flex gap-3">
                        <i class="fa-solid fa-circle-check mt-1 text-green-600"></i>
                        <p class="text-[#4D3900]">
                            <span class="font-semibold">Straight from above.</span>
                            Hold the phone parallel to the receipt so the lines are straight, not tilted.
                        </p>
                    </li>

                    <li class="flex gap-3">
                        <i class="fa-solid fa-circle-check mt-1 text-green-600"></i>
                        <p class="text-[#4D3900]">
                            <span class="font-semibold">Even light.</span>
                            Avoid shadows, glare and flash reflections. Daylight near a window works well.
                        </p>
                    </li>

                    <li class="flex gap-3">
                        <i class="fa-solid fa-circle-check mt-1 text-green-600"></i>
                        <p class="text-[#4D3900]">
                            <span class="font-semibold">Sharp text.</span>
                            Tap the screen to focus, and check that you can read every price on the photo.
                        </p>
                    </li>

                </ul>

            </div>

            <p class="mt-6 text-sm text-[#8A6A00]">
                <i class="fa-solid fa-lock"></i>
                The photo is cleaned up and read on your own device. Only the text it finds is sent for categorizing.
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">

                <label class="flex cursor-pointer items-center gap-2 text-sm text-[#6C6C6C]">
                    <input type="checkbox" x-model="dontShowAgain" class="h-4 w-4 rounded border-gray-300">
                    Don't show these tips again
                </label>

                <div class="flex gap-4">

                    <button
                        type="button"
                        x-on:click="close()"
                        class="rounded-2xl border border-gray-300 px-6 py-3 font-semibold transition hover:bg-gray-100">
                        Cancel
                    </button>

                    <button
                        type="button"
                        x-on:click="chooseFile()"
                        class="rounded-2xl bg-[#F5C000] px-6 py-3 font-semibold text-[#4D3900] transition hover:bg-yellow-400">
                        <i class="fa-solid fa-image"></i>
                        Choose photo
                    </button>

                </div>

            </div>

        </div>

        {{-- ============================================================ STEP 2: CROP --}}
        <div x-show="step === 'crop'" style="display: none;">

            <h3 class="text-2xl font-bold text-[#5D4300]">
                Select the part to scan
            </h3>

            <p class="mt-1 text-[#6C6C6C]">
                Drag a box around the <span class="font-semibold">items, the date and the total</span>.
                Leave out the logo, address, footer and the table around it.
            </p>

            <div class="mt-6 flex justify-center rounded-2xl bg-[#FFFDF5] p-4">

                <div
                    class="relative inline-block cursor-crosshair select-none overflow-hidden rounded-xl border border-[#F6E7AF]"
                    style="touch-action: none;"
                    x-on:pointerdown.prevent="cropStart($event)"
                    x-on:pointermove="cropMove($event)"
                    x-on:pointerup="cropEnd()"
                    x-on:pointercancel="cropEnd()">

                    <img
                        x-ref="cropImg"
                        x-bind:src="cropUrl"
                        alt="Your receipt photo"
                        draggable="false"
                        class="pointer-events-none block max-h-[55vh] w-auto select-none">

                    {{-- The selected box; everything outside it is dimmed --}}
                    <div
                        class="pointer-events-none absolute border-2 border-[#F5C000]"
                        x-bind:style="sel
                            ? 'left:' + (sel.x0 * 100) + '%; top:' + (sel.y0 * 100) + '%; width:' + ((sel.x1 - sel.x0) * 100) + '%; height:' + ((sel.y1 - sel.y0) * 100) + '%; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);'
                            : 'display: none;'"></div>

                </div>

            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">

                <p class="text-sm text-[#8A6A00]">
                    <span x-show="!sel">Drag on the photo to draw a box. You can redraw it any time.</span>
                    <span x-show="sel" style="display: none;">Looks good? Tap "Scan selected area".</span>
                </p>

                <div class="flex flex-wrap gap-3">

                    <button
                        type="button"
                        x-on:click="close()"
                        class="rounded-2xl border border-gray-300 px-6 py-3 font-semibold transition hover:bg-gray-100">
                        Cancel
                    </button>

                    <button
                        type="button"
                        x-on:click="scanSelection(true)"
                        class="rounded-2xl border border-[#F2C94C] bg-[#FFF6DA] px-6 py-3 font-semibold text-[#6A4B00] transition hover:bg-[#FFEFC2]">
                        Use whole photo
                    </button>

                    <button
                        type="button"
                        x-on:click="scanSelection()"
                        x-bind:disabled="!sel"
                        class="rounded-2xl bg-[#F5C000] px-6 py-3 font-semibold text-[#4D3900] transition hover:bg-yellow-400 disabled:cursor-not-allowed disabled:opacity-50">
                        Scan selected area
                    </button>

                </div>

            </div>

        </div>

        {{-- ============================================================ STEP 3: SCANNING --}}
        <div x-show="step === 'scan'" style="display: none;" class="grid grid-cols-1 gap-8 md:grid-cols-2">

            {{-- LEFT: the cleaned-up photo with the scan line and detected text lines --}}
            <div class="flex flex-col items-center rounded-2xl bg-[#FFFDF5] p-4">

                <div class="relative inline-block max-h-[60vh] overflow-hidden rounded-xl border border-[#F6E7AF]">

                    <img
                        x-show="previewUrl"
                        x-bind:src="previewUrl"
                        alt="Cleaned-up receipt"
                        class="block max-h-[60vh] w-auto transition-opacity"
                        x-bind:class="busy && stageIndex === 3 ? 'opacity-60' : 'opacity-100'">

                    {{-- Boxes drawn around the lines of text Tesseract found --}}
                    <canvas
                        x-ref="overlay"
                        class="pointer-events-none absolute inset-0 h-full w-full"></canvas>

                    {{-- Moving scan line while the image is being read --}}
                    <div
                        x-show="busy && stageIndex < 3 && previewUrl"
                        class="receipt-scan-line"></div>

                </div>

                <p x-show="previewUrl" class="mt-3 text-center text-xs text-[#8A6A00]">
                    This is the cleaned-up version of your photo that the text reader sees.
                </p>

            </div>

            {{-- RIGHT: steps, found items, buttons --}}
            <div class="flex flex-col">

                <div class="flex items-center justify-between">

                    <h3 class="text-2xl font-bold text-[#5D4300]">
                        Scanning receipt
                    </h3>

                    <span
                        x-show="busy"
                        class="text-sm text-[#8A6A00]"
                        x-text="elapsed + 's'"></span>

                </div>

                {{-- Steps --}}
                <ol class="mt-6 space-y-4">

                    <template x-for="(stage, i) in stages" x-bind:key="stage.key">

                        <li class="flex items-start gap-3">

                            <span
                                class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                x-bind:class="i < stageIndex
                                    ? 'bg-green-500 text-white'
                                    : (i === stageIndex && busy ? 'bg-[#F5C000] text-[#4D3900]' : 'bg-gray-200 text-gray-500')">

                                <i x-show="i < stageIndex" class="fa-solid fa-check"></i>
                                <i x-show="i === stageIndex && busy" class="fa-solid fa-spinner fa-spin"></i>
                                <span x-show="i > stageIndex || (i === stageIndex && !busy)" x-text="i + 1"></span>

                            </span>

                            <div class="flex-1">

                                <p
                                    class="font-semibold"
                                    x-bind:class="i <= stageIndex ? 'text-[#4D3900]' : 'text-gray-400'"
                                    x-text="stage.label"></p>

                                <p
                                    x-show="i === stageIndex && busy && detail"
                                    class="text-sm text-[#8A6A00]"
                                    x-text="detail"></p>

                                <div
                                    x-show="i === stageIndex && busy && progress > 0"
                                    class="mt-2 h-2 w-full overflow-hidden rounded-full bg-[#F6E7AF]">

                                    <div
                                        class="h-2 rounded-full bg-[#F5C000] transition-all"
                                        x-bind:style="'width: ' + progress + '%'"></div>

                                </div>

                            </div>

                        </li>

                    </template>

                </ol>

                {{-- Slow-AI hint --}}
                <p
                    x-show="busy && stageIndex === 3 && elapsed > 20"
                    class="mt-4 text-sm text-[#8A6A00]">
                    The AI is taking a bit longer than usual. Hang on...
                </p>

                {{-- Items found, revealed one by one --}}
                <div x-show="found.length" style="display: none;" class="mt-6">

                    <p class="mb-2 font-semibold text-[#4D3900]">
                        Found <span x-text="found.length"></span>
                        <span x-text="found.length === 1 ? 'item' : 'items'"></span>
                    </p>

                    <ul class="max-h-56 space-y-2 overflow-y-auto pr-1">

                        <template x-for="(item, i) in found" x-bind:key="i">

                            <li class="receipt-item-in flex items-center justify-between gap-3 rounded-xl border border-[#F6E7AF] bg-[#FFFDF5] px-4 py-2">

                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-[#4D3900]" x-text="item.name"></p>
                                    <p class="text-xs text-[#8A6A00]" x-text="item.category || 'Choose a category'"></p>
                                </div>

                                <span class="shrink-0 font-bold text-[#5D4300]" x-text="'₱' + item.amount"></span>

                            </li>

                        </template>

                    </ul>

                    <template x-if="summary && done">

                        <div class="mt-4">

                            <div class="flex items-center justify-between rounded-xl bg-[#FFF6DA] px-4 py-3">
                                <span class="font-semibold text-[#6A4B00]">Total</span>
                                <span class="text-xl font-extrabold text-[#4D3900]" x-text="'₱' + summary.total"></span>
                            </div>

                            <p x-show="summary.matches === true" class="mt-2 text-sm text-green-700">
                                <i class="fa-solid fa-circle-check"></i>
                                Matches the receipt total
                            </p>

                            <p x-show="summary.matches === false" class="mt-2 text-sm text-[#B45309]">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                The receipt shows ₱<span x-text="summary.printed_total"></span>. Please check the items.
                            </p>

                        </div>

                    </template>

                </div>

                {{-- Error --}}
                <div
                    x-show="error"
                    style="display: none;"
                    class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-600">

                    <p x-text="error"></p>

                    <p class="mt-2 text-red-500">
                        Tip: retake the photo flat, in even light, and select only the items, date and total.
                    </p>

                </div>

                {{-- Buttons --}}
                <div class="mt-auto flex flex-wrap justify-end gap-4 pt-8">

                    <button
                        type="button"
                        x-show="busy && stageIndex < 3"
                        x-on:click="cancel()"
                        class="rounded-2xl border border-gray-300 px-6 py-3 font-semibold transition hover:bg-gray-100">
                        Cancel
                    </button>

                    <button
                        type="button"
                        x-show="error"
                        style="display: none;"
                        x-on:click="close()"
                        class="rounded-2xl border border-gray-300 px-6 py-3 font-semibold transition hover:bg-gray-100">
                        Close
                    </button>

                    <button
                        type="button"
                        x-show="error"
                        style="display: none;"
                        x-on:click="showGuide()"
                        class="rounded-2xl bg-[#F5C000] px-6 py-3 font-semibold text-[#4D3900] transition hover:bg-yellow-400">
                        Try another photo
                    </button>

                    <button
                        type="button"
                        x-show="done"
                        style="display: none;"
                        x-on:click="close()"
                        class="rounded-2xl bg-[#F5C000] px-6 py-3 font-semibold text-[#4D3900] transition hover:bg-yellow-400">
                        Review items
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>