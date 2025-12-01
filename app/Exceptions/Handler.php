<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\EventDispatcher\DependencyInjection\ExtractingEventDispatcher;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        // Add exceptions you don’t want logged
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Custom logging if needed
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {

                // Custom: Date conflict
                if ($e instanceof DateConflictException) {
                    return response()->json([
                        'error'   => 'Date Conflict',
                        'message' => $e->getMessage(),
                    ], 422);
                }

                if ($e instanceof ExtraAttributesException) {
                    return response()->json([
                        'error' => $e->getMessage() . $e->getAttributes()
                    ], 422);
                }

                if ($e instanceof ModelNotFoundException) {
                    return response()->json([
                        'error'   => 'Resource Not Found',
                        'message' => $e->getMessage(),
                    ], 404);
                }

                if ($e instanceof AuthorizationException) {
                    return response()->json([
                        'error'   => 'Unauthorized',
                        'message' => $e->getMessage(),
                    ], 403);
                }

                if ($e instanceof CompletedBookingException) {
                    return response()->json([
                        'error'   => 'Invalid Booking Status',
                        'message' => $e->getMessage(),
                    ], 422);
                }

                if ($e instanceof CanceledBookingException) {
                    return response()->json([
                        'error'   => 'Invalid Booking Status',
                        'message' => $e->getMessage(),
                    ], 422);
                }

                return response()->json([
                    'error' => $e->getMessage(),
                ], 500);
            }
        });
    }
}
