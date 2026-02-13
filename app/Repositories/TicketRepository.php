<?php

namespace App\Repositories;

use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TicketRepository extends BaseRepository
{
    protected array $searchableFields = ['title', 'description', 'ticket_number'];
    protected array $filterableFields = ['status', 'priority', 'category_id', 'assigned_to', 'created_by'];

    public function __construct(Ticket $model)
    {
        parent::__construct($model);
    }

    /**
     * Get filtered and paginated tickets
     */
    public function getFiltered(array $filters = [], ?int $perPage = null): LengthAwarePaginator
    {
        $perPage ??= (int) config('services.pagination.per_page', 10);

        $query = $this->model->newQuery()
            ->with(['creator', 'assignee', 'category']);

        // Apply search
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Apply priority filter
        if (!empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        // Apply assignee filter
        if (!empty($filters['assigned_to'])) {
            $query->byAssignee($filters['assigned_to']);
        }

        // Apply creator filter
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Apply date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply overdue filter
        if (!empty($filters['overdue'])) {
            $query->overdue();
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get tickets for a specific user (developer)
     */
    public function getForUser(User $user, array $filters = [], ?int $perPage = null): LengthAwarePaginator
    {
        $perPage ??= (int) config('services.pagination.per_page', 10);

        $filters['assigned_to'] = $user->id;
        return $this->getFiltered($filters, $perPage);
    }

    /**
     * Get ticket with all relations
     */
    public function getWithRelations(int $id): ?Ticket
    {
        return $this->model->with([
            'creator',
            'assignee',
            'assigner',
            'category',
            'comments.user',
            'attachments.uploader',
            'activities.user',
        ])->find($id);
    }

    /**
     * Get ticket statistics (cached)
     */
    public function getStatistics(?int $userId = null): array
    {
        $cacheKey = $userId ? "ticket_stats_user_{$userId}" : 'ticket_stats_global';
        
        return Cache::remember($cacheKey, 300, function () use ($userId) {
            $query = $this->model->newQuery();
            
            if ($userId) {
                $query->where('assigned_to', $userId);
            }

            $total = (clone $query)->count();
            $assigned = (clone $query)->where('status', TicketStatus::ASSIGNED)->count();
            $inProcess = (clone $query)->where('status', TicketStatus::IN_PROCESS)->count();
            $completed = (clone $query)->where('status', TicketStatus::COMPLETED)->count();
            $onHold = (clone $query)->where('status', TicketStatus::ON_HOLD)->count();
            $overdue = (clone $query)->overdue()->count();
            $highPriority = (clone $query)->highPriority()
                ->whereNotIn('status', [TicketStatus::COMPLETED, TicketStatus::CANCELLED])
                ->count();

            return [
                'total' => $total,
                'assigned' => $assigned,
                'in_process' => $inProcess,
                'completed' => $completed,
                'on_hold' => $onHold,
                'overdue' => $overdue,
                'high_priority' => $highPriority,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
        });
    }

    /**
     * Clear statistics cache
     */
    public function clearStatisticsCache(?int $userId = null): void
    {
        if ($userId) {
            Cache::forget("ticket_stats_user_{$userId}");
        }
        Cache::forget('ticket_stats_global');
    }

    /**
     * Get recent tickets
     */
    public function getRecent(int $limit = 5, ?int $userId = null): Collection
    {
        $query = $this->model->with(['creator', 'assignee', 'category'])
            ->orderBy('created_at', 'desc');

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get priority distribution
     */
    public function getPriorityDistribution(?int $userId = null): array
    {
        $query = $this->model->newQuery()
            ->whereNotIn('status', [TicketStatus::COMPLETED, TicketStatus::CANCELLED]);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();
    }

    /**
     * Get category distribution
     */
    public function getCategoryDistribution(): array
    {
        return $this->model->newQuery()
            ->whereNotIn('status', [TicketStatus::COMPLETED, TicketStatus::CANCELLED])
            ->selectRaw('category_id, count(*) as count')
            ->groupBy('category_id')
            ->with('category:id,name,color')
            ->get()
            ->mapWithKeys(fn($item) => [
                $item->category?->name ?? 'Uncategorized' => [
                    'count' => $item->count,
                    'color' => $item->category?->color ?? '#6b7280',
                ]
            ])
            ->toArray();
    }
}
