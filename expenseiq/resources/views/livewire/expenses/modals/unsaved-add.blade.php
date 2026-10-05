@if($showUnsavedModal)

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">

    <div class="w-full max-w-md rounded-[32px] bg-white p-8 shadow-2xl">

        {{-- Warning Icon --}}
        <div class="mb-6 flex justify-center">

            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#FFF4D6]">

                <i class="fa-solid fa-triangle-exclamation text-4xl text-[#D4A100]"></i>

            </div>

        </div>

        {{-- Title --}}
        <h2 class="text-center text-3xl font-bold text-[#5D4300]">

            Unsaved Changes

        </h2>

        {{-- Message --}}
        <p class="mt-4 text-center leading-7 text-gray-600">

            You have unsaved changes.

            <br>

            Do you want to discard them?

        </p>

        {{-- Buttons --}}
        <div class="mt-8 flex justify-center gap-4">

            {{-- Continue Editing --}}
            <button
                type="button"
                wire:click="closeUnsavedModal"
                class="rounded-2xl border border-gray-300 px-8 py-3 font-semibold transition hover:bg-gray-100">

                Keep Editing

            </button>

            {{-- Discard --}}
            <button
                type="button"
                wire:click="cancelEdit"
                class="rounded-2xl bg-[#F5C000] px-8 py-3 font-semibold text-[#4D3900] transition hover:bg-yellow-400">

                Discard

            </button>

        </div>

    </div>

</div>

@endif