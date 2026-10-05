<div
    id="deleteAccountModal"
    class="hidden fixed inset-0 bg-[#00000066] flex items-center justify-center z-50">

    <div class="w-[820px] rounded-[40px] bg-white border-[4px] border-[#D81B47] px-14 py-12">

        <div class="flex justify-center">

            <div class="w-24 h-24 rounded-full bg-[#FFD9DF] flex items-center justify-center">

                <i class="fa-solid fa-user-minus text-[#A31237] text-5xl"></i>

            </div>

        </div>

        <h2 class="mt-8 text-center text-[52px] font-extrabold text-[#5D4300]">

            Delete your account?

        </h2>

        <p class="mt-5 text-center text-[26px] leading-relaxed text-[#B9A36A]">

            This will permanently delete your account and all data.

            You will be logged out immediately.

        </p>

        <div class="mt-10">

            <label class="block text-[22px] font-bold text-[#5D4300] mb-3">

                TYPE "DELETE" TO CONFIRM

            </label>

            <input
                id="delete-confirm-input"
                type="text"
                class="w-full h-16 rounded-2xl border border-gray-300 px-5 text-xl focus:outline-none focus:ring-2 focus:ring-red-400">

        </div>

        <div class="mt-10 flex justify-center gap-8">

            <form
                action="{{ route('settings.delete') }}"
                method="POST">

                @csrf
                @method('DELETE')

                <button
                    id="delete-account-button"
                    disabled
                    class="w-[260px] h-[78px] rounded-2xl border-2 border-black text-[24px] font-bold disabled:opacity-40 disabled:cursor-not-allowed hover:bg-red-50 transition">

                    Delete account

                </button>

            </form>

            <button
                type="button"
                onclick="closeModal('deleteAccountModal')"
                class="w-[260px] h-[78px] rounded-2xl border-2 border-black text-[24px] font-bold hover:bg-gray-100 transition">

                Cancel

            </button>

        </div>

    </div>

</div>