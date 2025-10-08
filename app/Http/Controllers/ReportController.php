<?php

namespace App\Http\Controllers;

use App\Models\CountingReport;
use App\Models\CompanyBranch;
use App\Models\ReIdBranchDetection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller {
    public function __construct() {
        //
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

    public function monthly(Request $request) {
        $month = $request->input('month', now()->format('Y-m'));
        $branchId = $request->input('branch_id');

        $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month . '-01')->endOfMonth();

        $query = CountingReport::where('report_type', 'daily')
            ->whereBetween('report_date', [$startDate, $endDate]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $reports = $query->with('branch')->orderBy('report_date')->get();
        $branches = CompanyBranch::active()->get();

        // Aggregate monthly stats
        $monthlyStats = [
            'total_detections' => $reports->sum('total_detections'),
            'unique_persons' => $reports->max('unique_person_count'),
            'total_events' => $reports->sum('total_events'),
        ];

        // Additional calculations for view
        $groupedReports = $reports->groupBy('branch_id');
        $totalDevices = $reports->unique('branch_id')->sum(function ($r) {
            return $r->total_devices;
        });
        $avgDetectionsPerDay = $monthlyStats['total_detections'] / max($reports->count(), 1);

        // Branch statistics
        $branchStats = $reports->groupBy('branch_id')->map(function ($items) {
            return [
                'branch' => $items->first()->branch,
                'total_detections' => $items->sum('total_detections'),
                'total_events' => $items->sum('total_events'),
                'unique_persons' => $items->max('unique_person_count'),
                'avg_per_day' => $items->avg('total_detections'),
            ];
        });

        $maxBranchDetections = $branchStats->max('total_detections') ?: 1;

        return view('reports.monthly', compact(
            'reports',
            'branches',
            'month',
            'branchId',
            'monthlyStats',
            'groupedReports',
            'totalDevices',
            'avgDetectionsPerDay',
            'branchStats',
            'maxBranchDetections'
        ));
    }
}
