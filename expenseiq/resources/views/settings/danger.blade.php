<div class="flex-1">

    <div class="mx-auto w-full max-w-[900px] py-8">

        <h1 class="text-[44px] font-extrabold text-[#B3123A]">
            Danger Zone
        </h1>

        <p class="text-[24px] text-[#5D4300] mb-10">
            These actions are permanent and cannot be undone.
        </p>

        <!-- Clear Data -->

        <div class="bg-white border border-[#E85B7A] rounded-[30px] p-10 mb-8">

            <div class="flex gap-6">

                <div class="w-20 h-20 rounded-2xl bg-[#FFD6DB] flex items-center justify-center">

                    <i class="fa-solid fa-trash text-[#B3123A] text-4xl"></i>

                </div>

                <div class="flex-1">

                    <h2 class="text-[34px] font-bold text-[#5D4300]">
                        Clear all data
                    </h2>

                    <p class="text-[24px] text-[#5D4300] leading-relaxed mt-2">
                        Permanently deletes all your expenses, budgets,
                        and reports. Your account will remain active.
                    </p>

                    <button
                        type="button"
                        onclick="openModal('clearDataModal')"
                        class="mt-8 px-10 py-4 rounded-xl border-2 border-black font-bold text-xl hover:bg-[#FFF3F5] transition">

                        Clear all data

                    </button>

                </div>

            </div>

        </div>

        <!-- Delete Account -->

        <div class="bg-white border border-[#E85B7A] rounded-[30px] p-10">

            <div class="flex gap-6">

                <div class="w-20 h-20 rounded-2xl bg-[#FFD6DB] flex items-center justify-center">

                    <i class="fa-solid fa-user-minus text-[#B3123A] text-4xl"></i>

                </div>

                <div class="flex-1">

                    <h2 class="text-[34px] font-bold text-[#5D4300]">
                        Delete account
                    </h2>

                    <p class="text-[24px] text-[#5D4300] leading-relaxed mt-2">
                        Permanently deletes your account and all associated
                        data. You will be logged out immediately.
                    </p>

                    <button
                        type="button"
                        onclick="openModal('deleteAccountModal')"
                        class="mt-8 px-10 py-4 rounded-xl border-2 border-black font-bold text-xl hover:bg-[#FFF3F5] transition">

                        Delete my account

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>