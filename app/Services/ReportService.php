<?php

namespace App\Services;

use App\Models\CountingReport;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class ReportService extends BaseService
{
    protected $model = CountingReport::class;

    protected $orderByColumn = 'report_date';
    protected $orderByDirection = 'desc';

    /**
     * Get monthly reports with pagination
     *
     * @param Request $request
     * @return array
     */
    public function getMonthlyReports(Request $request): array
    {
        $month = $request->input('month', now()->format('Y-m'));
        $branchId = $request->input('branch_id');
        $perPage = $request->input('per_page', 25);

        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $query = CountingReport::where('report_type', 'daily')
            ->whereBetween('report_date', [$startDate, $endDate]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Get all reports for statistics first (before pagination)
        $allReports = $query->with('branch')
            ->orderBy('report_date', 'desc')
            ->get();

        // Get paginated results (create new query)
        $paginatedQuery = CountingReport::where('report_type', 'daily')
            ->whereBetween('report_date', [$startDate, $endDate]);

        if ($branchId) {
            $paginatedQuery->where('branch_id', $branchId);
        }

        $reports = $paginatedQuery->with('branch')
            ->orderBy('report_date', 'desc')
            ->paginate($this->validatePerPage($perPage))
            ->appends($request->query());

        $branches = CompanyBranch::active()->get();


        // Calculate monthly statistics
        $monthlyStats = [
            'total_detections' => $allReports->sum('total_detections'),
            'unique_persons' => $allReports->max('unique_person_count'),
            'total_events' => $allReports->sum('total_events'),
        ];


        // Additional calculations for view
        $groupedReports = $allReports->groupBy('branch_id');
        $totalDevices = $allReports->unique('branch_id')->sum(function ($r) {
            return $r->total_devices;
        });
        $avgDetectionsPerDay = $monthlyStats['total_detections'] / max($allReports->count(), 1);

        // Branch statistics
        $branchStats = $allReports->groupBy('branch_id')->map(function ($items) {
            return [
                'branch' => $items->first()->branch,
                'total_detections' => $items->sum('total_detections'),
                'total_events' => $items->sum('total_events'),
                'unique_persons' => $items->max('unique_person_count'),
                'avg_per_day' => $items->avg('total_detections'),
            ];
        });

        $maxBranchDetections = $branchStats->max('total_detections') ?: 1;

        // Daily detections for chart
        $dailyDetections = [];
        foreach ($allReports as $report) {
            $date = Carbon::parse($report->report_date)->format('Y-m-d');
            if (!isset($dailyDetections[$date])) {
                $dailyDetections[$date] = 0;
            }
            $dailyDetections[$date] += $report->total_detections;
        }

        return [
            'reports' => $reports,
            'allReports' => $allReports,
            'branches' => $branches,
            'month' => $month,
            'branchId' => $branchId,
            'monthlyStats' => $monthlyStats,
            'groupedReports' => $groupedReports,
            'totalDevices' => $totalDevices,
            'avgDetectionsPerDay' => $avgDetectionsPerDay,
            'branchStats' => $branchStats,
            'maxBranchDetections' => $maxBranchDetections,
            'dailyDetections' => $dailyDetections,
            'perPageOptions' => $this->getPerPageOptions(),
            'perPage' => $perPage,
        ];
    }
}
