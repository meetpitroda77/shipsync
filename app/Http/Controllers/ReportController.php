<?php

namespace App\Http\Controllers;

use App\Models\ShipmentReport;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function showReports(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $reports = ShipmentReport::query()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('report_date', [$startDate, $endDate]);
            })
            ->when($startDate && !$endDate, function ($query) use ($startDate) {
                $query->where('report_date', '>=', $startDate);
            })
            ->when(!$startDate && $endDate, function ($query) use ($endDate) {
                $query->where('report_date', '<=', $endDate);
            })

            ->orderBy('report_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('pages.reports.reports', compact('reports', 'startDate', 'endDate'));
    }
}
