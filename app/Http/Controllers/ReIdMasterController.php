<?php

namespace App\Http\Controllers;

use App\Models\ReIdMaster;
use App\Services\ReIdMasterService;
use Illuminate\Http\Request;

class ReIdMasterController extends Controller {
    protected $reIdMasterService;

    public function __construct(ReIdMasterService $reIdMasterService) {
        $this->reIdMasterService = $reIdMasterService;
        $this->middleware('auth');
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
        $date = $request->input('date', now()->toDateString());

        $person = $this->reIdMasterService->getPersonWithDetections($reId, $date);

        if (!$person) {
            abort(404, 'Person not found for the specified date');
        }

        // Get all dates this person was detected
        $allDetections = $this->reIdMasterService->getAllDetectionsForPerson($reId);

        return view('re-id-masters.show', compact('person', 'allDetections', 'date'));
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
}
