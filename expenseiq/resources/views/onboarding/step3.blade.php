<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | ExpenseIQ</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body class="bg-[#FFF8E8] flex items-center justify-center min-h-screen">

<div class="w-full max-w-4xl">

    <div class="bg-white border-4 border-yellow-400 rounded-[45px] px-20 py-16">

        <!-- ICON -->

        <div class="flex justify-center">

            <div class="w-24 h-24 rounded-full bg-[#B8F29C] border-2 border-green-600 flex items-center justify-center">

                <i class="fa-solid fa-check text-green-700 text-5xl"></i>

            </div>

        </div>

        <!-- STEP -->

        <p class="mt-8 text-center text-pink-600 font-bold text-2xl uppercase">

            Step 3 of 3 — You're All Set!

        </p>

        <!-- TITLE -->

        <h1 class="mt-5 text-center text-[56px] font-black text-[#5B4300]">

            Welcome to ExpenseIQ!

        </h1>

        <!-- DESCRIPTION -->

        <p class="mt-5 text-center text-[24px] text-[#B3A477] leading-relaxed max-w-3xl mx-auto">

            Your account is ready. Here's what you can do next to get started.

        </p>

        <!-- CARDS -->

        <div class="mt-12 space-y-4">

            <!-- CARD 1 -->

            <div class="flex items-center gap-6 bg-[#FFF2AF] border-2 border-yellow-400 rounded-3xl px-8 py-6">

                <div class="w-14 h-14 rounded-full bg-[#8B6400] flex items-center justify-center flex-shrink-0">

                    <i class="fa-solid fa-plus text-white text-2xl"></i>

                </div>

                <div>

                    <h3 class="text-[30px] font-bold text-[#5B4300]">

                        Log your first expense

                    </h3>

                    <p class="text-[22px] text-[#6E5B25]">

                        Track every peso from day one.

                    </p>

                </div>

            </div>

            <!-- CARD 2 -->

            <div class="flex items-center gap-6 bg-[#EDB4C7] border-2 border-pink-400 rounded-3xl px-8 py-6">

                <div class="w-14 h-14 flex items-center justify-center flex-shrink-0">

                    <i class="fa-solid fa-sliders text-[#6B4B00] text-4xl"></i>

                </div>

                <div>

                    <h3 class="text-[30px] font-bold text-[#5B4300]">

                        Set category budgets

                    </h3>

                    <p class="text-[22px] text-[#6E5B25]">

                        Split your budget across categories.

                    </p>

                </div>

            </div>

            <!-- CARD 3 -->

            <div class="flex items-center gap-6 bg-[#FFF2AF] border-2 border-yellow-400 rounded-3xl px-8 py-6">

                <div class="w-14 h-14 flex items-center justify-center flex-shrink-0">

                    <i class="fa-regular fa-bell text-[#6B4B00] text-4xl"></i>

                </div>

                <div>

                    <h3 class="text-[30px] font-bold text-[#5B4300]">

                        Get budget alerts

                    </h3>

                    <p class="text-[22px] text-[#6E5B25]">

                        We'll warn you when you reach your budget limit.

                    </p>

                </div>

            </div>

        </div>

        <!-- BUTTON -->

        <form action="{{ route('welcome.finish') }}" method="POST" class="mt-12">

            @csrf

            <button
                type="submit"

                class="w-full
                h-20
                rounded-2xl
                border-2
                border-black
                bg-white
                text-[#5B4300]
                text-[34px]
                font-black
                hover:bg-yellow-100
                transition">

                Go to my Dashboard

                <i class="fa-solid fa-arrow-right ml-2"></i>

            </button>

        </form>

    </div>

</div>

</body>
</html>