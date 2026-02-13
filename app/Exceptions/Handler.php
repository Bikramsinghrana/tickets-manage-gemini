<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PDOException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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
            Log::warning('Exception occurred: ' . $e->getMessage(), [
                'exception' => $e,
                'url' => request()->fullUrl(),
                'input' => request()->all(),
            ]);
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e);
            }

            return $this->handleWebException($e, $request);
        });
    }

    protected function handleApiException(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'errors' => null,
            ], 401);
        }

        if ($e instanceof AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'errors' => null,
            ], 403);
        }

        if (
            $e instanceof NotFoundHttpException ||
            $e instanceof MethodNotAllowedHttpException ||
            $e instanceof ModelNotFoundException
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'errors' => null,
            ], 404);
        }

        if ($e instanceof QueryException || $e instanceof PDOException) {
            return response()->json([
                'success' => false,
                'message' => 'Service temporarily unavailable',
                'errors' => null,
            ], 503);
        }

        return response()->json([
            'success' => false,
            'message' => 'Internal server error',
            'errors' => null,
        ], 500);
    }

    protected function handleWebException(Throwable $e, $request)
    {
        if ($e instanceof TokenMismatchException) {
            return redirect()
                ->back()
                ->withInput($request->except('_token'))
                ->with('error', 'Your session has expired. Please try again.');
        }

        if ($e instanceof AuthenticationException) {
            return redirect()->guest(url('/login'));
        }

        if ($e instanceof AuthorizationException) {
            return response()->view('errors.403', [], 403);
        }

        if (
            $e instanceof NotFoundHttpException ||
            $e instanceof MethodNotAllowedHttpException ||
            $e instanceof ModelNotFoundException
        ) {
            return response()->view('errors.404', [], 404);
        }

        if ($e instanceof QueryException || $e instanceof PDOException) {
            return response()->view('errors.503', [], 503);
        }

        return null;
    }
}
