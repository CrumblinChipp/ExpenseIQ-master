<div class="flex-1">

    <div class="mx-auto w-full max-w-[980px] py-8">

        <!-- Title -->

        <h1 class="text-[44px] font-extrabold text-[#5D4300]">
            Personal Information
        </h1>

        <p class="text-[24px] text-[#5D4300] mb-10">
            Manage your name, email, and display name.
        </p>

        <!-- Personal Information Card -->

        <div class="bg-white border border-[#E6B400] rounded-[36px] overflow-hidden">

            <!-- Nickname -->

            <div class="grid grid-cols-[220px_1fr_100px] items-center px-14 py-9">

                <h2 class="text-[26px] font-bold text-[#5D4300]">
                    Nickname
                </h2>

                <p class="text-[26px]">
                    {{ $user->nickname }}
                </p>

                <button
                    type="button"
                    onclick="openModal('nicknameModal')"
                    class="text-pink-500 font-bold text-[24px] hover:underline text-right">

                    Edit

                </button>

            </div>

            <div class="border-t border-[#E6B400]"></div>

            <!-- Full Name -->

            <div class="grid grid-cols-[220px_1fr_100px] items-center px-14 py-9">

                <h2 class="text-[26px] font-bold text-[#5D4300]">
                    Full Name
                </h2>

                <p class="text-[26px]">
                    {{ $user->first_name }} {{ $user->last_name }}
                </p>

                <button
                    type="button"
                    onclick="openModal('fullnameModal')"
                    class="text-pink-500 font-bold text-[24px] hover:underline text-right">

                    Edit

                </button>

            </div>

            <div class="border-t border-[#E6B400]"></div>

            <!-- Email -->

            <div class="grid grid-cols-[220px_1fr_100px] items-center px-14 py-9">

                <h2 class="text-[26px] font-bold text-[#5D4300]">
                    Email Address
                </h2>

                <p class="text-[24px] break-all">
                    {{ $user->email }}
                </p>

                <button
                    type="button"
                    onclick="openModal('emailModal')"
                    class="text-pink-500 font-bold text-[24px] hover:underline text-right">

                    Edit

                </button>

            </div>

        </div>

        <!-- Budget -->

        <h2 class="text-[34px] font-bold text-[#5D4300] mt-16 mb-6">
            Budget Preference
        </h2>

        <div class="bg-white border border-[#E6B400] rounded-[36px] overflow-hidden">

            <div class="grid grid-cols-[220px_1fr_100px] items-center px-14 py-9">

                <h2 class="text-[26px] font-bold text-[#5D4300]">
                    Monthly Budget
                </h2>

                <p class="text-[26px]">
                    ₱ {{ number_format($user->monthly_budget,2) }}
                </p>

                <button
                    type="button"
                    onclick="openModal('budgetModal')"
                    class="text-pink-500 font-bold text-[24px] hover:underline text-right">

                    Edit

                </button>

            </div>

        </div>

    </div>

</div>