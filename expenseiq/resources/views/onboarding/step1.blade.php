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

            <div class="flex justify-center">

                <div class="w-24 h-24 rounded-[28px] bg-yellow-400 flex items-center justify-center">

                    <i class="fa-solid fa-user text-[#6A5200] text-5xl"></i>

                </div>

            </div>

            <p class="mt-8 text-center text-pink-600 font-bold text-2xl uppercase">

                Step 1 of 3 — Welcome

            </p>

            <h1 class="mt-5 text-center text-[56px] font-black text-[#5B4300] leading-tight">

                What should we call you?

            </h1>

            <p class="mt-5 text-center text-[24px] text-[#B3A477] leading-relaxed max-w-3xl mx-auto">

                This is the name that will appear on your dashboard and
                notifications. You can always change it later in your account
                settings.

            </p>

            @if ($errors->any())

                <div class="mt-10 rounded-xl bg-red-50 border border-red-300 p-4">

                    <p class="text-red-600">

                        {{ $errors->first() }}

                    </p>

                </div>

            @endif

            <form
                action="{{ route('welcome.step1.save') }}"
                method="POST"
                class="mt-12">

                @csrf

                <div>

                    <label class="block text-[#5B4300] font-bold text-2xl mb-4">

                        NICKNAME

                    </label>

                    <input
                        type="text"
                        name="nickname"
                        value="{{ old('nickname') }}"
                        placeholder="e.g. Cole"
                        maxlength="20"
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
                    transition
                    hover:bg-yellow-100">

                    Continue
                    <i class="fa-solid fa-arrow-right ml-2"></i>

                </button>

            </form>

            <p class="mt-10 text-center text-pink-600 text-2xl">

                Account created • Step 1 of 3

            </p>

        </div>

    </div>

</body>
</html>