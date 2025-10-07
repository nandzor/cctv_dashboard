<?php

namespace App\Services;

use App\Models\CompanyGroup;
use Illuminate\Database\Eloquent\Model;

class CompanyGroupService extends BaseService {
    /**
     * CompanyGroupService constructor.
     */
    public function __construct() {
        $this->model = new CompanyGroup();
        $this->searchableFields = ['group_name', 'province', 'description'];
        $this->orderByColumn = 'group_name';
        $this->orderByDirection = 'asc';
    }

    /**
     * Get all active groups with branch count
     */
    public function getActiveGroupsWithBranchCount() {
        return CompanyGroup::active()
            ->withCount('branches')
            ->orderBy('group_name', 'asc')
            ->get();
    }

    /**
     * Get group by ID with branches
     */
    public function getGroupWithBranches(int $id): ?CompanyGroup {
        return CompanyGroup::with(['branches' => function ($query) {
            $query->active()->orderBy('branch_name', 'asc');
        }])->find($id);
    }

    /**
     * Get groups by province
     */
    public function getByProvince(string $province) {
        return CompanyGroup::where('province', $province)
            ->active()
            ->orderBy('group_name', 'asc')
            ->get();
    }

    /**
     * Create company group
     */
    public function createGroup(array $data): CompanyGroup {
        return $this->create($data);
    }

    /**
     * Update company group
     */
    public function updateGroup(CompanyGroup $group, array $data): bool {
        return $this->update($group, $data);
    }

    /**
     * Delete company group (soft delete via status)
     */
    public function deleteGroup(CompanyGroup $group): bool {
        // Check if group has active branches
        if ($group->branches()->where('status', 'active')->exists()) {
            throw new \Exception('Cannot delete group with active branches. Please deactivate branches first.');
        }

        // Soft delete by setting status to inactive
        return $group->update(['status' => 'inactive']);
    }

    /**
     * Activate company group
     */
    public function activateGroup(CompanyGroup $group): bool {
        return $group->update(['status' => 'active']);
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array {
        $total = CompanyGroup::count();
        $active = CompanyGroup::active()->count();
        $inactive = CompanyGroup::inactive()->count();
        $totalBranches = CompanyGroup::withCount('branches')->get()->sum('branches_count');

        return [
            'total_groups' => $total,
            'active_groups' => $active,
            'inactive_groups' => $inactive,
            'total_branches' => $totalBranches,
        ];
    }
}
