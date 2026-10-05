@extends('layouts.app')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div>

        <h1 class="text-[48px] font-extrabold text-[#4D3900] leading-none">

            {{ strtoupper(now()->format('F Y')) }}

        </h1>

        <p class="text-[#6C6C6C] text-lg mt-2">

            Monthly Overview

        </p>

    </div>

  <!-- Dashboard Warning -->

    @if(count($dashboard['warnings']))

    <div class="bg-[#F9C7CF] border border-[#E89BAA] rounded-2xl px-6 py-5">

        <div class="flex items-start gap-4">

            <div
                class="w-11 h-11
                rounded-full
                bg-[#F39BAE]
                flex items-center
                justify-center
                shrink-0">

                <i class="fa-solid fa-triangle-exclamation text-white"></i>

            </div>

            <div>

                <h3 class="font-bold text-[#8C2740] text-lg">

                    Budget Limit Warning

                </h3>

                <p class="text-[#8C2740] leading-relaxed">

                    @foreach($dashboard['warnings'] as $warning)

                        <strong>{{ $warning['name'] }}</strong>

                        ({{ $warning['percentage'] }}%)

                        @if(!$loop->last)
                            ,
                        @endif

                    @endforeach

                    {{ count($dashboard['warnings']) > 1 ? 'are' : 'is' }}

                    near

                    {{ count($dashboard['warnings']) > 1 ? 'their' : 'its' }}

                    budget limit.

                </p>

            </div>

        </div>

    </div>

    @endif

    <!-- Monthly Summary -->

    <div class="bg-white border-2 border-yellow-400 rounded-[28px] p-6">

        <h2 class="text-3xl font-bold text-[#5D4300] mb-8">
            Monthly Summary
        </h2>

        <div class="grid grid-cols-12 gap-10 items-center">

            <!-- Donut -->
            <div class="col-span-4 flex justify-center">

                <div class="w-[210px] h-[210px]">
                    <canvas id="summaryChart"></canvas>
                </div>

            </div>

            <!-- Statistics -->
            <div class="col-span-8">

                <div class="grid grid-cols-2 gap-y-10 gap-x-10">

                    <!-- Total Spent -->
                    <div class="flex gap-4">

                        <div class="w-6 h-6 bg-[#FFD54A] mt-2"></div>

                        <div>

                            <p class="uppercase text-[#7B6100] font-bold text-sm">
                                Total Spent
                            </p>

                            <h2 class="text-3xl font-black">
                                ₱{{ number_format($dashboard['totalExpenses'],2) }}
                            </h2>

                        </div>

                    </div>

                    <!-- Budget Remaining -->
                    <div class="flex gap-4">

                        <div class="w-6 h-6 bg-[#F4719B] mt-2"></div>

                        <div>

                            <p class="uppercase text-[#7B6100] font-bold text-sm">
                                Budget Remaining
                            </p>

                            <h2 class="text-3xl font-black text-pink-600">
                                ₱{{ number_format($dashboard['remainingBudget'],2) }}
                            </h2>

                        </div>

                    </div>

                    <!-- Transactions -->
                    <div class="flex gap-4">

                        <div class="w-6 h-6 bg-[#F5C000] mt-2"></div>

                        <div>

                            <p class="uppercase text-[#7B6100] font-bold text-sm">
                                Transactions
                            </p>

                            <h2 class="text-3xl font-black">
                                {{ $dashboard['expenseCount'] }}
                            </h2>

                        </div>

                    </div>

                    <!-- Largest Spent -->
                    <div class="flex gap-4">

                        <div class="w-6 h-6 bg-[#98004B] mt-2"></div>

                        <div>

                            <p class="uppercase text-[#7B6100] font-bold text-sm">
                                Largest Spent
                            </p>

                            <h2 class="text-3xl font-black">
                                ₱{{ number_format($dashboard['recentExpenses']->max('amount') ?? 0,2) }}
                            </h2>

                            <p class="text-pink-600 text-sm">
                                Monthly Record
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
    <!-- Bottom -->

    <div class="bg-white border-2 border-yellow-400 rounded-[28px] p-6">

        <div class="grid grid-cols-12 gap-8">

            <!-- Budget Process -->
            <div class="col-span-8">

                <h2 class="text-2xl font-bold text-[#5D4300] mb-5">
                    Budget Process
                </h2>

                @php
                $categories = [
                    'Housing',
                    'Food & Drinks',
                    'Grocery',
                    'Transportation',
                    'Health',
                    'Entertainment'
                ];
                @endphp

                @foreach($dashboard['budgetProgress'] as $index => $category)

                    <div class="mb-5">

                        <p class="text-lg font-medium text-[#3D2E00] mb-2">
                            {{ $category['name'] }}
                        </p>

                        <div class="h-2.5 rounded-full bg-pink-100">

                            <div
                                class="h-2.5 rounded-full {{ $index % 2 == 0 ? 'bg-pink-500' : 'bg-yellow-400' }}"
                               style="width: {{ $category['percentage'] }}%">
                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

            <!-- Category Chart -->
            <div class="w-[440px] h-[440px]">
                <canvas id="categoryChart"></canvas>
            </div>

        </div>

    </div>
@push('scripts')

<script>

document.addEventListener("DOMContentLoaded", function () {

    // Monthly Summary
    new Chart(document.getElementById('summaryChart'), {

        type: 'doughnut',

        data: {

            labels: [
                'Spent',
                'Remaining'
            ],

            datasets: [{

                data: [

                    {{ $dashboard['totalExpenses'] }},

                    {{ max($dashboard['remainingBudget'], 0) }}

                ],

                backgroundColor: [

                    '#F5C000',

                    '#F58FB5'

                ],

                borderWidth: 0,

                borderRadius: 8,

                hoverOffset: 5,

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            cutout: '60%',

            plugins: {

                legend: {

                    display: false

                }

            }

        }

    });



    // Category Chart
    new Chart(document.getElementById('categoryChart'), {

        type: 'doughnut',

        data: {

            labels: @json($dashboard['chartLabels']),

            datasets: [{

                data: @json($dashboard['chartData']),

                backgroundColor: [

                    '#F7B6D2',

                    '#F5C000',

                    '#FF9B85',

                    '#FFD400',

                    '#F4719B',

                    '#F2EA52'

                ],

                borderWidth: 0

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            cutout: '62%',

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        boxWidth: 10,

                        usePointStyle: true,

                        pointStyle: 'circle',

                        padding: 12,

                        font: {

                            size: 11

                        }

                    }

                }

            }

        }

    });

});

</script>

@endpush
@endsection