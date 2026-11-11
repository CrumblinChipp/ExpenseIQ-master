@if($showEditModal)

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">

    <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[32px] bg-white p-8 shadow-2xl">

        {{-- Header --}}
        <div class="mb-8 flex items-center justify-between">

            <h2 class="text-3xl font-bold text-[#5D4300]">
                Edit Expense
            </h2>

            <button
                type="button"
                wire:click="$set('showEditUnsavedModal', true)"
                class="flex h-10 w-10 items-center justify-center rounded-full transition hover:bg-gray-100">

                <i class="fa-solid fa-xmark text-xl text-gray-500"></i>

            </button>

        </div>

        <form wire:submit="updateExpense" class="space-y-6">

            <div class="grid grid-cols-2 gap-6">

                {{-- Title --}}
                <div>

                    <label class="mb-2 block font-semibold text-[#4D3900]">
                        Title
                    </label>

                    <input
                        type="text"
                        wire:model.live="description"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 focus:border-[#F2C94C] focus:outline-none">

                    @error('description')
                        <p class="mt-2 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Date --}}
                <div>

                    <label class="mb-2 block font-semibold text-[#4D3900]">
                        Date
                    </label>

                    <input
                        type="date"
                        wire:model.live="expense_date"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 focus:border-[#F2C94C] focus:outline-none">

                    @error('expense_date')
                        <p class="mt-2 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>

            {{-- Items (category + amount per item, total at the end) --}}
            @include('livewire.expenses.partials.items-editor')

            {{-- Notes --}}
            <div>

                <label class="mb-2 block font-semibold text-[#4D3900]">
                    Notes
                </label>

                <textarea
                    rows="5"
                    wire:model.live="notes"
                    class="w-full rounded-2xl border border-gray-300 px-5 py-3 focus:border-[#F2C94C] focus:outline-none"></textarea>

            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-4">

                <button
                    type="button"
                    wire:click="$set('showEditUnsavedModal', true)"
                    class="rounded-2xl border border-gray-300 px-8 py-3 font-semibold transition hover:bg-gray-100">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="rounded-2xl bg-[#F5C000] px-8 py-3 font-semibold text-[#4D3900] transition hover:bg-yellow-400">

                    Save Changes

                </button>

            </div>

        </form>

    </div>

</div>

@endif