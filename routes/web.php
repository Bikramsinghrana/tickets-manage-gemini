<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatbotController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Home page
Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard') 
        : view('welcome');
})->name('home');

// Guest routes (Login/Register)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [LoginController::class, 'register'])->middleware('throttle:3,1')->name('register.post');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// AI Chatbot API (available to all authenticated users)
Route::post('/api/chatbot', [ChatbotController::class, 'chat'])
    ->middleware(['auth', 'throttle:30,1'])
    ->name('chatbot.chat');

// Protected routes (all authenticated users)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile.show');

    // Notifications (AJAX)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    // Tickets - CRUD
    Route::resource('tickets', TicketController::class);
    
    // Ticket status update (AJAX for developers)
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])
        ->middleware('throttle:30,1')
        ->name('tickets.update-status');
    
    // Restore deleted ticket (Admin only via policy)
    Route::post('/tickets/{id}/restore', [TicketController::class, 'restore'])->name('tickets.restore') ->withTrashed();

    // Comments (AJAX)
    Route::post('/comments', [CommentController::class, 'store'])->middleware('throttle:10,1')->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Attachments
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Admin/Manager only routes
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::patch('/categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
    });
});
