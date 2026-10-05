<div class="rounded-[28px] border-2 border-[#F3C400] bg-white p-6 h-full">

    <div class="flex items-center justify-between mb-6">

        <h2 class="text-2xl font-bold text-[#4D3900]">
            Month Comparison
        </h2>

        <span class="text-sm text-gray-500">
            Previous Months
        </span>

    </div>

    @forelse($report['monthComparison'] ?? [] as $month)

        <div class="mb-6">

            <div class="flex justify-between items-center">

                <div>

                    <h3 class="font-bold text-[#4D3900]">
                        {{ $month['month'] }}
                    </h3>

                    <p class="text-sm text-gray-500">
                        ₱{{ number_format($month['amount'],2) }}
                    </p>

                </div>

                @if($month['change'] >= 0)

                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-bold text-green-600">

                        +{{ $month['change'] }}%

                    </span>

                @else

                    <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-bold text-red-600">

                        {{ $month['change'] }}%

                    </span>

                @endif

            </div>

        </div>

    @empty

        <div class="flex h-52 items-center justify-center">

            <p class="text-gray-400">
                No comparison data available.
            </p>

        </div>

    @endforelse

</div>