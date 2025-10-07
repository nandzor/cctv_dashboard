<?php

namespace App\Services;

use App\Models\DeviceMaster;

class DeviceMasterService extends BaseService {
    public function __construct() {
        $this->model = new DeviceMaster();
        $this->searchableFields = ['device_id', 'device_name', 'device_type', 'notes'];
        $this->orderByColumn = 'device_name';
        $this->orderByDirection = 'asc';
    }

    public function getDeviceWithRelationships(string $deviceId): ?DeviceMaster {
        return DeviceMaster::with(['branch.group', 'reIdDetections', 'eventSettings'])
            ->where('device_id', $deviceId)->first();
    }

    public function getByBranch(int $branchId) {
        return DeviceMaster::where('branch_id', $branchId)->active()->get();
    }

    public function getByType(string $type) {
        return DeviceMaster::where('device_type', $type)->active()->get();
    }

    public function createDevice(array $data): DeviceMaster {
        return $this->create($data);
    }

    public function updateDevice(DeviceMaster $device, array $data): bool {
        return $this->update($device, $data);
    }

    public function deleteDevice(DeviceMaster $device): bool {
        return $device->update(['status' => 'inactive']);
    }

    public function getStatistics(): array {
        return [
            'total_devices' => DeviceMaster::count(),
            'active_devices' => DeviceMaster::active()->count(),
            'inactive_devices' => DeviceMaster::inactive()->count(),
            'by_type' => DeviceMaster::selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')->pluck('count', 'device_type')->toArray(),
        ];
    }
}
