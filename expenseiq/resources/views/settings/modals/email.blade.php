<!-- Email Modal -->

<div
    id="emailModal"
    class="expenseiq-modal hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">

    <div class="w-[650px] bg-white rounded-[36px] border border-[#E6B400] overflow-hidden">

        <!-- Header -->

        <div class="px-10 py-7 border-b border-[#E6B400] flex items-center justify-between">

            <h2 class="text-[34px] font-extrabold text-[#5D4300]">

                Edit Email Address

            </h2>

            <button
                type="button"
                onclick="closeModal('emailModal')">

                <i class="fa-solid fa-xmark text-3xl text-[#5D4300]"></i>

            </button>

        </div>

        <form
            action="{{ route('settings.profile') }}"
            method="POST">

            @csrf

            <!-- Hidden Fields -->

            <input
                type="hidden"
                name="type"
                value="email">
                
            <!-- Body -->

            <div class="px-10 py-8">

                <label class="block text-[#5D4300] font-bold mb-3">

                    Email Address

                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    class="w-full border border-[#D6D6D6] rounded-2xl px-5 py-4 text-xl focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    required>

            </div>

            <!-- Footer -->

            <div class="px-10 pb-8 flex justify-end gap-4">

                <button
                    type="button"
                    onclick="closeModal('emailModal')"
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