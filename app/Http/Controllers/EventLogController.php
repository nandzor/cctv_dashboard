<?php

namespace App\Http\Controllers;

use App\Models\EventLog;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;

class EventLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = EventLog::with(['branch', 'device', 'reIdMaster']);

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('event_timestamp', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_timestamp', '<=', $request->date_to);
        }

        $events = $query->latest('event_timestamp')->paginate(20);
        $branches = CompanyBranch::active()->get();

        return view('event-logs.index', compact('events', 'branches'));
    }

    public function show(EventLog $eventLog)
    {
        $eventLog->load(['branch', 'device', 'reIdMaster']);
        return view('event-logs.show', compact('eventLog'));
    }
}
