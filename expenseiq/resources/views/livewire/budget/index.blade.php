@extends('layouts.app')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div>

        <h1 class="text-[42px] font-bold text-[#4D3900]">
            Budget Setting
        </h1>

        <p class="text-gray-500">
            Allocate your monthly budget for each category.
        </p>

    </div>

    @if ($errors->has('budget'))

        <div class="bg-red-100 border border-red-300 rounded-2xl px-5 py-4">

            <p class="text-red-600 font-semibold">

                <i class="fa-solid fa-circle-exclamation mr-2"></i>

                {{ $errors->first('budget') }}

            </p>

        </div>

    @endif

    {{-- Budget Warning --}}

    @if(count($warnings))

    <div class="bg-[#F9C7CF] border border-[#E89BAA] rounded-2xl px-6 py-5">

        <div class="flex items-start gap-4">

            <div
                class="w-11 h-11 rounded-full
                bg-[#F39BAE]
                flex items-center justify-center
                shrink-0">

                <i class="fa-solid fa-triangle-exclamation text-white"></i>

            </div>

            <div>

                <h3 class="font-bold text-[#8C2740] text-lg">

                    Budget Limit Warning

                </h3>

                <p class="text-[#8C2740] leading-relaxed">

                    @foreach($warnings as $index => $warning)

                        <strong>

                            {{ $warning['name'] }}

                        </strong>

                        ({{ $warning['percentage'] }}%)

                        @if(!$loop->last)
                            ,
                        @endif

                    @endforeach

                    {{ count($warnings) > 1 ? 'are' : 'is' }}

                    nearing

                    {{ count($warnings) > 1 ? 'their' : 'its' }}

                    budget limit.

                </p>

            </div>

        </div>

    </div>

    @endif
    
    <div class="bg-white rounded-[28px] border-2 border-yellow-400 overflow-hidden">

        <form action="{{ route('budget.save') }}" method="POST">

            @csrf

            @php
                $totalSpent = 0;
            @endphp

            <table class="w-full">

                <thead>

                <tr class="bg-[#F3C400] text-[#5D4300]">

                    <th class="text-left px-8 py-4 text-2xl">
                        Category
                    </th>

                    <th class="text-center text-2xl">
                        Budget
                    </th>

                    <th class="text-center text-2xl">
                        Spent
                    </th>

                    <th class="text-center text-2xl">
                        Remaining
                    </th>

                </tr>

                </thead>

                <tbody>

                @foreach($categories as $category)

                    @php

                        $budget = $budgets[$category->id] ?? null;

                        $amount = $budget->budget_amount ?? '';

                        $spentAmount = $spent[$category->id] ?? 0;

                        $remaining = ($budget->budget_amount ?? 0) - $spentAmount;

                        $totalSpent += $spentAmount;

                    @endphp

                    <tr class="border-b border-yellow-300">

                        <td class="px-8 py-5 text-[22px]">

                            {{ $category->category_name }}

                        </td>

                        <td class="text-center">

                            <input
                                type="hidden"
                                name="category_id[]"
                                value="{{ $category->id }}">

                            <input
                                type="number"
                                name="budget_amount[]"
                                value="{{ $amount }}"
                                min="0"
                                step="0.01"
                                class="w-56 h-14 rounded-xl border border-gray-300 text-center text-2xl focus:outline-none focus:ring-2 focus:ring-yellow-400">

                        </td>

                        <td class="text-center text-2xl">

                            ₱{{ number_format($spentAmount,2) }}

                        </td>

                        <td class="text-center text-2xl">

                            ₱{{ number_format($remaining,2) }}

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

            <!-- Footer -->

            <div class="border-t border-yellow-300 px-8 py-6 flex justify-between items-center">

                <div class="space-y-2 text-lg font-semibold text-[#5D4300]">

                    <p>
                        Monthly Budget:
                        <span class="font-bold">
                            ₱{{ number_format($monthlyBudget,2) }}
                        </span>
                    </p>

                    <p>
                        Spent Budget:
                        <span class="font-bold">
                            ₱{{ number_format($allocatedBudget,2) }}
                        </span>
                    </p>

                    <p>
                        Remaining Budget:
                        <span class="font-bold">
                            ₱{{ number_format($remainingBudget,2) }}
                        </span>
                    </p>

                </div>

                <button
                    type="submit"
                    class="bg-[#F3C400] hover:bg-yellow-500 text-[#4D3900] font-bold px-10 py-4 rounded-xl transition">

                    Save Budget

                </button>

            </div>

        </form>

    </div>

</div>

@endsection