<?php

namespace App\Http\Controllers;

use App\Models\DeviceMaster;
use App\Models\CompanyBranch;
use App\Services\DeviceMasterService;
use App\Http\Requests\StoreDeviceMasterRequest;
use App\Http\Requests\UpdateDeviceMasterRequest;
use Illuminate\Http\Request;

class DeviceMasterController extends Controller {
    protected $deviceMasterService;

    public function __construct(DeviceMasterService $deviceMasterService) {
        $this->deviceMasterService = $deviceMasterService;
        $this->middleware('auth');
    }

    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $filters = [];
        if ($request->filled('status')) $filters['status'] = $request->input('status');
        if ($request->filled('device_type')) $filters['device_type'] = $request->input('device_type');
        if ($request->filled('branch_id')) $filters['branch_id'] = $request->input('branch_id');

        $deviceMasters = $this->deviceMasterService->getPaginate($search, $perPage, $filters);
        $statistics = $this->deviceMasterService->getStatistics();
        $companyBranches = CompanyBranch::active()->get();

        return view('device-masters.index', compact('deviceMasters', 'statistics', 'companyBranches'));
    }

    public function create() {
        $companyBranches = CompanyBranch::active()->with('companyGroup')->get();
        return view('device-masters.create', compact('companyBranches'));
    }

    public function store(StoreDeviceMasterRequest $request) {
        try {
            $data = $request->validated();
            $data['status'] = $data['status'] ?? 'active';
            $this->deviceMasterService->createDevice($data);
            return redirect()->route('device-masters.index')->with('success', 'Device created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function show($deviceId) {
        $deviceMaster = $this->deviceMasterService->getDeviceWithRelationships($deviceId);
        if (!$deviceMaster) abort(404);
        return view('device-masters.show', compact('deviceMaster'));
    }

    public function edit($deviceId) {
        $deviceMaster = DeviceMaster::where('device_id', $deviceId)->firstOrFail();
        $companyBranches = CompanyBranch::active()->with('companyGroup')->get();
        return view('device-masters.edit', compact('deviceMaster', 'companyBranches'));
    }

    public function update(UpdateDeviceMasterRequest $request, $deviceId) {
        try {
            $device = DeviceMaster::where('device_id', $deviceId)->firstOrFail();
            $this->deviceMasterService->updateDevice($device, $request->validated());
            return redirect()->route('device-masters.show', $deviceId)->with('success', 'Device updated.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function destroy($deviceId) {
        try {
            $device = DeviceMaster::where('device_id', $deviceId)->firstOrFail();
            $this->deviceMasterService->deleteDevice($device);
            return redirect()->route('device-masters.index')->with('success', 'Device deactivated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }
}
