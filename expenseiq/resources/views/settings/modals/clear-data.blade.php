<div
    id="clearDataModal"
    class="hidden fixed inset-0 bg-[#00000066] flex items-center justify-center z-50">

    <div class="w-[810px] rounded-[40px] bg-white border-[4px] border-[#D81B47] px-14 py-12">

        <div class="flex justify-center">

            <div class="w-24 h-24 rounded-full bg-[#FFD9DF] flex items-center justify-center">

                <i class="fa-solid fa-trash text-[#A31237] text-5xl"></i>

            </div>

        </div>

        <h2 class="mt-8 text-center text-[54px] font-extrabold text-[#5D4300]">

            Clear all data?

        </h2>

        <p class="mt-5 text-center text-[28px] leading-relaxed text-[#B9A36A]">

            All expenses, budgets, and reports will be

            <span class="font-bold">
                permanently deleted.
            </span>

            Your account stays active.

            <br>

            This cannot be undone.

        </p>

        <div class="mt-12 flex justify-center gap-8">

            <form
                action="{{ route('settings.clear-data') }}"
                method="POST">

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="w-[260px] h-[78px] rounded-2xl border-2 border-black text-[24px] font-bold hover:bg-red-50 transition">

                    Yes, clear all

                </button>

            </form>

            <button
                type="button"
                onclick="closeModal('clearDataModal')"
                class="w-[260px] h-[78px] rounded-2xl border-2 border-black text-[24px] font-bold hover:bg-gray-100 transition">

                Cancel

            </button>

        </div>

    </div>

</div>