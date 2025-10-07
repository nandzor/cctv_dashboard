<?php

namespace App\Services;

use App\Models\ReIdMaster;
use Illuminate\Support\Facades\DB;

class ReIdMasterService extends BaseService {
    public function __construct() {
        $this->model = new ReIdMaster();
        $this->searchableFields = ['re_id', 'person_name'];
        $this->orderByColumn = 'detection_date';
        $this->orderByDirection = 'desc';
    }

    /**
     * Get person with detection history
     */
    public function getPersonWithDetections(string $reId, string $date) {
        return ReIdMaster::with([
            'reIdBranchDetections' => function ($query) use ($date) {
                $query->whereDate('detection_timestamp', $date)
                    ->with(['branch', 'device'])
                    ->orderBy('detection_timestamp', 'desc');
            }
        ])
            ->where('re_id', $reId)
            ->where('detection_date', $date)
            ->first();
    }

    /**
     * Get all detections for a person across all dates
     */
    public function getAllDetectionsForPerson(string $reId) {
        return ReIdMaster::where('re_id', $reId)
            ->with('reIdBranchDetections.branch')
            ->orderBy('detection_date', 'desc')
            ->get();
    }

    /**
     * Get persons by date range
     */
    public function getByDateRange(string $startDate, string $endDate, array $filters = []) {
        $query = ReIdMaster::whereBetween('detection_date', [$startDate, $endDate]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('detection_date', 'desc')
            ->orderBy('detection_time', 'desc')
            ->get();
    }

    /**
     * Get statistics
     */
    public function getStatistics(array $filters = []): array {
        $query = ReIdMaster::query();

        if (isset($filters['date'])) {
            $query->whereDate('detection_date', $filters['date']);
        }

        $total = (clone $query)->count();
        $active = (clone $query)->where('status', 'active')->count();
        $inactive = (clone $query)->where('status', 'inactive')->count();

        $totalDetections = (clone $query)->sum('total_detection_branch_count');
        $totalActual = (clone $query)->sum('total_actual_count');
        $uniquePersons = (clone $query)->distinct('re_id')->count('re_id');

        return [
            'total_records' => $total,
            'active_records' => $active,
            'inactive_records' => $inactive,
            'total_detections' => $totalDetections,
            'total_actual_count' => $totalActual,
            'unique_persons' => $uniquePersons,
        ];
    }

    /**
     * Update person status
     */
    public function updateStatus(string $reId, string $date, string $status): bool {
        return ReIdMaster::where('re_id', $reId)
            ->where('detection_date', $date)
            ->update(['status' => $status]);
    }

    /**
     * Get top detected persons
     */
    public function getTopDetectedPersons(int $limit = 10, array $filters = []) {
        $query = ReIdMaster::query();

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereBetween('detection_date', [$filters['date_from'], $filters['date_to']]);
        }

        return $query->orderBy('total_actual_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
