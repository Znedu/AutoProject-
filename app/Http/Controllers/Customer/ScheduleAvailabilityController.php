<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CheckScheduleRequest;
use App\Services\Booking\ScheduleAvailabilityService;
use Illuminate\Http\JsonResponse;

class ScheduleAvailabilityController extends Controller
{
    public function __invoke(CheckScheduleRequest $request, ScheduleAvailabilityService $schedule): JsonResponse
    {
        $availability = $schedule->availabilityForDate($request->validated('date'));

        return response()->json($availability);
    }
}
