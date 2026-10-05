<!-- Password Modal -->

<div
    id="passwordModal"
    class="expenseiq-modal hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">

    <div class="w-[650px] bg-white rounded-[36px] border border-[#E6B400] overflow-hidden">

        <!-- Header -->

        <div class="px-10 py-7 border-b border-[#E6B400] flex items-center justify-between">

            <h2 class="text-[34px] font-extrabold text-[#5D4300]">

                Change Password

            </h2>

            <button
                type="button"
                onclick="closeModal('passwordModal')">

                <i class="fa-solid fa-xmark text-3xl text-[#5D4300]"></i>

            </button>

        </div>

        <form
            action="{{ route('settings.password.update') }}"
            method="POST">

            @csrf
            

            <div class="px-10 py-8 space-y-6">

                <!-- Current Password -->

                <div>

                    <label class="block text-[#5D4300] font-bold mb-3">

                        Current Password

                    </label>

                    <input
                        type="password"
                        name="current_password"
                        class="w-full border border-[#D6D6D6] rounded-2xl px-5 py-4 text-xl focus:outline-none focus:ring-2 focus:ring-yellow-400"
                        required>

                </div>

                <!-- New Password -->

                <div>

                    <label class="block text-[#5D4300] font-bold mb-3">

                        New Password

                    </label>

                    <input
                        type="password"
                        name="password"
                        class="w-full border border-[#D6D6D6] rounded-2xl px-5 py-4 text-xl focus:outline-none focus:ring-2 focus:ring-yellow-400"
                        required>

                </div>

                <!-- Confirm Password -->

                <div>

                    <label class="block text-[#5D4300] font-bold mb-3">

                        Confirm Password

                    </label>

                    <input
                        type="password"
                        name="password_confirmation"
                        class="w-full border border-[#D6D6D6] rounded-2xl px-5 py-4 text-xl focus:outline-none focus:ring-2 focus:ring-yellow-400"
                        required>

                </div>

            </div>

            <!-- Footer -->

            <div class="px-10 pb-8 flex justify-end gap-4">

                <button
                    type="button"
                    onclick="closeModal('passwordModal')"
                    class="px-8 py-3 rounded-xl border border-gray-300 font-semibold hover:bg-gray-100">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="bg-[#F3C400] hover:bg-yellow-500 text-[#5D4300] font-bold px-8 py-3 rounded-xl">

                    Save Changes

                </button>

            </div>

        </form>

    </div>

</div>