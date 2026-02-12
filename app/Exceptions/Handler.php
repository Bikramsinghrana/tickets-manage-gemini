<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException; // Add this import for QueryException
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use PDOException; // Add this import for PDOException
use Throwable; // Add this import for Throwable

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // ✅ GLOBAL DB CONNECTION ERROR HANDLING
        $this->renderable(function (Throwable $e, $request) {
            
            // Check for database connection errors
            if ($e instanceof QueryException || $e instanceof PDOException) {

                $message = $e->getMessage();

                // DB connection lost / refused / timeout
                if (
                    str_contains($message, 'SQLSTATE[HY000]') ||
                    str_contains($message, 'server has gone away') ||
                    str_contains($message, 'Lost connection')
                ) {

                    // API request
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Service temporarily unavailable. Please try again later.'
                        ], 503);
                    }

                    // Web request
                    return response()->view('errors.service-unavailable', [], 503);
                }
            }

            return null; // let Laravel handle others
        });
    }
}
