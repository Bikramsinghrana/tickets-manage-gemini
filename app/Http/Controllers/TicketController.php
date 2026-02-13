<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketService;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Requests\TicketUpdateRequest;
use App\Http\Requests\TicketStatusRequest;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
        protected TicketRepository $ticketRepository,
        protected UserRepository $userRepository,
        protected CategoryRepository $categoryRepository
    ) {}

    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $filters = $request->only(['search', 'status', 'priority', 'category_id', 'assigned_to', 'date_from', 'date_to', 'sort', 'direction']);
        
        // Non-managers only see their own tickets
        // dd(Auth::user()->getRoleNames());
        if (!$user->canManageTickets()) {
            // Not for Admin and Manager
            $tickets = $this->ticketService->getForUser($user, $filters);
        } else {
            $tickets = $this->ticketService->getFiltered($filters);
        }

        // Get filter options
        $categories = $this->categoryRepository->getForDropdown();
        $developers = $user->canManageTickets() ? $this->userRepository->getDevelopers() : collect();
        $statuses = TicketStatus::options();
        $priorities = TicketPriority::options();

        return view('tickets.index', compact(
            'tickets',
            'categories',
            'developers',
            'statuses',
            'priorities',
            'filters'
        ));
    }

    /**
     * Show the form for creating a new ticket.
     * Only Admin and Manager can access
     */
    public function create()
    {
        $this->authorize('create', Ticket::class);

        $categories = $this->categoryRepository->getForDropdown();
        $developers = $this->userRepository->getAssignableUsers();
        $priorities = TicketPriority::options();

        return view('tickets.create', compact('categories', 'developers', 'priorities'));
    }

    /**
     * Store a newly created ticket.
     * Standard POST request (no AJAX) for Admin/Manager
     */
    public function store(TicketStoreRequest $request)
    {
        $this->authorize('create', Ticket::class);

        $files = $request->hasFile('attachments') ? $request->file('attachments') : null;
        
        $ticket = $this->ticketService->create($request->validated(), $files);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} created successfully!");
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket = $this->ticketService->getWithRelations($ticket->id);
        
        // Get available status transitions for developers
        $availableStatuses = [];
        if (auth()->user()->isDeveloper() && $ticket->assigned_to === auth()->id()) {
            $availableStatuses = $ticket->getAvailableStatusTransitions();
        }

        return view('tickets.show', compact('ticket', 'availableStatuses'));
    }

    /**
     * Show the form for editing the ticket.
     * Only Admin and Manager can access
     */
    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $categories = $this->categoryRepository->getForDropdown();
        $developers = $this->userRepository->getAssignableUsers();
        $priorities = TicketPriority::options();

        return view('tickets.edit', compact('ticket', 'categories', 'developers', 'priorities'));
    }

    /**
     * Update the specified ticket.
     * Standard POST request (no AJAX) for Admin/Manager
     */
    public function update(TicketUpdateRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $files = $request->hasFile('attachments') ? $request->file('attachments') : null;
        
        $this->ticketService->update($ticket, $request->validated(), $files);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully!');
    }

    /**
     * Update ticket status via AJAX.
     * Only for Developers updating their assigned tickets
     */
    public function updateStatus(TicketStatusRequest $request, Ticket $ticket): JsonResponse
    {
        // Authorization check
        if (!$ticket->canUpdateStatus(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this ticket status.',
            ], 403);
        }

        $result = $this->ticketService->updateStatus($ticket, $request->status);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'ticket' => [
                'id' => $result['ticket']->id,
                'status' => $result['ticket']->status->value,
                'status_label' => $result['ticket']->status->label(),
                'status_color' => $result['ticket']->status->color(),
                'status_badge' => $result['ticket']->status->badgeClass(),
            ],
        ]);
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticketNumber = $ticket->ticket_number;
        $this->ticketService->delete($ticket);

        return redirect()->route('tickets.index')
            ->with('success', "Ticket {$ticketNumber} deleted successfully!");
    }

    /**
     * Restore a soft-deleted ticket.
     */
    public function restore(int $id)
    {
        $ticket = Ticket::withTrashed()->findOrFail($id);
        $this->authorize('restore', $ticket);

        $this->ticketService->restore($id);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} restored successfully!");
    }
}
