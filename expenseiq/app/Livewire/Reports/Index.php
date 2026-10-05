<?php

namespace App\Livewire\Reports;

use App\Models\Category;
use App\Services\ReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;

class Index extends Component
{
    public $month;

    public $category = 'all';

    public $report = [];

    // Export Modal
    public bool $showExportModal = false;

    public $exportFrom;
    public $exportTo;
    public $exportCategory = 'all';
    public $exportFormat = 'csv';

    public function mount(ReportService $reportService)
    {
        $this->month = 'all';

        $this->loadReport($reportService);
    }

    public function updatedMonth(ReportService $reportService)
    {
        $this->loadReport($reportService);
    
    }

    public function updatedCategory(ReportService $reportService)
    {
        $this->loadReport($reportService);
        
    }

    // =========================
    // Export Modal
    // =========================

    public function openExportModal()
    {
        $this->exportFrom = now()->startOfMonth()->format('Y-m-d');
        $this->exportTo = now()->endOfMonth()->format('Y-m-d');
        $this->exportCategory = 'all';
        $this->exportFormat = 'csv';

        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
    }

    // =========================

    public function downloadReport()
    {
        $route = $this->exportFormat === 'pdf'
            ? 'reports.export.pdf'
            : 'reports.export.csv';

        return redirect()->route($route, [
            'from' => $this->exportFrom,
            'to' => $this->exportTo,
            'category' => $this->exportCategory,
        ]);
    }

    private function loadReport(ReportService $reportService)
    {
        $this->report = $reportService->getMonthlyReport(
            Auth::id(),
            $this->month,
            now()->year,
            $this->category
        );

        $this->dispatch(
            'report-chart-updated',
            labels: $this->report['chartLabels'],
            data: $this->report['chartData'],
        );
    }

    public function render()
    {
        return view('livewire.reports.index', [
            'report' => $this->report,
            'categories' => Category::orderBy('category_name')->get(),
        ])->layout('layouts.app');
    }
}