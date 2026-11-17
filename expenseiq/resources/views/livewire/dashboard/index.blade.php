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


    <!-- Monthly Summary -->

    <div class="bg-white border-2 border-yellow-400 rounded-[28px] p-6">

        <h2 class="text-3xl font-bold text-[#5D4300] mb-8">
            Monthly Summary
        </h2>

        <div class="grid grid-cols-12 gap-10 items-center">

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

            <!-- Category Chart -->
            <div class="w-[440px] h-[440px]">
                <canvas id="categoryChart"></canvas>
            </div>

        </div>

    </div>
@push('scripts')

<script>

document.addEventListener("DOMContentLoaded", function () {


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