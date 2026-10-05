<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ExpenseIQ</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body class="bg-[#F7CDDB]">

<div class="min-h-screen flex flex-col items-center justify-center px-6">

    <!-- Logo -->

    <div class="flex items-center gap-3 mb-6">

        <div class="w-14 h-14 rounded-2xl bg-[#FFB17A] flex items-center justify-center">

            <i class="fa-solid fa-wallet text-[#7B3E00] text-2xl"></i>

        </div>

        <h1 class="text-5xl font-extrabold text-[#B88900]">
            ExpenseIQ
        </h1>

    </div>

    <!-- Welcome -->

    <h2 class="text-6xl font-black text-[#B10F4A]">
        Welcome Back !
    </h2>

    <p class="mt-5 text-[#B10F4A] text-xl">
        Log in to see your latest expenses, budget status, and monthly reports.
    </p>

    <!-- Card -->

    <div class="mt-10 bg-white rounded-[30px] border-2 border-yellow-400 shadow-xl p-10 w-full max-w-2xl">

        <h3 class="text-5xl font-bold text-center text-[#5B4300]">

            Log in to your account

        </h3>

        <p class="text-center text-gray-500 mt-2 mb-8 text-lg">

            Don't have an account?

            <a href="{{ route('register') }}"
               class="text-pink-500 font-bold hover:underline">

                Sign up free

            </a>

        </p>

        @if ($errors->any())

            <div class="mb-6 rounded-xl bg-red-50 border border-red-300 p-4">

                @foreach($errors->all() as $error)

                    <p class="text-red-600">

                        {{ $error }}

                    </p>

                @endforeach

            </div>

        @endif

        <form action="{{ route('login.store') }}" method="POST">

            @csrf

            <div class="mb-6">

                <label class="block font-bold text-[#5B4300] mb-2">

                    EMAIL ADDRESS

                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full border rounded-xl px-5 py-4 focus:ring-2 focus:ring-yellow-400 outline-none">

            </div>

            <div class="mb-8">

                <label class="block font-bold text-[#5B4300] mb-2">

                    PASSWORD

                </label>

                <input
                    type="password"
                    name="password"
                    required
                    class="w-full border rounded-xl px-5 py-4 focus:ring-2 focus:ring-yellow-400 outline-none">

            </div>

            <button
                type="submit"
                class="w-full bg-white border-2 border-black rounded-2xl py-4 text-2xl font-black text-[#5B4300] hover:bg-yellow-100 transition">

                LOG IN

            </button>

        </form>

    </div>

</div>

</body>
</html>