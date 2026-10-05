<div class="rounded-[28px] border-2 border-[#F3C400] bg-white p-6 h-full">

    <div class="flex items-center justify-between mb-6">

        <h2 class="text-2xl font-bold text-[#4D3900]">
            Top Categories
        </h2>

        <span class="text-sm text-gray-500">
            This Month
        </span>

    </div>

    @forelse($report['topCategories'] ?? [] as $category)

        <div class="mb-6">

            <div class="flex justify-between items-center mb-2">

                <span class="font-semibold text-[#4D3900]">
                    {{ $category['name'] }}
                </span>

                <span class="text-[#7B6100] font-bold">
                    ₱{{ number_format($category['amount'], 2) }}
                </span>

            </div>

            <div class="w-full h-3 rounded-full bg-[#FDECC8]">

                <div
                    class="h-3 rounded-full bg-[#F3C400]"
                    style="width: {{ $category['percentage'] }}%;">
                </div>

            </div>

        </div>

    @empty

        <div class="flex items-center justify-center h-52">

            <p class="text-gray-400">
                No category data available.
            </p>

        </div>

    @endforelse

</div>