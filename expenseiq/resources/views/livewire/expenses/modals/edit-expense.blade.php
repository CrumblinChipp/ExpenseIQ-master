@if($showEditModal)

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">

    <div class="w-full max-w-2xl rounded-[32px] bg-white p-8 shadow-2xl">

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

        <form wire:submit="updateExpense">

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

                {{-- Category --}}
                <div>

                    <label class="mb-2 block font-semibold text-[#4D3900]">
                        Category
                    </label>

                    <select
                        wire:model.live="category_id"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 focus:border-[#F2C94C] focus:outline-none">

                        <option value="">
                            Select Category
                        </option>

                        @foreach($categories as $category)

                            <option value="{{ $category->id }}">
                                {{ $category->category_name }}
                            </option>

                        @endforeach

                    </select>

                    @error('category_id')
                        <p class="mt-2 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Amount --}}
                <div>

                    <label class="mb-2 block font-semibold text-[#4D3900]">
                        Amount
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        wire:model.live="amount"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 focus:border-[#F2C94C] focus:outline-none">

                    @error('amount')
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

            {{-- Notes --}}
            <div class="mt-6">

                <label class="mb-2 block font-semibold text-[#4D3900]">
                    Notes
                </label>

                <textarea
                    rows="5"
                    wire:model.live="notes"
                    class="w-full rounded-2xl border border-gray-300 px-5 py-3 focus:border-[#F2C94C] focus:outline-none"></textarea>

            </div>

            {{-- Footer --}}
            <div class="mt-8 flex justify-end gap-4">

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