<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Http\Requests\ReportFilterRequest; // ← Tạo mới
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function dashboard(ReportFilterRequest $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : null;
        $end   = $request->end_date ? Carbon::parse($request->end_date) : null;

        return response()->json($this->reportService->getDashboardStats($start, $end));
    }

    public function salesTrend(ReportFilterRequest $request)
    {
        $period = $request->validated('period', 'day');
        $days   = $request->validated('days', 90);

        return response()->json($this->reportService->getSalesTrend($period, $days));
    }

    // Các method khác dùng ReportFilterRequest nếu cần
}