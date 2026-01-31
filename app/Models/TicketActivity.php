<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'field_changed',
        'old_value',
        'new_value',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Ticket this activity belongs to
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * User who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'created the ticket',
            'assigned' => 'assigned the ticket',
            'status_changed' => 'changed the status',
            'priority_changed' => 'changed the priority',
            'commented' => 'added a comment',
            'attachment_added' => 'added an attachment',
            'attachment_removed' => 'removed an attachment',
            'updated' => 'updated the ticket',
            'deleted' => 'deleted the ticket',
            'restored' => 'restored the ticket',
            default => $this->action,
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'fa-plus-circle text-success',
            'assigned' => 'fa-user-check text-info',
            'status_changed' => 'fa-exchange-alt text-warning',
            'priority_changed' => 'fa-flag text-warning',
            'commented' => 'fa-comment text-primary',
            'attachment_added' => 'fa-paperclip text-secondary',
            'attachment_removed' => 'fa-times text-danger',
            'updated' => 'fa-edit text-info',
            'deleted' => 'fa-trash text-danger',
            'restored' => 'fa-undo text-success',
            default => 'fa-circle text-muted',
        };
    }

    /**
     * Scope: Filter by action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Recent activities
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Create activity log entry
     */
    public static function log(
        Ticket $ticket, 
        string $action, 
        ?string $fieldChanged = null, 
        ?string $oldValue = null, 
        ?string $newValue = null, 
        ?string $description = null
    ): self {
        return self::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'field_changed' => $fieldChanged,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent(), 0, 500),
        ]);
    }
}
