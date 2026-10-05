<div class="flex-1">

    <div class="mx-auto w-full max-w-[700px] py-8">

        <h1 class="text-[44px] font-extrabold text-[#5D4300]">
            Password & Security
        </h1>

        <p class="text-[24px] text-[#5D4300] mb-10">
            Update your account password.
        </p>

        @if(session('success'))
            <div class="mb-6 rounded-xl bg-green-100 border border-green-300 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl bg-red-100 border border-red-300 p-4 text-red-700">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('settings.password.update') }}"
            method="POST"
            class="bg-white border border-[#E6B400] rounded-[36px] p-10 space-y-8">

            @csrf

            <!-- Current Password -->

            <div>

                <label class="block font-bold text-[#5D4300] mb-2">
                    Current Password
                </label>

                <input
                    type="password"
                    name="current_password"
                    class="w-full rounded-xl border border-gray-300 px-5 py-4">

            </div>

            <!-- New Password -->

            <div>

                <label class="block font-bold text-[#5D4300] mb-2">
                    New Password
                </label>

                <div class="relative">

                    <input
                        id="settings-password"
                        data-password
                        type="password"
                        name="password"
                        class="w-full rounded-xl border border-gray-300 px-5 py-4 pr-12">

                    <button
                        type="button"
                        data-toggle-password
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-[#B88900]">

                        <i id="password-icon" class="fa-solid fa-eye"></i>

                    </button>

                </div>

                <!-- Password Strength -->

                <div id="password-strength-box" class="mt-4">

                    <p class="text-sm font-semibold">

                        Strength:

                        <span id="strength-text" class="text-red-600">

                            Weak

                        </span>

                    </p>

                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">

                        <div
                            id="strength-bar"
                            class="bg-red-500 h-2 rounded-full transition-all duration-300"
                            style="width:0%">
                        </div>

                    </div>

                    <ul
                        id="password-rules"
                        class="mt-3 text-sm text-gray-600 space-y-1">

                        <li id="rule-length">
                            • At least 8 characters
                        </li>

                        <li id="rule-upper">
                            • One uppercase letter
                        </li>

                        <li id="rule-number">
                            • One number
                        </li>

                        <li id="rule-symbol">
                            • One special character
                        </li>

                    </ul>

                </div>

            </div>

            <!-- Confirm Password -->

            <div>

                <label class="block font-bold text-[#5D4300] mb-2">
                    Confirm Password
                </label>

                <div class="relative">

                    <input
                        id="settings-confirm-password"
                        data-confirm-password
                        type="password"
                        name="password_confirmation"
                        class="w-full rounded-xl border border-gray-300 px-5 py-4 pr-12">

                    <button
                        type="button"
                        data-toggle-confirm-password
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-[#B88900]">

                        <i id="confirm-password-icon" class="fa-solid fa-eye"></i>

                    </button>

                </div>

                <p
                    id="confirm-message"
                    class="mt-2 text-sm font-medium hidden">

                </p>

            </div>

            <div class="flex justify-end">

                <button
                    class="bg-[#F3C400] hover:bg-yellow-500 px-8 py-4 rounded-xl font-bold text-[#5D4300]">

                    Save Changes

                </button>

            </div>

        </form>

    </div>

</div>