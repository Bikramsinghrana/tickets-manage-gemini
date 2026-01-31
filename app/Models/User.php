<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'department',
        'bio',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get tickets created by this user
     */
    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    /**
     * Get tickets assigned to this user
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Get tickets assigned by this user
     */
    public function ticketsAssignedByMe()
    {
        return $this->hasMany(Ticket::class, 'assigned_by');
    }

    /**
     * Get comments by this user
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get attachments uploaded by this user
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    /**
     * Get ticket activities by this user
     */
    public function ticketActivities()
    {
        return $this->hasMany(TicketActivity::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN->value);
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->hasRole(Role::MANAGER->value);
    }

    /**
     * Check if user is developer
     */
    public function isDeveloper(): bool
    {
        return $this->hasRole(Role::DEVELOPER->value);
    }

    /**
     * Check if user can manage tickets (Admin or Manager)
     */
    public function canManageTickets(): bool
    {
        return $this->hasAnyRole([Role::ADMIN->value, Role::MANAGER->value]);
    }

    /**
     * Get avatar URL or default
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // Generate initials-based avatar
        $initials = collect(explode(' ', $this->name))
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->implode('');
            
        return "https://ui-avatars.com/api/?name={$initials}&background=6366f1&color=fff&size=128";
    }

    /**
     * Get primary role name
     */
    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->roles->first()?->name;
    }

    /**
     * Get primary role enum
     */
    public function getPrimaryRoleEnumAttribute(): ?Role
    {
        $roleName = $this->primary_role;
        return $roleName ? Role::tryFrom($roleName) : null;
    }

    /**
     * Get unread notification count
     */
    public function getUnreadNotificationCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Scope: Active users only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Users with specific role
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->role($role);
    }

    /**
     * Scope: Developers only
     */
    public function scopeDevelopers($query)
    {
        return $query->role(Role::DEVELOPER->value);
    }

    /**
     * Scope: Managers only
     */
    public function scopeManagers($query)
    {
        return $query->role(Role::MANAGER->value);
    }

    /**
     * Scope: Assignable users (Developers)
     */
    public function scopeAssignable($query)
    {
        return $query->active()->developers();
    }
}
