<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function csv(
        Request $request,
        ReportService $reportService
    ): StreamedResponse {

        $from = $request->from;
        $to = $request->to;
        $category = $request->category ?? 'all';

        $report = $reportService->getReportByDateRange(
            Auth::id(),
            $from,
            $to,
            $category
        );

        $headers = [

            'Content-Type' => 'text/csv',

            'Content-Disposition' =>
                'attachment; filename="expense-report.csv"',

        ];

        $callback = function () use ($report) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [

                'Category',
                'Description',
                'Amount',
                'Date',

            ]);

            foreach ($report['expenses'] as $expense) {

                fputcsv($file, [

                    $expense->category->category_name,
                    $expense->description,
                    $expense->amount,
                    $expense->expense_date,

                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function pdf(
        Request $request,
        ReportService $reportService
    )
    {
        $from = $request->from;
        $to = $request->to;
        $category = $request->category ?? 'all';

        $report = $reportService->getReportByDateRange(
            Auth::id(),
            $from,
            $to,
            $category
        );

        $pdf = Pdf::loadView('reports.pdf', [

            'report' => $report,

            'from' => $from,

            'to' => $to,

        ]);

        return $pdf->download('expense-report.pdf');
    }
}