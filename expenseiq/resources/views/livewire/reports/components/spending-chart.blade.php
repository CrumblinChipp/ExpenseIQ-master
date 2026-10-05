<div class="rounded-[28px] border-2 border-[#F3C400] bg-white p-6">

    <div class="flex items-center justify-between mb-6">

        <div>

            <h2 class="text-3xl font-bold text-[#4D3900]">
                Spending Trend
            </h2>

            <p class="text-gray-500 mt-1">
                Monthly expenses overview
            </p>

        </div>

        <div class="flex gap-3">

            {{-- Month --}}
            <select
                wire:model.live="month"
                class="rounded-xl border border-gray-300 px-4 py-2 focus:border-[#F3C400] focus:outline-none">

                <option value="all">
                    All Months
                </option>

                @for ($m = 1; $m <= 12; $m++)

                    <option value="{{ $m }}">
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>

                @endfor

            </select>

            {{-- Category --}}
            <select
                wire:model.live="category"
                class="rounded-xl border border-gray-300 px-4 py-2 focus:border-[#F3C400] focus:outline-none">

                <option value="all">
                    All Categories
                </option>

                @foreach ($categories as $item)

                    <option value="{{ $item->id }}">
                        {{ $item->category_name }}
                    </option>

                @endforeach

            </select>

        </div>

    </div>

    <div class="h-[380px]" wire:ignore>

        <canvas id="reportChart"></canvas>

    </div>

</div>

<script>

let reportChart = null;

function createReportChart(labels, data)
{
    const canvas = document.getElementById('reportChart');

    if (!canvas) return;

    // Destroy old chart
    if (reportChart) {
        reportChart.destroy();
        reportChart = null;
    }

    reportChart = new Chart(canvas, {

        type: 'line',

        data: {

            labels,

            datasets: [{

                label: 'Expenses',

                data,

                borderColor: '#F3C400',

                backgroundColor: 'rgba(243,196,0,.15)',

                fill: true,

                tension: .4,

                borderWidth: 3,

                pointRadius: 5,

                pointBackgroundColor: '#F3C400'

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            animation: false,

            plugins: {

                legend: {

                    display: false

                }

            }

        }

    });

}

document.addEventListener('livewire:init', () => {

    createReportChart(
        @json($report['chartLabels']),
        @json($report['chartData'])
    );

    Livewire.on('report-chart-updated', (event) => {
        createReportChart(
            event.labels,
            event.data
        );
    });

});

</script>