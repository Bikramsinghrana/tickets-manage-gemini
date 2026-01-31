<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'status',
        'priority',
        'category_id',
        'created_by',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'started_at',
        'completed_at',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'resolution_notes',
        'satisfaction_rating',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'priority' => TicketPriority::class,
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_date' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    /**
     * Boot method to generate ticket number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
        });
    }

    /**
     * Generate unique ticket number
     */
    public static function generateTicketNumber(): string
    {
        $year = date('Y');
        $lastTicket = self::withTrashed()
            ->where('ticket_number', 'like', "TKT-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) Str::afterLast($lastTicket->ticket_number, '-');
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('TKT-%s-%05d', $year, $newNumber);
    }

    /**
     * Category relationship
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->withDefault([
            'name' => 'Uncategorized',
            'color' => '#6b7280',
        ]);
    }

    /**
     * User who created the ticket
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User assigned to the ticket
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * User who assigned the ticket
     */
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Comments on this ticket
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Attachments for this ticket (polymorphic)
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Activity log for this ticket
     */
    public function activities()
    {
        return $this->hasMany(TicketActivity::class)->orderBy('created_at', 'desc');
    }

    /**
     * Check if ticket is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        
        return $this->due_date->isPast() && 
               !in_array($this->status, [TicketStatus::COMPLETED, TicketStatus::CANCELLED]);
    }

    /**
     * Get time remaining until due date
     */
    public function getTimeRemainingAttribute(): ?string
    {
        if (!$this->due_date) {
            return null;
        }
        
        return $this->due_date->diffForHumans();
    }

    /**
     * Check if current user can update status
     */
    public function canUpdateStatus(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admins and Managers can always update
        if ($user->canManageTickets()) {
            return true;
        }

        // Developers can only update their assigned tickets
        return $this->assigned_to === $user->id;
    }

    /**
     * Get available status transitions
     */
    public function getAvailableStatusTransitions(): array
    {
        return collect(TicketStatus::cases())
            ->filter(fn($status) => $this->status->canTransitionTo($status))
            ->values()
            ->toArray();
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: Filter by priority
     */
    public function scopeByPriority($query, $priority)
    {
        if ($priority) {
            return $query->where('priority', $priority);
        }
        return $query;
    }

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }
        return $query;
    }

    /**
     * Scope: Filter by assignee
     */
    public function scopeByAssignee($query, $userId)
    {
        if ($userId) {
            return $query->where('assigned_to', $userId);
        }
        return $query;
    }

    /**
     * Scope: Search by title or description
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Scope: Assigned tickets
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', TicketStatus::ASSIGNED);
    }

    /**
     * Scope: In progress tickets
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', TicketStatus::IN_PROCESS);
    }

    /**
     * Scope: Completed tickets
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', TicketStatus::COMPLETED);
    }

    /**
     * Scope: Overdue tickets
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
                     ->where('due_date', '<', now())
                     ->whereNotIn('status', [TicketStatus::COMPLETED, TicketStatus::CANCELLED]);
    }

    /**
     * Scope: High priority (high + urgent)
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [TicketPriority::HIGH, TicketPriority::URGENT]);
    }

    /**
     * Scope: Tickets for a specific user (created or assigned)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('created_by', $userId)
              ->orWhere('assigned_to', $userId);
        });
    }

    /**
     * Scope: With common eager loads
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['creator', 'assignee', 'category']);
    }
}
