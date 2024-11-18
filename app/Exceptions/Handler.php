<?php

namespace App\Exceptions;

use App\Traits\LoggableTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    use LoggableTrait;
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
    }

    protected function shouldReturnJson($request, Throwable $e)
    {
        return true;
    }

    // public function render($request, Throwable $exception)
    // {
    //     $this->logError($exception, $request->all());

    //     return response()->json([
    //         'message' => 'Lỗi server'
    //     ], Response::HTTP_INTERNAL_SERVER_ERROR);
    // }
}
