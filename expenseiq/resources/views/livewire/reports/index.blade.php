<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between">

        <div>

            <h1 class="text-[48px] font-extrabold text-[#4D3900] leading-none">
                Report
            </h1>

            <p class="text-[#6C6C6C] text-lg mt-2">
                Overall Report Overview
            </p>

        </div>

        <button
            wire:click="openExportModal"
            class="w-48 h-16 rounded-2xl border-2 border-black bg-white
                   text-3xl font-bold hover:bg-gray-50 transition">

            Export

        </button>

    </div>

    {{-- Summary Cards --}}
    @include('livewire.reports.components.summary-cards')

    {{-- Spending Chart --}}
    @include('livewire.reports.components.spending-chart')

    {{-- Bottom Section --}}
    <div class="grid grid-cols-12 gap-6">

        <div class="col-span-6">
            @include('livewire.reports.components.top-categories')
        </div>

        <div class="col-span-6">
            @include('livewire.reports.components.month-comparison')
        </div>

    </div>

    {{-- Export Modal --}}
    @if($showExportModal)
        @include('livewire.reports.components.export-modal')
    @endif

</div>