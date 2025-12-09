<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

// CUSTOM EXCEPTIONS

class Handler extends ExceptionHandler
{
    /**
     * Exceptions that should not be reported.
     */
    protected $dontReport = [
        //
    ];

    /**
     * Inputs that should never be flashed for validation exceptions.
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Force JSON response for API requests
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        // Force JSON for all API routes
        return $request->is('api/*');
    }

    /**
     * Get the original exception (unwrap wrapped exceptions)
     */
    protected function getOriginalException(Throwable $e): Throwable
    {
        while ($e->getPrevious()) {
            $e = $e->getPrevious();
        }

        return $e;
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Get the original exception in case it's wrapped
        $exception = $this->getOriginalException($e);

        // Force JSON response for API
        if ($this->shouldReturnJson($request, $exception)) {

            // Handle custom exceptions
            if ($exception instanceof DateConflictException) {
                return response()->json([
                    'error' => 'Date Conflict',
                    'message' => $exception->getMessage(),
                ], 422);
            }

            if ($exception instanceof ExtraAttributesException) {
                return response()->json([
                    'error' => 'Invalid Attributes',
                    'message' => $exception->getMessage(),
                    'attributes' => $exception->getAttributes(),
                ], 422);
            }

            if ($exception instanceof CompletedBookingException || $exception instanceof CanceledBookingException) {
                return response()->json([
                    'error' => 'Invalid Booking Status',
                    'message' => $exception->getMessage(),
                ], 422);
            }

            // Standard Laravel exceptions
            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'error' => 'Resource Not Found',
                    'message' => $exception->getMessage(),
                ], 404);
            }

            if ($exception instanceof AuthorizationException) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => $exception->getMessage(),
                ], 403);
            }

            // Default catch-all for API exceptions
            return response()->json([
                'error' => 'Server Error',
                'message' => $exception->getMessage(),
            ], 422);
        }

        // Fallback to default HTML error page for non-API requests
        return parent::render($request, $e);
    }
}
