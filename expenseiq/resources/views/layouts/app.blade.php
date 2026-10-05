<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'ExpenseIQ' }}</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="font-poppins bg-[#FFF9DD]">

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-60 bg-[#FDE68A] flex flex-col justify-between p-6 shadow-lg">

        <div>

            {{-- Logo --}}
            <div class="flex items-center gap-3 mb-10">

                <div class="w-14 h-14 rounded-full bg-yellow-400 flex items-center justify-center">

                    <i class="fa-solid fa-wallet text-2xl text-[#6B4F00]"></i>

                </div>

                <div>

                    <h1 class="text-2xl font-bold text-[#6B4F00]">
                        ExpenseIQ
                    </h1>

                    <p class="text-xs text-gray-600">
                        Personal Finance Tracker
                    </p>

                </div>

            </div>

            {{-- Navigation --}}
            <nav class="space-y-3">

                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 p-3 rounded-xl transition
                    {{ request()->routeIs('dashboard') ? 'bg-yellow-300 font-semibold shadow-sm' : 'hover:bg-yellow-300' }}">

                    <i class="fa-solid fa-house w-5"></i>

                    Dashboard

                </a>

                <a href="{{ route('expenses.index') }}"
                    class="flex items-center gap-3 p-3 rounded-xl transition
                    {{ request()->routeIs('expenses.*') ? 'bg-yellow-300 font-semibold shadow-sm' : 'hover:bg-yellow-300' }}">

                    <i class="fa-solid fa-wallet w-5"></i>

                    Expenses

                </a>

                <a href="{{ route('budget.index') }}"
                    class="flex items-center gap-3 p-3 rounded-xl transition
                    {{ request()->routeIs('budget.*') ? 'bg-yellow-300 font-semibold shadow-sm' : 'hover:bg-yellow-300' }}">

                        <i class="fa-solid fa-sliders w-5"></i>

                        Budget

                    </a>

                {{-- Budget button ilalagay natin kapag ready na ang routes --}}
                {{-- Reports --}}
                <a href="{{ route('reports.index') }}"
                    class="flex items-center gap-3 p-3 rounded-xl transition
                    {{ request()->routeIs('reports.*') ? 'bg-yellow-300 font-semibold shadow-sm' : 'hover:bg-yellow-300' }}">

                    <i class="fa-solid fa-chart-column w-5"></i>

                    Reports

                </a>

                {{-- Settings --}}
                <a href="{{ route('settings') }}"
                    class="flex items-center gap-3 p-3 rounded-xl transition
                    {{ request()->routeIs('settings.*') ? 'bg-yellow-300 font-semibold shadow-sm' : 'hover:bg-yellow-300' }}">

                    <i class="fa-solid fa-gear w-5"></i>

                    Settings

                </a>

            </nav>

        </div>

        {{-- Logout --}}
        <form action="{{ route('logout') }}" method="POST">

            @csrf

            <button
                class="w-full flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl">

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </button>

        </form>

    </aside>

    {{-- Main Content --}}
    <main class="flex-1 px-8 py-6 overflow-y-auto max-w-[1400px] mx-auto">
        
        @isset($slot)
        {{ $slot }}
        @else
            @yield('content')
        @endisset

    </main>

</div>

@livewireScripts
@stack('scripts')

</body>
</html>