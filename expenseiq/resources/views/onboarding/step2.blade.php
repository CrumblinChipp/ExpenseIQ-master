<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Budget | ExpenseIQ</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body class="bg-[#FFF8E8] flex items-center justify-center min-h-screen">

<div class="w-full max-w-4xl">

    <div class="bg-white border-4 border-yellow-400 rounded-[45px] px-20 py-16">

        <!-- ICON -->

        <div class="flex justify-center">

            <div class="w-24 h-24 rounded-[28px] bg-pink-200 flex items-center justify-center">

                <i class="fa-solid fa-wallet text-[#A31444] text-5xl"></i>

            </div>

        </div>

        <!-- STEP -->

        <p class="mt-8 text-center text-pink-600 font-bold text-2xl uppercase">

            Step 2 of 3 — Monthly Budget

        </p>

        <!-- TITLE -->

        <h1 class="mt-5 text-center text-[56px] font-black text-[#5B4300] leading-tight">

            Set your monthly budget

        </h1>

        <!-- DESCRIPTION -->

        <p class="mt-5 text-center text-[24px] text-[#B3A477] leading-relaxed max-w-3xl mx-auto">

            How much do you plan to spend this month? This becomes
            your total budget limit. You can break it down by category
            on the Budget page.

        </p>

        @if ($errors->any())

            <div class="mt-10 rounded-xl bg-red-50 border border-red-300 p-4">

                <p class="text-red-600">

                    {{ $errors->first() }}

                </p>

            </div>

        @endif

        <form
            action="{{ route('welcome.step2.save') }}"
            method="POST"
            class="mt-12">

            @csrf

            <div>

                <label class="block text-[#5B4300] font-bold text-2xl mb-4">

                    TOTAL MONTHLY BUDGET (₱)

                </label>

                <input
                    type="number"
                    name="budget"
                    value="{{ old('budget') }}"
                    min="1"
                    step="0.01"
                    required

                    class="w-full
                    h-20
                    rounded-2xl
                    border
                    border-gray-300
                    px-6
                    text-2xl
                    outline-none
                    focus:ring-2
                    focus:ring-yellow-500">

            </div>

            <!-- CONTINUE -->

            <button
                type="submit"

                class="mt-10
                w-full
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

                Continue
                <i class="fa-solid fa-arrow-right ml-2"></i>

            </button>

        </form>

        <!-- BACK -->

        <a
            href="{{ route('welcome.step1') }}"

            class="mt-5
            w-full
            h-20
            rounded-2xl
            border-2
            border-black
            bg-white
            flex
            items-center
            justify-center
            text-[#5B4300]
            text-[34px]
            font-black
            hover:bg-gray-100
            transition">

            <i class="fa-solid fa-arrow-left mr-3"></i>

            Back

        </a>

    </div>

</div>

</body>
</html>