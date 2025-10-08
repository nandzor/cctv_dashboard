<?php

namespace App\Http\Controllers;

use App\Models\ReIdMaster;
use App\Services\ReIdMasterService;
use App\Services\BaseExportService;
use App\Exports\ReIdMastersExport;
use Illuminate\Http\Request;

class ReIdMasterController extends Controller {
    protected $reIdMasterService;
    protected $exportService;

    public function __construct(ReIdMasterService $reIdMasterService, BaseExportService $exportService) {
        $this->reIdMasterService = $reIdMasterService;
        $this->exportService = $exportService;
    }

    /**
     * Display a listing of persons (Re-ID)
     */
    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $filters = [];
        if ($status) $filters['status'] = $status;

        // If date range provided, use specialized method
        if ($dateFrom && $dateTo) {
            $persons = $this->reIdMasterService->getByDateRange($dateFrom, $dateTo, $filters);
            $statistics = $this->reIdMasterService->getStatistics(['date' => $dateFrom]);
        } else {
            $persons = $this->reIdMasterService->getPaginate($search, $perPage, $filters);
            $statistics = $this->reIdMasterService->getStatistics();
        }

        return view('re-id-masters.index', compact('persons', 'statistics', 'search', 'perPage', 'status'));
    }

    /**
     * Display the specified person with detection history
     */
    public function show(string $reId, Request $request) {
        // Get all dates this person was detected first
        $allDetections = $this->reIdMasterService->getAllDetectionsForPerson($reId);

        if ($allDetections->isEmpty()) {
            abort(404, 'Person not found');
        }

        // If date not specified, use the latest detection date
        $date = $request->input('date');

        if (!$date) {
            $latestDetection = $allDetections->first();
            $date = \Carbon\Carbon::parse($latestDetection->detection_date)->format('Y-m-d');
        }

        $person = $this->reIdMasterService->getPersonWithDetections($reId, $date);

        if (!$person) {
            // If not found for specific date, redirect to latest date
            $latestDetection = $allDetections->first();
            $latestDate = \Carbon\Carbon::parse($latestDetection->detection_date)->format('Y-m-d');
            return redirect()->route('re-id-masters.show', ['reId' => $reId, 'date' => $latestDate]);
        }

        $hasMultipleDetections = $allDetections->count() > 1;

        return view('re-id-masters.show', compact('person', 'allDetections', 'hasMultipleDetections', 'date'));
    }

    /**
     * Update person status (active/inactive)
     */
    public function update(Request $request, string $reId) {
        $request->validate([
            'date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'person_name' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $data = ['status' => $request->status];
            if ($request->filled('person_name')) {
                $data['person_name'] = $request->person_name;
            }

            ReIdMaster::where('re_id', $reId)
                ->where('detection_date', $request->date)
                ->update($data);

            return redirect()->back()->with('success', 'Person status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * Export persons data
     */
    public function export(Request $request) {
        // Build filters using service
        $filterKeys = ['status', 'date_from', 'date_to'];
        $filters = $this->exportService->buildFilters($request, $filterKeys);

        // Build query
        $query = ReIdMaster::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('detection_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('detection_date', '<=', $filters['date_to']);
        }

        $persons = $query->orderBy('detection_date', 'desc')->get();
        $format = $request->input('format', 'excel');

        // Generate filename using service
        $fileName = $this->exportService->generateFileName('Person_Tracking');

        // Export using service
        return $this->exportService->export(
            $format,
            new ReIdMastersExport($persons, $filters),
            're-id-masters.export-pdf',
            compact('persons', 'filters'),
            $fileName
        );
    }
}
