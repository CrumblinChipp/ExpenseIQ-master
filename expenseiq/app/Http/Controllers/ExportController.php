<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    private function reportFor(Request $request, ReportService $reportService): array
    {
        $data = $request->validate([
            'from'     => 'required|date',
            'to'       => 'required|date|after_or_equal:from',
            'category' => 'nullable',
        ]);

        return [
            $reportService->getReportByDateRange(
                Auth::id(),
                $data['from'],
                $data['to'],
                $data['category'] ?? 'all'
            ),
            $data['from'],
            $data['to'],
        ];
    }

    public function csv(Request $request, ReportService $reportService): StreamedResponse
    {
        [$report] = $this->reportFor($request, $reportService);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="expense-report.csv"',
        ];

        $callback = function () use ($report) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Date', 'Purchase', 'Item', 'Category', 'Amount']);

            foreach ($report['expenses'] as $item) {
                fputcsv($file, [
                    $item->expense?->expense_date?->format('Y-m-d'),
                    $item->expense?->description,
                    $item->description,
                    $item->category?->category_name,
                    $item->amount,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function pdf(Request $request, ReportService $reportService)
    {
        [$report, $from, $to] = $this->reportFor($request, $reportService);

        return Pdf::loadView('reports.pdf', [
            'report' => $report,
            'from'   => $from,
            'to'     => $to,
        ])->download('expense-report.pdf');
    }
}