<?php

namespace App\Exceptions;

use App\BirthdayBooking\BirthdayAvailabilityService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Psr\Log\LogLevel;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
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
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (QueryException $exception, Request $request) {
            if (! config('birthday_booking.enabled')
                || ! BirthdayAvailabilityService::isSlotConflict($exception)) {
                return null;
            }

            return ValidationException::withMessages([
                'reserve_time' => trans('birthday_booking.slot_unavailable'),
            ])->toResponse($request);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
