<?php

namespace App\Exceptions;

use App\Services\Auth\IdleSessionService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(){
        $this->reportable(function (Throwable $e) {
            if ($e instanceof RollOverException) {
                Log::error($e->getLogMessage());
                // Optionally, you can add more logging or notification logic here
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception){
        $shouldReturnJson = $this->shouldReturnJsonResponse($request);
        $idleSession = app(IdleSessionService::class);
        $guard = $idleSession->resolveGuard($request) ?? 'web';
        $loginRoute = $idleSession->loginRouteName($guard);

        if ($exception instanceof TokenMismatchException) {
            if ($shouldReturnJson) {
                return response()->json([
                    'error' => [
                        'message' => 'Your session has expired. Please refresh and try again.',
                        'status' => 419,
                    ],
                ], 419);
            }

            return redirect()->route($loginRoute)
                ->with('message', 'Your session has expired. Please try again.');
        }

        // Handle RollOverException (keep existing logic)
        if ($exception instanceof RollOverException) {
            if ($shouldReturnJson) {
                return response()->json(['error' => $exception->getDisplayMessage()], 500);
            }

            return redirect()->route('rollover.error')
                ->with('error_message', $exception->getDisplayMessage())
                ->with('error_code', 'YR-' . time());
        }

        // Handle API requests
        if ($shouldReturnJson) {
            return $this->handleApiException($request, $exception);
        }

        // Handle 404 errors
        if ($exception instanceof ModelNotFoundException) {
            return response()->view('errors.404', [
                'exception' => $exception
            ], 404);
        }

        // If it's a severe error that should be logged
        if ($this->shouldReport($exception)) {
            Log::error('Application Error', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]);
        }

        // For all other exceptions, let Laravel handle it normally
        // This ensures we don't break any other error handling
        $response = parent::render($request, $exception);
        
        // If it's a 500 error, show our custom 500 page
        if ($response->getStatusCode() === 500) {
            return response()->view('errors.500', [
                'exception' => config('app.debug') ? $exception : null
            ], 500);
        }

        return $response;
    }

    private function shouldReturnJsonResponse($request): bool
    {
        $acceptHeader = strtolower((string) $request->header('Accept', ''));

        return $request->expectsJson()
            || $request->wantsJson()
            || $request->ajax()
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($acceptHeader, 'application/json')
            || str_contains($acceptHeader, '+json');
    }

    /**
     * Handle API exceptions
     */
    private function handleApiException($request, Throwable $exception){
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $exception->errors(),
            ], 422);
        }

        $status = match(true) {
            $exception instanceof ModelNotFoundException => 404,
            $exception instanceof TokenMismatchException => 419,
            $exception instanceof AuthenticationException => 401,
            $exception instanceof AuthorizationException => 403,
            default => 500
        };

        return response()->json([
            'error' => [
                'message' => $exception->getMessage() ?: 'An error occurred',
                'status' => $status
            ]
        ], $status);
    }
}
