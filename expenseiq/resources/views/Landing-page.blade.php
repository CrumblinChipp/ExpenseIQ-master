<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExpenseIQ</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body class="bg-[#FFF8E8]">

<!-- NAVBAR -->

<nav class="bg-white border-b border-yellow-400 shadow-sm">

    <div class="max-w-[1700px] mx-auto px-16 py-5 flex items-center">

        <div class="flex items-center gap-4">

            <div class="w-12 h-12 rounded-xl bg-[#B59A16] flex items-center justify-center">

                <i class="fa-solid fa-wallet text-[#4D3900] text-2xl"></i>

            </div>

            <h1 class="text-5xl font-black">

                ExpenseIQ

            </h1>

        </div>

    </div>

</nav>

<!-- HERO -->

<section class="max-w-[1700px] mx-auto px-20 py-16">

    <div class="grid grid-cols-[0.85fr_1.15fr] gap-20 items-center">

        <!-- LEFT -->

        <div class="pl-10">

            <h1 class="text-[64px] leading-[72px] font-black text-[#5B4300]">

                Track your
                <br>
                Expenses.
                <br>
                Master your
                <br>
                Money.

            </h1>

            <p class="mt-8 text-[22px] leading-relaxed text-[#B3A477] max-w-[520px]">

                ExpenseIQ helps you log daily expenses,
                set monthly budgets, receive instant alerts,
                and generate spending reports—
                all in one simple dashboard.

            </p>

        </div>

        <!-- RIGHT -->

        <div>

            <h2 class="text-[46px] font-black text-center text-[#5B4300] mb-10">
                Everything you need to stay on budget
            </h2>

            @php
                $features = [

                    [
                        'icon'=>'fa-circle-plus',
                        'bg'=>'bg-yellow-300',
                        'title'=>'Expense Entry',
                        'desc'=>'Quickly log transactions with category, amount and date.'
                    ],

                    [
                        'icon'=>'fa-sliders',
                        'bg'=>'bg-pink-200',
                        'title'=>'Monthly Budget',
                        'desc'=>'Set spending limits per category and monitor your budget.'
                    ],

                    [
                        'icon'=>'fa-bell',
                        'bg'=>'bg-yellow-300',
                        'title'=>'Budget Alert',
                        'desc'=>'Receive warnings before you exceed your budget.'
                    ],

                    [
                        'icon'=>'fa-chart-column',
                        'bg'=>'bg-pink-200',
                        'title'=>'View Reports',
                        'desc'=>'Visual charts and summaries of your spending habits.'
                    ],

                    [
                        'icon'=>'fa-download',
                        'bg'=>'bg-yellow-300',
                        'title'=>'Export Reports',
                        'desc'=>'Download your reports as CSV or PDF anytime.'
                    ],

                    [
                        'icon'=>'fa-user',
                        'bg'=>'bg-pink-200',
                        'title'=>'User Accounts',
                        'desc'=>'Securely manage your account and personal settings.'
                    ],

                ];
            @endphp

            <div class="grid grid-cols-3 gap-5">

                @foreach($features as $feature)

                <div
                    class="bg-white border-2 border-yellow-400 rounded-[26px] p-5 h-[180px]
                    transition duration-300 hover:-translate-y-1 hover:shadow-xl">

                    <div class="flex items-center gap-3">

                        <div class="w-12 h-12 rounded-xl {{ $feature['bg'] }}
                            flex items-center justify-center shrink-0">

                            <i class="fa-solid {{ $feature['icon'] }}
                                text-[#5B4300] text-xl"></i>

                        </div>

                        <h3 class="text-[18px] font-bold text-[#4D3900] leading-tight">
                            {{ $feature['title'] }}
                        </h3>

                    </div>

                    <p class="mt-4 text-[15px] text-[#9D8A5E] leading-relaxed">
                        {{ $feature['desc'] }}
                    </p>

                </div>

                @endforeach

            </div>

            <!-- BUTTONS -->

            <div class="flex justify-center gap-8 mt-10">

                <a href="{{ route('register') }}"
                    class="px-12 py-4
                    rounded-2xl
                    border-2 border-pink-400
                    bg-white
                    text-[22px]
                    font-bold
                    hover:bg-pink-50
                    hover:shadow-lg
                    transition">

                    Create Account

                </a>

                <a href="{{ route('login') }}"
                    class="px-16 py-4
                    rounded-2xl
                    border-2 border-pink-400
                    bg-white
                    text-[22px]
                    font-bold
                    hover:bg-pink-50
                    hover:shadow-lg
                    transition">

                    Log In

                </a>

            </div>

        </div>

    </div>

</section>

</body>
</html>