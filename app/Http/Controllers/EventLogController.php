<?php

namespace App\Http\Controllers;

use App\Models\EventLog;
use App\Models\CompanyBranch;
use App\Exports\EventLogsExport;
use App\Services\BaseExportService;
use Illuminate\Http\Request;

class EventLogController extends Controller {
    protected $exportService;

    public function __construct(BaseExportService $exportService) {
        $this->exportService = $exportService;
    }

    public function index(Request $request) {
        $query = EventLog::with(['branch', 'device', 'reIdMaster']);

        // Apply filters
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $events = $query->latest('created_at')->paginate(20);
        $branches = CompanyBranch::active()->get();

        return view('event-logs.index', compact('events', 'branches'));
    }

    public function show(EventLog $eventLog) {
        $eventLog->load(['branch', 'device', 'reIdMaster']);
        return view('event-logs.show', compact('eventLog'));
    }

    public function export(Request $request) {
        $query = EventLog::with(['branch', 'device', 'reIdMaster']);

        // Build filters using service (only event_type and branch_id)
        $filterKeys = ['event_type', 'branch_id'];
        $filters = $this->exportService->buildFilters($request, $filterKeys);

        // Apply filters to query
        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        $events = $query->latest('created_at')->get();
        $format = $request->input('format', 'excel');

        // Generate filename using service
        $fileName = $this->exportService->generateFileName('Event_Logs');

        // Export using service
        return $this->exportService->export(
            $format,
            new EventLogsExport($events, $filters),
            'event-logs.export-pdf',
            compact('events', 'filters'),
            $fileName
        );
    }
}
