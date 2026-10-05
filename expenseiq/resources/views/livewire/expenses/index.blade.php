<div class="space-y-6">

    {{-- Success Message --}}
    @if(session()->has('success'))
        <div
            class="rounded-2xl border border-green-300 bg-green-100 px-5 py-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div>
        <h1 class="text-[48px] font-extrabold text-[#4D3900] leading-none">
            Expenses
        </h1>

        <p class="mt-2 text-lg text-[#6C6C6C]">
            Track your daily expenses
        </p>
    </div>

    {{-- Add Expense Card --}}
    <div
        class="rounded-[30px] border-2 border-[#F2C94C] bg-white p-8 shadow-sm">

        <div class="flex items-center justify-between">

            <h2 class="text-3xl font-bold text-[#5D4300]">
                Add New Expenses
            </h2>

        </div>

        {{-- Unsaved Banner --}}
        @if(
            $description ||
            $amount ||
            $category_id ||
            $notes
        )

            <div
                class="mt-6 flex items-center justify-between rounded-2xl border border-[#F5C542] bg-[#FFF6DA] px-5 py-4">

                <div class="flex items-center gap-3">

                    <i class="fa-solid fa-circle-exclamation text-[#D39B00]"></i>

                    <div>

                        <p class="font-semibold text-[#6A4B00]">
                            Unsaved changes
                        </p>

                        <p class="text-sm text-[#8A6A00]">
                            Save before leaving this page.
                        </p>

                    </div>

                </div>

            </div>

        @endif

        {{-- Form --}}
        <form
            wire:submit="saveExpense"
            class="mt-8 space-y-6">

            <div class="grid grid-cols-2 gap-6">

                {{-- Title --}}
                <div>

                    <label
                        class="mb-2 block font-semibold text-[#4D3900]">

                        Title

                    </label>

                    <input
                        type="text"
                        wire:model.live="description"
                        placeholder="Enter expense title..."
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 outline-none transition focus:border-[#F2C94C]">

                    @error('description')
                        <p class="mt-2 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Category --}}
                <div>

                    <label
                        class="mb-2 block font-semibold text-[#4D3900]">

                        Category

                    </label>

                    <select
                        wire:model.live="category_id"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 outline-none transition focus:border-[#F2C94C]">

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

                    <label
                        class="mb-2 block font-semibold text-[#4D3900]">

                        Amount

                    </label>

                    <input
                        type="number"
                        step="0.01"
                        wire:model.live="amount"
                        placeholder="0.00"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 outline-none transition focus:border-[#F2C94C]">

                    @error('amount')
                        <p class="mt-2 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- Date --}}
                <div>

                    <label
                        class="mb-2 block font-semibold text-[#4D3900]">

                        Date

                    </label>

                    <input
                        type="date"
                        wire:model.live="expense_date"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 outline-none transition focus:border-[#F2C94C]">

                    @error('expense_date')
                        <p class="mt-2 text-sm text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>

            {{-- Notes --}}
            <div>

                <label
                    class="mb-2 block font-semibold text-[#4D3900]">

                    Notes

                </label>

                <textarea
                    rows="4"
                    wire:model.live="notes"
                    placeholder="Add notes..."
                    class="w-full rounded-2xl border border-gray-300 px-5 py-3 outline-none transition focus:border-[#F2C94C]"></textarea>

            </div>

            {{-- Buttons --}}
            <div class="flex justify-end gap-4 pt-2">

                <button
                    type="button"
                    wire:click="$set('showUnsavedModal', true)"
                    class="rounded-2xl border border-gray-300 px-8 py-3 font-semibold transition hover:bg-gray-100">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="rounded-2xl bg-[#F5C000] px-8 py-3 font-semibold text-[#4D3900] transition hover:bg-yellow-400">

                    Save Expense

                </button>

            </div>

        </form>

    </div>

    {{-- Expense Record Card --}}
    <div class="rounded-[30px] border-2 border-[#F2C94C] bg-white p-8 shadow-sm">

        <div class="mb-6 flex items-center justify-between">

            <h2 class="text-3xl font-bold text-[#5D4300]">
                Expense Record
            </h2>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full">

<thead>

    <tr class="border-b border-[#F2C94C] text-[#5D4300]">

        <th class="py-4 text-left font-bold">
            Date
        </th>

        <th class="text-left font-bold">
            Category
        </th>

        <th class="text-left font-bold">
            Title
        </th>

        <th class="text-right font-bold">
            Amount
        </th>

        <th class="text-center font-bold w-[140px]">
            Action
        </th>

    </tr>

</thead>

<tbody>

    @forelse($expenses as $expense)

        <tr
            wire:key="expense-{{ $expense->id }}"
            class="border-b border-[#F6E7AF] hover:bg-[#FFFDF5] transition">

            {{-- Date --}}
            <td class="py-5">

                <span class="text-[#4D3900]">

                    {{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}

                </span>

            </td>

            {{-- Category --}}
            <td>

                <span
                    class="inline-flex items-center rounded-full bg-[#FFF2C7] px-4 py-2 text-sm font-semibold text-[#7A5A00]">

                    {{ $expense->category->category_name }}

                </span>

            </td>

            {{-- Title --}}
            <td>

                <div class="font-semibold text-[#4D3900]">

                    {{ $expense->description }}

                </div>

                @if($expense->notes)

                    <div class="mt-1 text-sm text-gray-500">

                        {{ $expense->notes }}

                    </div>

                @endif

            </td>

            {{-- Amount --}}
            <td class="text-right">

                <span class="font-bold text-[#5D4300]">

                    ₱{{ number_format($expense->amount,2) }}

                </span>

            </td>

            {{-- Actions --}}
            <td>

                <div class="flex justify-center gap-3">

                    {{-- Edit --}}
                    <button
                        type="button"
                        wire:click="editExpense({{ $expense->id }})"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FFEFC2] transition hover:bg-[#FFD95C]">

                        <i class="fa-solid fa-pen text-[#6A4B00]"></i>

                    </button>

                    {{-- Delete --}}
                    <button
                        type="button"
                        wire:click="confirmDelete({{ $expense->id }})"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FFD8D8] transition hover:bg-[#FFBABA]">

                        <i class="fa-solid fa-trash text-red-600"></i>

                    </button>

                </div>

            </td>

        </tr>

    @empty

        <tr>

            <td
                colspan="5"
                class="py-14 text-center text-gray-500">

                <div class="flex flex-col items-center">

                    <i class="fa-solid fa-wallet mb-3 text-4xl text-[#E5C85C]"></i>

                    <p class="text-lg font-semibold text-[#6A4B00]">

                        No expenses yet

                    </p>

                    <p class="mt-1 text-sm text-gray-500">

                        Start by adding your first expense.

                    </p>

                </div>

            </td>

        </tr>

    @endforelse

</tbody>

</table>

</div>

</div>

@include('livewire.expenses.modals.edit-expense')
@include('livewire.expenses.modals.unsaved-add')
@include('livewire.expenses.modals.unsaved-edit')
@include('livewire.expenses.modals.delete-expense')