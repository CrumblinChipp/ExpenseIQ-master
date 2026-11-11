
document.addEventListener('alpine:init', () => {
    window.Alpine.data('categoryCombobox', (uid) => ({
        uid,
        open: false,
        text: '', // what is shown in the box
        typed: false, // false until the user types; until then the whole list is shown
        highlight: -1, // keyboard-highlighted row
        busy: false, // waiting for the server to check / create a category
        message: '',
        suggestion: null, // { id, name, typed } when the server says "Did you mean ...?"

        // ------------------------------------------------------------ data

        get options() {
            return this.$wire.categoryOptions || [];
        },

        // Rows are found by uid, not by position, so removing a row above never mixes them up.
        index() {
            return this.$wire.items.findIndex((row) => row.uid === this.uid);
        },

        row() {
            return this.$wire.items[this.index()];
        },

        nameFor(id) {
            const found = this.options.find((c) => String(c.id) === String(id));
            return found ? found.name : '';
        },

        get filtered() {
            const q = this.text.trim().toLowerCase();

            if (!this.typed || q === '') return this.options;

            return this.options.filter((c) => c.name.toLowerCase().includes(q));
        },

        get canAdd() {
            const q = this.text.trim().toLowerCase();

            return this.typed && q.length >= 2 && !this.options.some((c) => c.name.toLowerCase() === q);
        },

        // Keeps the box showing the chosen category, including when the server changes it
        // (AI suggestion, receipt scan, new category). Runs via x-effect.
        sync() {
            const id = this.row() ? this.row().category_id : '';
            const known = this.options.length; // read so this re-runs when the list changes

            if (!this.open && known >= 0) this.text = this.nameFor(id);
        },

        // ------------------------------------------------------------ opening / closing

        openList() {
            if (this.open) return;

            this.open = true;
            this.typed = false;
            this.highlight = -1;
            this.message = '';
            this.suggestion = null;

            this.$nextTick(() => this.$refs.input.select());
        },

        onInput() {
            this.open = true;
            this.typed = true;
            this.highlight = -1;
            this.message = '';
            this.suggestion = null;
        },

        close() {
            this.open = false;
            this.typed = false;
            this.suggestion = null;
            this.message = '';
            // sync() puts the chosen category's name back in the box
        },

        // ------------------------------------------------------------ choosing

        pick(category) {
            this.$wire.$set(`items.${this.index()}.category_id`, category.id);
            this.close();
            this.text = category.name;
        },

        useSuggestion() {
            this.pick({ id: this.suggestion.id, name: this.suggestion.name });
        },

        // Asks the server to add the typed name as a new category (or to find the existing one).
        async addNew(force = false) {
            const name = this.text.trim();

            if (!name || this.busy) return;

            this.busy = true;
            this.message = '';
            if (!force) this.suggestion = null;

            try {
                const result = await this.$wire.addCategory(this.uid, name, force);

                if (result.status === 'suggest') {
                    this.suggestion = result;
                    return;
                }

                if (result.status === 'invalid') {
                    this.message = result.message;
                    return;
                }

                // 'created' or 'exists': the server has already selected it for this item
                this.close();
                this.text = result.name;
            } catch (e) {
                console.error('Could not add the category:', e);
                this.message = 'Could not add the category. Please try again.';
            } finally {
                this.busy = false;
            }
        },

        // ------------------------------------------------------------ keyboard

        onKeydown(e) {
            const rows = this.filtered.length + (this.canAdd ? 1 : 0);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.open = true;
                this.highlight = Math.min(this.highlight + 1, rows - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.highlight = Math.max(this.highlight - 1, 0);
            } else if (e.key === 'Enter') {
                e.preventDefault();

                if (this.highlight >= 0 && this.highlight < this.filtered.length) {
                    this.pick(this.filtered[this.highlight]);
                } else if (this.canAdd) {
                    this.addNew();
                } else if (this.filtered.length === 1) {
                    this.pick(this.filtered[0]);
                }
            } else if (e.key === 'Escape') {
                this.close();
            }
        },
    }));
});