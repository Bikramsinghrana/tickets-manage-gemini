<?php

namespace App\Repositories;

use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get users with role filter
     */
    public function getByRole(string $role): Collection
    {
        return $this->model->role($role)->active()->get();
    }

    /**
     * Get all developers for ticket assignment (cached)
     */
    public function getDevelopers(): Collection
    {
        return Cache::remember('developers_list', 600, function () {
            return $this->model
                ->role(Role::DEVELOPER->value)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'department']);
        });
    }

    /**
     * Get all managers (cached)
     */
    public function getManagers(): Collection
    {
        return Cache::remember('managers_list', 600, function () {
            return $this->model
                ->role(Role::MANAGER->value)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'department']);
        });
    }

    /**
     * Get assignable users for dropdown (cached)
     */
    public function getAssignableUsers(): Collection
    {
        return Cache::remember('assignable_users', 600, function () {
            return $this->model
                ->assignable()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'department']);
        });
    }

    /**
     * Clear user cache
     */
    public function clearCache(): void
    {
        Cache::forget('developers_list');
        Cache::forget('managers_list');
        Cache::forget('assignable_users');
    }

    /**
     * Get users filtered and paginated users
     */
    public function getFiltered(array $filters = [], ?int $perPage = null): LengthAwarePaginator
    {
        Log::info('UserRepository@getFiltered called by user');
        $perPage ??= (int) config('services.pagination.per_page', 10);

        $query = $this->model->newQuery()->with('roles');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get user with ticket statistics
     */
    public function getWithStats(int $id): ?User
    {
        return $this->model->with(['roles'])->withCount([
            'assignedTickets',
            'assignedTickets as assigned_tickets_count' => function ($q) {
                $q->where('status', 'assigned');
            },
            'assignedTickets as in_process_tickets_count' => function ($q) {
                $q->where('status', 'in_process');
            },
            'assignedTickets as completed_tickets_count' => function ($q) {
                $q->where('status', 'completed');
            },
        ])->find($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }
}
