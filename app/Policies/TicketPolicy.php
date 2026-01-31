<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any tickets.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view ticket list
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Admin and Manager can view all tickets
        if ($user->canManageTickets()) {
            return true;
        }

        // Developers can only view tickets assigned to them or created by them
        return $ticket->assigned_to === $user->id || $ticket->created_by === $user->id;
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user): bool
    {
        // Only Admin and Manager can create tickets
        return $user->canManageTickets();
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Only Admin and Manager can update tickets (full update)
        return $user->canManageTickets();
    }

    /**
     * Determine whether the user can update the ticket status.
     */
    public function updateStatus(User $user, Ticket $ticket): bool
    {
        // Admin and Manager can update any ticket status
        if ($user->canManageTickets()) {
            return true;
        }

        // Developers can only update status of their assigned tickets
        return $ticket->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the ticket.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Only Admin can delete tickets
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the ticket.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        // Only Admin can restore tickets
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the ticket.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        // Only Admin can force delete
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        // Only Admin and Manager can assign tickets
        return $user->canManageTickets();
    }
}
