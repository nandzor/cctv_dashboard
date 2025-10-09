<?php

namespace App\Http\Controllers;

use App\Models\CountingReport;
use App\Models\CompanyBranch;
use App\Models\ReIdBranchDetection;
use App\Exports\DailyReportsExport;
use App\Exports\MonthlyReportsExport;
use App\Exports\DashboardReportExport;
use App\Services\BaseExportService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller {
    protected $exportService;
    protected $reportService;

    public function __construct(BaseExportService $exportService, ReportService $reportService) {
        $this->exportService = $exportService;
        $this->reportService = $reportService;
    }

    public function dashboard(Request $request) {
        $dateFrom = $request->input('date_from', now()->subDays(7)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');

        // Overall statistics
        $query = ReIdBranchDetection::whereBetween('detection_timestamp', [$dateFrom, $dateTo]);
        if ($branchId) $query->where('branch_id', $branchId);

        $totalDetections = $query->count();
        $uniquePersons = $query->distinct('re_id')->count('re_id');
        $uniqueBranches = $query->distinct('branch_id')->count('branch_id');
        $uniqueDevices = $query->distinct('device_id')->count('device_id');

        // Daily trend - Fill all days in range
        $detectionData = ReIdBranchDetection::selectRaw('DATE(detection_timestamp) as date, COUNT(*) as count')
            ->whereBetween('detection_timestamp', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill all days in range (including days with 0 detections)
        $dailyTrend = collect();
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dailyTrend->push((object)[
                'date' => $dateStr,
                'count' => $detectionData->get($dateStr)->count ?? 0
            ]);
        }

        $maxDailyCount = $dailyTrend->max('count') ?: 1;

        // Top branches
        $topBranches = ReIdBranchDetection::select('branch_id', DB::raw('COUNT(*) as detection_count'))
            ->whereBetween('detection_timestamp', [$dateFrom, $dateTo])
            ->groupBy('branch_id')
            ->with('branch')
            ->orderByDesc('detection_count')
            ->limit(5)
            ->get();

        $branches = CompanyBranch::active()->get();

        return view('reports.dashboard', compact(
            'totalDetections',
            'uniquePersons',
            'uniqueBranches',
            'uniqueDevices',
            'dailyTrend',
            'maxDailyCount',
            'topBranches',
            'branches',
            'dateFrom',
            'dateTo',
            'branchId'
        ));
    }

    public function daily(Request $request) {
        $date = $request->input('date', now()->toDateString());
        $branchId = $request->input('branch_id');

        $query = CountingReport::where('report_type', 'daily')
            ->where('report_date', $date);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $reports = $query->with('branch')->get();
        $branches = CompanyBranch::active()->get();

        return view('reports.daily', compact('reports', 'branches', 'date', 'branchId'));
    }

    public function exportDashboard(Request $request) {
        $dateFrom = $request->input('date_from', now()->subDays(7)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $branchId = $request->input('branch_id');
        $format = $request->input('format', 'excel');

        // Get the same data as dashboard view
        $query = ReIdBranchDetection::whereBetween('detection_timestamp', [$dateFrom, $dateTo]);
        if ($branchId) $query->where('branch_id', $branchId);

        $totalDetections = $query->count();
        $uniquePersons = $query->distinct('re_id')->count('re_id');
        $uniqueBranches = $query->distinct('branch_id')->count('branch_id');
        $uniqueDevices = $query->distinct('device_id')->count('device_id');

        // Daily trend
        $detectionData = ReIdBranchDetection::selectRaw('DATE(detection_timestamp) as date, COUNT(*) as count')
            ->whereBetween('detection_timestamp', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyTrend = collect();
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dailyTrend->push((object)[
                'date' => $dateStr,
                'count' => $detectionData->get($dateStr)->count ?? 0
            ]);
        }

        // Top branches
        $topBranches = ReIdBranchDetection::select('branch_id', DB::raw('COUNT(*) as detection_count'))
            ->whereBetween('detection_timestamp', [$dateFrom, $dateTo])
            ->groupBy('branch_id')
            ->with('branch')
            ->orderByDesc('detection_count')
            ->limit(5)
            ->get();

        $data = compact(
            'totalDetections',
            'uniquePersons',
            'uniqueBranches',
            'uniqueDevices',
            'dailyTrend',
            'topBranches',
            'dateFrom',
            'dateTo',
            'branchId'
        );

        $fileName = $this->exportService->generateFileName('Dashboard_Report');

        // Export using service
        return $this->exportService->export(
            $format,
            new DashboardReportExport($data),
            'reports.dashboard-pdf',
            $data,
            $fileName
        );
    }

    public function exportDaily(Request $request) {
        $date = $request->input('date', now()->toDateString());
        $branchId = $request->input('branch_id');
        $format = $request->input('format', 'excel'); // excel or pdf

        $query = CountingReport::where('report_type', 'daily')
            ->where('report_date', $date);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $reports = $query->with('branch')->get();
        $dateFormatted = \Carbon\Carbon::parse($date)->format('Y-m-d');
        $fileName = 'Daily_Report_' . $dateFormatted;

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.daily-pdf', compact('reports', 'date', 'branchId'));
            return $pdf->download($fileName . '.pdf');
        }

        // Default: Excel
        return Excel::download(new DailyReportsExport($reports, $date), $fileName . '.xlsx');
    }

    public function monthly(Request $request) {
        $data = $this->reportService->getMonthlyReports($request);
        return view('reports.monthly', $data);
    }

    public function exportMonthly(Request $request) {
        $month = $request->input('month', now()->format('Y-m'));
        $branchId = $request->input('branch_id');
        $format = $request->input('format', 'excel');

        $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month . '-01')->endOfMonth();

        $query = CountingReport::where('report_type', 'daily')
            ->whereBetween('report_date', [$startDate, $endDate]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $reports = $query->with('branch')->orderBy('report_date')->get();

        // Aggregate monthly stats
        $monthlyStats = [
            'total_detections' => $reports->sum('total_detections'),
            'unique_persons' => $reports->max('unique_person_count'),
            'total_events' => $reports->sum('total_events'),
        ];

        $totalDevices = $reports->unique('branch_id')->sum(function ($r) {
            return $r->total_devices;
        });
        $avgDetectionsPerDay = $monthlyStats['total_detections'] / max($reports->count(), 1);

        $fileName = $this->exportService->generateFileName('Monthly_Report_' . $month);

        // Export using service
        return $this->exportService->export(
            $format,
            new MonthlyReportsExport($reports, $month),
            'reports.monthly-pdf',
            compact('reports', 'month', 'branchId', 'monthlyStats', 'totalDevices', 'avgDetectionsPerDay'),
            $fileName
        );
    }
}
