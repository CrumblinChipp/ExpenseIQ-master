<div>

    <div class="mb-3 flex items-center justify-between">

        <label class="font-semibold text-[#4D3900]">
            Items
        </label>

        <button
            type="button"
            wire:click="addItem"
            class="rounded-xl border border-[#F2C94C] bg-[#FFF6DA] px-4 py-2 text-sm font-semibold text-[#6A4B00] transition hover:bg-[#FFEFC2]">

            <i class="fa-solid fa-plus"></i>
            Add item

        </button>

    </div>

    <div class="space-y-4">

        @foreach($items as $i => $item)

            <div
                wire:key="item-{{ $item['uid'] }}"
                class="grid grid-cols-12 items-start gap-3">

                {{-- Item name (AI suggests a category when you leave this field) --}}
                <div class="col-span-12 md:col-span-4">

                    <input
                        type="text"
                        wire:model.blur="items.{{ $i }}.description"
                        placeholder="Item name..."
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 outline-none transition focus:border-[#F2C94C]">

                    <p
                        wire:loading
                        wire:target="items.{{ $i }}.description"
                        class="mt-1 text-xs text-[#8A6A00]">
                        Suggesting a category...
                    </p>

                    @error('items.'.$i.'.description')
                        <p class="mt-1 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Category: pick from the list, or type a new one --}}
                <div
                    class="relative col-span-12 md:col-span-4"
                    x-data="categoryCombobox('{{ $item['uid'] }}')"
                    x-effect="sync()"
                    x-on:click.outside="close()"
                    x-on:focusout="if (! $el.contains($event.relatedTarget)) close()">

                    <div class="relative">

                        <input
                            type="text"
                            x-ref="input"
                            x-model="text"
                            x-on:focus="openList()"
                            x-on:click="openList()"
                            x-on:input="onInput()"
                            x-on:keydown="onKeydown($event)"
                            autocomplete="off"
                            placeholder="Choose or type a category..."
                            class="w-full rounded-2xl border border-gray-300 px-5 py-3 pr-10 outline-none transition focus:border-[#F2C94C]">

                        <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>

                    </div>

                    {{-- Dropdown --}}
                    <div
                        x-show="open"
                        style="display: none;"
                        class="absolute left-0 right-0 z-30 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-[#F2C94C] bg-white p-2 shadow-lg">

                        <template x-for="(category, n) in filtered" x-bind:key="category.id">

                            <button
                                type="button"
                                tabindex="-1"
                                x-on:mousedown.prevent
                                x-on:click="pick(category)"
                                class="flex w-full items-center justify-between rounded-xl px-4 py-2 text-left text-[#4D3900] hover:bg-[#FFF6DA]"
                                x-bind:class="highlight === n ? 'bg-[#FFF6DA]' : ''">

                                <span x-text="category.name"></span>

                                <i
                                    x-show="row() && String(category.id) === String(row().category_id)"
                                    class="fa-solid fa-check text-green-600"></i>

                            </button>

                        </template>

                        <p
                            x-show="typed && filtered.length === 0 && !canAdd"
                            class="px-4 py-2 text-sm text-gray-500">
                            No matching categories
                        </p>

                        {{-- "Did you mean ...?" from the AI --}}
                        <div
                            x-show="suggestion"
                            style="display: none;"
                            class="mt-1 rounded-xl border border-[#F5C542] bg-[#FFF6DA] p-3">

                            <p class="text-sm text-[#6A4B00]">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                Did you mean
                                <span class="font-semibold" x-text="suggestion ? suggestion.name : ''"></span>?
                            </p>

                            <div class="mt-2 flex flex-wrap gap-2">

                                <button
                                    type="button"
                                    tabindex="-1"
                                    x-on:mousedown.prevent
                                    x-on:click="useSuggestion()"
                                    class="rounded-lg bg-[#F5C000] px-3 py-1.5 text-sm font-semibold text-[#4D3900] hover:bg-yellow-400">
                                    Yes, use <span x-text="suggestion ? suggestion.name : ''"></span>
                                </button>

                                <button
                                    type="button"
                                    tabindex="-1"
                                    x-on:mousedown.prevent
                                    x-on:click="addNew(true)"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold text-[#4D3900] hover:bg-gray-100">
                                    No, add "<span x-text="text.trim()"></span>"
                                </button>

                            </div>

                        </div>

                        {{-- Add the typed name as a new category --}}
                        <button
                            type="button"
                            tabindex="-1"
                            x-show="canAdd && !suggestion"
                            style="display: none;"
                            x-on:mousedown.prevent
                            x-on:click="addNew()"
                            x-bind:disabled="busy"
                            class="mt-1 flex w-full items-center gap-2 rounded-xl border border-dashed border-[#F2C94C] px-4 py-2 text-left text-[#6A4B00] hover:bg-[#FFF6DA] disabled:opacity-60"
                            x-bind:class="highlight === filtered.length ? 'bg-[#FFF6DA]' : ''">

                            <i class="fa-solid fa-plus" x-show="!busy"></i>
                            <i class="fa-solid fa-spinner fa-spin" x-show="busy" style="display: none;"></i>

                            <span x-show="!busy">Add "<span class="font-semibold" x-text="text.trim()"></span>" as a new category</span>
                            <span x-show="busy" style="display: none;">Checking...</span>

                        </button>

                        <p
                            x-show="message"
                            style="display: none;"
                            class="px-4 py-2 text-sm text-red-500"
                            x-text="message"></p>

                    </div>

                    @if(($item['category_source'] ?? 'manual') !== 'manual')
                        <p class="mt-1 text-xs text-[#8A6A00]">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            {{ $item['category_source'] === 'history' ? 'From your past expenses' : 'AI suggested' }}
                        </p>
                    @endif

                    @error('items.'.$i.'.category_id')
                        <p class="mt-1 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Amount --}}
                <div class="col-span-10 md:col-span-3">

                    <input
                        type="number"
                        step="0.01"
                        wire:model.live.debounce.400ms="items.{{ $i }}.amount"
                        placeholder="0.00"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 outline-none transition focus:border-[#F2C94C]">

                    @error('items.'.$i.'.amount')
                        <p class="mt-1 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Remove --}}
                <div class="col-span-2 md:col-span-1">

                    <button
                        type="button"
                        wire:click="removeItem({{ $i }})"
                        @disabled(count($items) === 1)
                        class="flex h-12 w-full items-center justify-center rounded-2xl bg-[#FFD8D8] transition hover:bg-[#FFBABA] disabled:cursor-not-allowed disabled:opacity-40">

                        <i class="fa-solid fa-trash text-red-600"></i>

                    </button>

                </div>

            </div>

        @endforeach

    </div>

    @error('items')
        <p class="mt-2 text-sm text-red-500">
            {{ $message }}
        </p>
    @enderror

    {{-- Total --}}
    <div class="mt-5 flex items-center justify-end gap-4 rounded-2xl bg-[#FFF6DA] px-6 py-4">

        <span class="font-semibold text-[#6A4B00]">
            Total
        </span>

        <span class="text-2xl font-extrabold text-[#4D3900]">
            ₱{{ number_format($total, 2) }}
        </span>

    </div>

    {{-- Compare with the total printed on a scanned receipt --}}
    @if($printedTotal)

        @if(abs($printedTotal - $total) < 0.01)

            <p class="mt-2 text-right text-sm text-green-700">
                <i class="fa-solid fa-circle-check"></i>
                Matches the receipt total (₱{{ number_format($printedTotal, 2) }})
            </p>

        @else

            <p class="mt-2 text-right text-sm text-[#B45309]">
                <i class="fa-solid fa-triangle-exclamation"></i>
                The receipt shows ₱{{ number_format($printedTotal, 2) }}, but your items add up to ₱{{ number_format($total, 2) }}.
                Tax, discounts or a misread item may explain the difference.
            </p>

        @endif

    @endif

</div>