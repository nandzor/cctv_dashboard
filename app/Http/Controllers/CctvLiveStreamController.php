<?php

namespace App\Http\Controllers;

use App\Models\CctvLayoutSetting;
use App\Models\CctvPositionSetting;
use App\Models\CompanyBranch;
use App\Models\DeviceMaster;
use App\Services\CctvLayoutService;
use Illuminate\Http\Request;

class CctvLiveStreamController extends Controller
{
    protected $cctvLayoutService;

    public function __construct(CctvLayoutService $cctvLayoutService)
    {
        $this->cctvLayoutService = $cctvLayoutService;
    }

    /**
     * Display the main CCTV live stream page
     */
    public function index(Request $request)
    {
        $layoutId = $request->input('layout_id');

        // Get default layout if no specific layout requested
        $layout = $layoutId
            ? $this->cctvLayoutService->getLayoutWithPositions($layoutId)
            : $this->cctvLayoutService->getDefaultLayout();

        // Get all available layouts for switching
        $availableLayouts = CctvLayoutSetting::active()
            ->orderBy('is_default', 'desc')
            ->orderBy('layout_name')
            ->get();

        // Get all branches and devices for position configuration
        $branches = CompanyBranch::active()->with('group')->orderBy('branch_name')->get();
        $devices = DeviceMaster::active()->with('branch')->orderBy('device_name')->get();

        return view('cctv-live-stream.index', compact(
            'layout',
            'availableLayouts',
            'branches',
            'devices'
        ));
    }

    /**
     * Get stream URL for a specific device
     */
    public function getStreamUrl(Request $request, $deviceId)
    {
        $device = DeviceMaster::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        // Return the device URL (already decrypted by model)
        return response()->json([
            'device_id' => $device->device_id,
            'device_name' => $device->device_name,
            'stream_url' => $device->url,
            'username' => $device->username,
            'password' => $device->password,
        ]);
    }

    /**
     * Update position configuration
     */
    public function updatePosition(Request $request, $layoutId, $positionNumber)
    {
        $request->validate([
            'branch_id' => 'required|exists:company_branches,id',
            'device_id' => 'required|exists:device_masters,device_id',
            'is_enabled' => 'boolean',
            'auto_switch' => 'boolean',
            'switch_interval' => 'integer|min:5|max:300',
            'quality' => 'in:low,medium,high',
            'resolution' => 'in:640x480,1280x720,1920x1080',
        ]);

        $position = CctvPositionSetting::where('layout_id', $layoutId)
            ->where('position_number', $positionNumber)
            ->first();

        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        $position->update($request->only([
            'branch_id', 'device_id', 'is_enabled', 'auto_switch',
            'switch_interval', 'quality', 'resolution'
        ]));

        return response()->json([
            'success' => true,
            'position' => $position->load('branch', 'device')
        ]);
    }

    /**
     * Get devices for a specific branch
     */
    public function getBranchDevices(Request $request, $branchId)
    {
        $devices = DeviceMaster::where('branch_id', $branchId)
            ->where('status', 'active')
            ->where('device_type', 'cctv')
            ->orderBy('device_name')
            ->get(['device_id', 'device_name', 'device_type']);

        return response()->json($devices);
    }

    /**
     * Capture screenshot from a stream
     */
    public function captureScreenshot(Request $request, $deviceId)
    {
        $device = DeviceMaster::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        // This would typically involve calling an external service or FFmpeg
        // For now, we'll return a placeholder response
        return response()->json([
            'success' => true,
            'screenshot_url' => '/storage/screenshots/' . $deviceId . '_' . time() . '.jpg',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Start/stop recording for a stream
     */
    public function toggleRecording(Request $request, $deviceId)
    {
        $device = DeviceMaster::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $action = $request->input('action', 'start'); // start or stop

        // This would typically involve calling an external service
        // For now, we'll return a placeholder response
        return response()->json([
            'success' => true,
            'action' => $action,
            'recording_url' => $action === 'start' ? '/storage/recordings/' . $deviceId . '_' . time() . '.mp4' : null,
            'timestamp' => now()->toISOString()
        ]);
    }
}
