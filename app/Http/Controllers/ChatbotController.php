<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;

class ChatbotController extends Controller
{
    /**
     * Handle chatbot message and get AI response
     */
    public function chat(Request $request)
    {

        // 🔹 LOCAL FAKE RESPONSE (optional)

        // if (config('app.env') === 'local') {
        //     return response()->json([
        //         'success' => true,
        //         'response' => '🤖 Fake AI response for testing',
        //     ]);
        // }

        // 🔹 PRODUCTION AI INTEGRATION
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->input('message');
        $apiKey = config('services.gemini.api_key');

        // dd($apiKey);

        // Check if API key is configured
        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'response' => 'AI service is not configured. Please add your GEMINI_API_KEY to the .env file.',
            ], 500);
        }

        try {
            // Get personalized context with real data
            $systemContext = $this->getPersonalizedContext();

            // Build the prompt with context
            $fullPrompt = $systemContext . "\n\nUser Question: " . $userMessage;

            // Call Gemini API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Extract the response text
                $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'I apologize, but I couldn\'t generate a response. Please try again.';

                // Convert markdown-style formatting to HTML for better display
                $aiResponse = $this->formatResponse($aiResponse);

                return response()->json([
                    'success' => true,
                    'response' => $aiResponse,
                ]);
            } else {

                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'response' => 'I\'m having trouble connecting to the AI service. Please try again later.',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Chatbot Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'response' => 'An error occurred while processing your request. Please try again.',
            ], 500);
        }
    }


    /**
     * Get personalized context with real data from database
     */
    private function getPersonalizedContext(): string
    {
        $user = auth()->user();
        $context = $this->getBaseContext();
        
        // Add real categories from database
        $context .= $this->getCategoriesContext();
        
        // Add user-specific context
        $context .= $this->getUserContext($user);
        
        // Add system statistics
        $context .= $this->getSystemStats();
        
        // Add response guidelines
        $context .= $this->getResponseGuidelines();
        
        return $context;
    }

    /**
     * Get base system context
     */
    private function getBaseContext(): string
    {
        return <<<EOT
        You are a helpful AI assistant for the Ticket Management System. Your name is "Ticket Assistant".

        SYSTEM OVERVIEW:
        This is a ticket management system where users can:
        - Create support tickets for issues, bugs, or feature requests
        - Track ticket progress through various statuses
        - Communicate via comments on tickets
        - Attach files and screenshots to tickets
        - Receive notifications on ticket updates

        TICKET STATUSES:
        - Assigned: Ticket has been assigned to a developer
        - In Process: Developer is actively working on it
        - On Hold: Work is temporarily paused
        - Completed: Issue has been resolved
        - Cancelled: Ticket was cancelled

        PRIORITY LEVELS:
        - Low: Minor issues, no rush
        - Medium: Normal priority
        - High: Important, needs attention soon
        - Urgent: Critical, needs immediate attention

        EOT;
    }

    /**
     * Get categories context from database
     */
    private function getCategoriesContext(): string
    {
        try {
            $categories = Category::active()->sorted()->get(['name', 'description']);
            
            if ($categories->isEmpty()) {
                return "\nAVAILABLE CATEGORIES: No categories defined yet.\n";
            }
            
            $categoryList = $categories->map(function ($cat) {
                $desc = $cat->description ? " - {$cat->description}" : "";
                return "- {$cat->name}{$desc}";
            })->implode("\n");
            
            return "\nAVAILABLE TICKET CATEGORIES:\n{$categoryList}\n";
        } catch (\Exception $e) {
            Log::warning('Could not fetch categories for chatbot: ' . $e->getMessage());
            return "\nCATEGORIES: Unable to fetch categories.\n";
        }
    }

    /**
     * Get user-specific context
     */
    private function getUserContext($user): string
    {
        if (!$user) {
            return "\nUSER: Guest user (not logged in)\n";
        }

        $context = "\nCURRENT USER CONTEXT:\n";
        $context .= "- Name: {$user->name}\n";
        $context .= "- Role: " . ($user->primary_role ?? 'User') . "\n";
        
        try {
            // Get user's ticket statistics
            if ($user->isAdmin() || $user->isManager()) {
                // Admin/Manager sees overall stats
                $totalTickets = Ticket::count();
                $openTickets = Ticket::whereNotIn('status', [TicketStatus::COMPLETED, TicketStatus::CANCELLED])->count();
                $context .= "- Access Level: Full system access (Admin/Manager)\n";
                $context .= "- Total tickets in system: {$totalTickets}\n";
                $context .= "- Open tickets: {$openTickets}\n";
            } elseif ($user->isDeveloper()) {
                // Developer sees their assigned tickets
                $assignedTickets = $user->assignedTickets()->whereNotIn('status', [TicketStatus::COMPLETED, TicketStatus::CANCELLED])->count();
                $inProgress = $user->assignedTickets()->where('status', TicketStatus::IN_PROCESS)->count();
                $context .= "- Access Level: Developer\n";
                $context .= "- Your assigned tickets: {$assignedTickets}\n";
                $context .= "- Currently in progress: {$inProgress}\n";
            } else {
                // Regular user sees their created tickets
                $myTickets = $user->createdTickets()->count();
                $myOpenTickets = $user->createdTickets()->whereNotIn('status', [TicketStatus::COMPLETED, TicketStatus::CANCELLED])->count();
                $context .= "- Access Level: Standard User\n";
                $context .= "- Your total tickets: {$myTickets}\n";
                $context .= "- Your open tickets: {$myOpenTickets}\n";
            }

            // Get user's recent tickets (last 5)
            $recentTickets = $user->createdTickets()
                ->with('category')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(['ticket_number', 'title', 'status', 'priority', 'category_id']);

            if ($recentTickets->isNotEmpty()) {
                $context .= "\nYour recent tickets:\n";
                foreach ($recentTickets as $ticket) {
                    $status = $ticket->status->label();
                    $priority = $ticket->priority->label();
                    $category = $ticket->category->name ?? 'Uncategorized';
                    $context .= "- {$ticket->ticket_number}: \"{$ticket->title}\" [{$status}] [{$priority}] [{$category}]\n";
                }
            }

        } catch (\Exception $e) {
            Log::warning('Could not fetch user context for chatbot: ' . $e->getMessage());
        }

        return $context;
    }

    /**
     * Get system statistics
     */
    private function getSystemStats(): string
    {
        try {
            $stats = [
                'total_tickets' => Ticket::count(),
                'completed_today' => Ticket::where('status', TicketStatus::COMPLETED)
                    ->whereDate('completed_at', today())
                    ->count(),
                'urgent_tickets' => Ticket::where('priority', TicketPriority::URGENT)
                    ->whereNotIn('status', [TicketStatus::COMPLETED, TicketStatus::CANCELLED])
                    ->count(),
                'developers' => User::developers()->active()->count(),
            ];

            return "\nSYSTEM STATISTICS:\n" .
                "- Total tickets in system: {$stats['total_tickets']}\n" .
                "- Completed today: {$stats['completed_today']}\n" .
                "- Urgent open tickets: {$stats['urgent_tickets']}\n" .
                "- Active developers: {$stats['developers']}\n";

        } catch (\Exception $e) {
            Log::warning('Could not fetch system stats for chatbot: ' . $e->getMessage());
            return "";
        }
    }

    /**
     * Get response guidelines for the AI
     */
    private function getResponseGuidelines(): string
    {
        return <<<EOT

        RESPONSE GUIDELINES:
        1. Be concise and helpful - aim for 2-4 sentences for simple questions
        2. Use the user's name when appropriate to personalize responses
        3. Reference their actual ticket data when relevant
        4. Guide users step-by-step for complex tasks
        5. If asked about a specific ticket, reference it by ticket number if available
        6. For questions outside the ticket system scope, politely redirect
        7. Use bullet points for listing items
        8. Be friendly but professional
        9. If the user asks about their tickets, reference the actual data provided above
        10. Suggest relevant actions the user can take

        HOW TO CREATE A TICKET:
        1. Click "Tickets" in the sidebar
        2. Click "Create New Ticket" button
        3. Fill in: Title, Description, Category, Priority
        4. Add attachments if needed (screenshots help!)
        5. Click "Submit" to create the ticket

        EOT;
    }

    /**
     * Format the AI response for HTML display
     */
    private function formatResponse(string $response): string
    {
        // Convert **bold** to <strong>
        $response = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $response);
        
        // Convert *italic* to <em>
        $response = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $response);
        
        // Convert newlines to <br>
        $response = nl2br($response);
        
        // Convert bullet points
        $response = preg_replace('/^[\-\•]\s*/m', '• ', $response);
        
        return $response;
    }
}
