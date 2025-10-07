<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\CompanyGroupService;
use App\Services\CompanyBranchService;
use App\Services\DeviceMasterService;
use App\Services\ReIdMasterService;
use App\Models\EventLog;
use App\Models\ReIdBranchDetection;

class DashboardController extends Controller {
    protected $userService;
    protected $companyGroupService;
    protected $companyBranchService;
    protected $deviceMasterService;
    protected $reIdMasterService;

    public function __construct(
        UserService $userService,
        CompanyGroupService $companyGroupService,
        CompanyBranchService $companyBranchService,
        DeviceMasterService $deviceMasterService,
        ReIdMasterService $reIdMasterService
    ) {
        $this->userService = $userService;
        $this->companyGroupService = $companyGroupService;
        $this->companyBranchService = $companyBranchService;
        $this->deviceMasterService = $deviceMasterService;
        $this->reIdMasterService = $reIdMasterService;
    }

    /**
     * Display dashboard with comprehensive statistics
     */
    public function index() {
        // User statistics
        $totalUsers = $this->userService->getAll()->count();

        // Company statistics
        $totalGroups = $this->companyGroupService->getAll()->count();
        $totalBranches = $this->companyBranchService->getAll()->count();
        $totalDevices = $this->deviceMasterService->getAll()->count();

        // Re-ID statistics (today)
        $reIdStats = $this->reIdMasterService->getStatistics(['date' => now()->toDateString()]);

        // Recent detections (last 10)
        $recentDetections = ReIdBranchDetection::with(['reIdMaster', 'branch', 'device'])
            ->latest('detection_timestamp')
            ->limit(10)
            ->get();

        // Detection trend (last 7 days)
        $detectionTrend = ReIdBranchDetection::selectRaw('DATE(detection_timestamp) as date, COUNT(*) as count')
            ->where('detection_timestamp', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent events (last 10)
        $recentEvents = EventLog::with(['branch', 'device'])
            ->latest('event_timestamp')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'totalUsers',
            'totalGroups',
            'totalBranches',
            'totalDevices',
            'reIdStats',
            'recentDetections',
            'detectionTrend',
            'recentEvents'
        ));
    }
}
