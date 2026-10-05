<div class="w-[360px] bg-white border-r border-[#E6B400] flex flex-col min-h-screen">

    <!-- Header -->

    <div class="px-8 py-7 border-b border-[#E6B400]">

        <div class="flex items-center gap-4">

            <a href="{{ route('dashboard') }}"
                class="w-10 h-10 rounded-full bg-[#7A5A00] text-white flex items-center justify-center hover:bg-[#5D4300]">

                <i class="fa-solid fa-chevron-left"></i>

            </a>

            <h1 class="text-[28px] font-extrabold text-[#5D4300]">
                Settings
            </h1>

        </div>

    </div>

    <!-- YOUR ACCOUNT -->

    <div class="mt-6">

        <p class="px-6 text-[#5D4300] font-bold text-sm uppercase">
            Your Account
        </p>

        <!-- Personal Information -->

        <a href="{{ route('settings') }}"
        class="mt-5 flex items-center gap-4 px-6 py-4
        {{ request()->routeIs('settings')
            ? 'bg-[#FFF1B3] border-y border-[#E6B400]'
            : 'hover:bg-[#FFF8DD]' }}">

            <div class="w-12 h-12 rounded-full bg-[#FFE9A8] flex items-center justify-center">

                <i class="fa-regular fa-user text-[#9B6A00] text-xl"></i>

            </div>

            <div>

                <h3 class="font-bold text-[#5D4300]">
                    Personal Information
                </h3>

                <p class="text-[#6D5C30] text-sm">
                    Name, email, nickname
                </p>

            </div>

        </a>

        <!-- Password -->

        <a href="{{ route('settings.password.page') }}"
        class="flex items-center gap-4 px-6 py-4
        {{ request()->routeIs('settings.password.page')
            ? 'bg-[#FFF1B3] border-y border-[#E6B400]'
            : 'hover:bg-[#FFF8DD]' }}">

            <div class="w-12 h-12 rounded-full bg-[#FFE9A8] flex items-center justify-center">

                <i class="fa-solid fa-lock text-[#9B6A00] text-lg"></i>

            </div>

            <div>

                <h3 class="font-bold text-[#5D4300]">
                    Password & Security
                </h3>

                <p class="text-[#6D5C30] text-sm">
                    Change your password
                </p>

            </div>

        </a>

    </div>

    <!-- MORE -->

    <div class="mt-10">

        <p class="px-6 text-[#5D4300] font-bold text-sm uppercase">
            More
        </p>

        <!-- Danger Zone -->

        <a href="{{ route('settings.danger.page') }}"
        class="mt-5 flex items-center gap-4 px-6 py-4
        {{ request()->routeIs('settings.danger.page')
            ? 'bg-[#FFF1B3] border-y border-[#E6B400]'
            : 'hover:bg-[#FFF8DD]' }}">

            <div class="w-12 h-12 rounded-full bg-[#FFD6DB] flex items-center justify-center">

                <i class="fa-solid fa-triangle-exclamation text-[#C31E47]"></i>

            </div>

            <div>

                <h3 class="font-bold text-[#B3123A]">
                    Danger Zone
                </h3>

                <p class="text-[#6D5C30] text-sm">
                    Delete data or account
                </p>

            </div>

        </a>

    </div>

</div>