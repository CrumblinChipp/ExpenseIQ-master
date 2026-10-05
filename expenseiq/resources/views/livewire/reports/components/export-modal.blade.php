@if($showExportModal)

<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30">

    <div class="w-[820px] rounded-[34px] border-[3px] border-[#F3C400] bg-white p-10 shadow-2xl">

        {{-- Header --}}
        <div class="flex items-start justify-between">

            <div>

                <h2 class="flex items-center gap-3 text-[34px] font-bold text-[#5B4300]">

                    <i class="fa-solid fa-download"></i>

                    Export Report

                </h2>

            </div>

            <button
                wire:click="closeExportModal"
                class="flex h-14 w-14 items-center justify-center rounded-xl border-2 border-black hover:bg-gray-100">

                <i class="fa-solid fa-xmark text-3xl"></i>

            </button>

        </div>

        {{-- Date Range --}}
        <div class="mt-6">

            <h3 class="mb-4 text-[26px] font-bold text-[#5B4300]">

                Date Range

            </h3>

            <div class="grid grid-cols-2 gap-8">

                <div>

                    <label class="mb-2 block text-2xl text-[#5B4300]">

                        From

                    </label>

                    <input
                        type="date"
                        wire:model="exportFrom"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 text-xl focus:border-[#F3C400] focus:outline-none">

                </div>

                <div>

                    <label class="mb-2 block text-2xl text-[#5B4300]">

                        To

                    </label>

                    <input
                        type="date"
                        wire:model="exportTo"
                        class="w-full rounded-2xl border border-gray-300 px-5 py-3 text-xl focus:border-[#F3C400] focus:outline-none">

                </div>

            </div>

        </div>

        {{-- Category --}}
        <div class="mt-6">

            <label class="mb-2 block text-[26px] font-bold text-[#5B4300]">

                Category

            </label>

            <select
                wire:model="exportCategory"
                class="w-full rounded-2xl border border-gray-300 px-5 py-3 text-xl focus:border-[#F3C400] focus:outline-none">

                <option value="all">

                    All Categories

                </option>

                @foreach($categories as $category)

                    <option value="{{ $category->id }}">

                        {{ $category->category_name }}

                    </option>

                @endforeach

            </select>

        </div>

        {{-- Format --}}
        <div class="mt-8">

            <h3 class="mb-4 text-[26px] font-bold text-[#5B4300]">

                Format

            </h3>

            <div class="space-y-5">

                {{-- CSV --}}
                <div
                    wire:click="$set('exportFormat','csv')"
                    class="cursor-pointer rounded-2xl border-2 border-[#F3C400] p-6 transition

                    {{ $exportFormat == 'csv'
                        ? 'bg-[#FFF3B0]'
                        : 'bg-white hover:bg-[#FFF9DD]' }}">

                    <div class="flex items-center gap-5">

                        @if($exportFormat=='csv')

                            <i class="fa-solid fa-circle text-[#F3C400] text-xl"></i>

                        @else

                            <i class="fa-regular fa-circle text-gray-400 text-xl"></i>

                        @endif

                        <i class="fa-solid fa-file-csv text-5xl text-[#7A5B00]"></i>

                        <div>

                            <h3 class="text-[30px] font-bold text-[#5B4300]">

                                CSV Spreadsheet

                            </h3>

                            <p class="text-lg text-gray-600">

                                Open on Excel or Google Sheets

                            </p>

                        </div>

                    </div>

                </div>

                {{-- PDF --}}
                <div
                    wire:click="$set('exportFormat','pdf')"
                    class="cursor-pointer rounded-2xl border-2 border-[#F08AA5] p-6 transition

                    {{ $exportFormat == 'pdf'
                        ? 'bg-[#F9D0D8]'
                        : 'bg-white hover:bg-[#FCE8EC]' }}">

                    <div class="flex items-center gap-5">

                        @if($exportFormat=='pdf')

                            <i class="fa-solid fa-circle text-pink-500 text-xl"></i>

                        @else

                            <i class="fa-regular fa-circle text-gray-400 text-xl"></i>

                        @endif

                        <i class="fa-solid fa-file-pdf text-5xl text-[#7A5B00]"></i>

                        <div>

                            <h3 class="text-[30px] font-bold text-[#5B4300]">

                                PDF Report

                            </h3>

                            <p class="text-lg text-gray-600">

                                Formatted summary with charts

                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Buttons --}}
        <div class="mt-10 flex justify-end gap-5">

            <button
                wire:click="closeExportModal"
                class="rounded-2xl border-2 border-black bg-white px-10 py-4 text-2xl font-bold hover:bg-gray-100">

                Cancel

            </button>

            <button
                wire:click="downloadReport"
                class="flex items-center gap-3 rounded-2xl bg-[#F3C400] px-10 py-4 text-2xl font-bold text-black hover:bg-yellow-400">

                <i class="fa-solid fa-download"></i>

                Download

            </button>

        </div>

    </div>

</div>

@endif