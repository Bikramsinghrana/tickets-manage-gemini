<?php

namespace App\Http\Controllers;

use App\Services\TicketService;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
        protected TicketRepository $ticketRepository,
        protected UserRepository $userRepository,
        protected CategoryRepository $categoryRepository
    ) {}

    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get statistics based on user role
        if ($user->canManageTickets()) {
            // Admin/Manager sees global statistics
            $statistics = $this->ticketService->getStatistics();
            $recentTickets = $this->ticketRepository->getRecent(10);
            $priorityDistribution = $this->ticketRepository->getPriorityDistribution();
            $categoryDistribution = $this->ticketRepository->getCategoryDistribution();
            $developers = $this->userRepository->getDevelopers();
            
            return view('dashboard.admin', compact(
                'statistics',
                'recentTickets',
                'priorityDistribution',
                'categoryDistribution',
                'developers'
            ));
        } else {
            // Developer sees only their statistics
            $statistics = $this->ticketService->getStatistics($user->id);
            $recentTickets = $this->ticketRepository->getRecent(10, $user->id);
            $priorityDistribution = $this->ticketRepository->getPriorityDistribution($user->id);
            
            // Get assigned tickets by status
            $assignedTickets = $this->ticketRepository->getForUser($user, ['status' => 'assigned'], 5);
            $inProcessTickets = $this->ticketRepository->getForUser($user, ['status' => 'in_process'], 5);
            
            return view('dashboard.developer', compact(
                'statistics',
                'recentTickets',
                'priorityDistribution',
                'assignedTickets',
                'inProcessTickets'
            ));
        }
    }
}
