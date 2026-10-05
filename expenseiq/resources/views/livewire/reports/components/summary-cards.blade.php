<div class="grid grid-cols-4 gap-6">

    {{-- Average Monthly --}}
    <div class="rounded-[28px] border-2 border-[#F3C400] bg-white p-6">

        <p class="text-[#7B6100] text-sm font-bold uppercase tracking-wide">
            Average Monthly
        </p>

        <h2 class="mt-3 text-4xl font-black text-[#4D3900]">
            ₱{{ number_format($report['averageMonthly'] ?? 0, 2) }}
        </h2>

    </div>

    {{-- Best Month --}}
    <div class="rounded-[28px] border-2 border-[#F3C400] bg-white p-6">

        <p class="text-[#7B6100] text-sm font-bold uppercase tracking-wide">
            Best Month
        </p>

        <h2 class="mt-3 text-4xl font-black text-[#4D3900]">
            {{ $report['bestMonth'] ?? '-' }}
        </h2>

    </div>

    {{-- Highest Month --}}
    <div class="rounded-[28px] border-2 border-[#F3C400] bg-white p-6">

        <p class="text-[#7B6100] text-sm font-bold uppercase tracking-wide">
            Highest Month
        </p>

        <h2 class="mt-3 text-4xl font-black text-[#4D3900]">
            ₱{{ number_format($report['highestMonthAmount'] ?? 0, 2) }}
        </h2>

    </div>

    {{-- Year Total --}}
    <div class="rounded-[28px] border-2 border-[#F3C400] bg-white p-6">

        <p class="text-[#7B6100] text-sm font-bold uppercase tracking-wide">
            Year Total
        </p>

        <h2 class="mt-3 text-4xl font-black text-[#4D3900]">
            ₱{{ number_format($report['yearTotal'] ?? 0, 2) }}
        </h2>

    </div>

</div>