<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get paginated users
     */
    public function getPaginatedUsers(int $perPage = 10): LengthAwarePaginator
    {
        // Validate per page value
        $perPage = $this->validatePerPage($perPage);
        
        return User::latest()->paginate($perPage);
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        return User::all();
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Create new user
     */
    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        
        return User::create($data);
    }

    /**
     * Update user
     */
    public function updateUser(User $user, array $data): bool
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $user->update($data);
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Search users
     */
    public function searchUsers(string $query, int $perPage = 10): LengthAwarePaginator
    {
        // Validate per page value
        $perPage = $this->validatePerPage($perPage);
        
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Validate and sanitize per page value
     */
    private function validatePerPage(int $perPage): int
    {
        $allowedPerPage = [10, 20, 50, 100];
        
        if (!in_array($perPage, $allowedPerPage)) {
            return 10; // Default fallback
        }
        
        return $perPage;
    }

    /**
     * Get available per page options
     */
    public function getPerPageOptions(): array
    {
        return [
            10 => '10 per page',
            20 => '20 per page', 
            50 => '50 per page',
            100 => '100 per page'
        ];
    }
}

