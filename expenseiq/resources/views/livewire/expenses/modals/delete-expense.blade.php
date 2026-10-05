@if($showDeleteModal)

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">

    <div class="w-full max-w-md rounded-[32px] bg-white p-8 shadow-2xl">

        {{-- Warning Icon --}}
        <div class="mb-6 flex justify-center">

            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#FFE5E5]">

                <i class="fa-solid fa-trash text-4xl text-red-500"></i>

            </div>

        </div>

        {{-- Title --}}
        <h2 class="text-center text-3xl font-bold text-[#5D4300]">

            Delete Expense?

        </h2>

        {{-- Message --}}
        <p class="mt-4 text-center leading-7 text-gray-600">

            Are you sure you want to delete this expense?

            <br>

            This action cannot be undone.

        </p>

        {{-- Buttons --}}
        <div class="mt-8 flex justify-center gap-4">

            {{-- Cancel --}}
            <button
                type="button"
                wire:click="closeDeleteModal"
                class="rounded-2xl border border-gray-300 px-8 py-3 font-semibold transition hover:bg-gray-100">

                Cancel

            </button>

            {{-- Delete --}}
            <button
                type="button"
                wire:click="deleteExpense"
                class="rounded-2xl bg-red-500 px-8 py-3 font-semibold text-white transition hover:bg-red-600">

                Delete

            </button>

        </div>

    </div>

</div>

@endif