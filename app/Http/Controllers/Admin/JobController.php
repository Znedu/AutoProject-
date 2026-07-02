<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\AssignJobRequest;
use App\Models\JobOrder;
use App\Models\User;
use App\Services\Booking\JobAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'unassigned');

        $jobs = JobOrder::query()
            ->with([
                'booking.user',
                'booking.vehicle',
                'booking.bookingServices.service',
                'booking.quotations' => fn ($q) => $q->latestVersion()->limit(1),
                'mechanic',
                'assigner',
            ])
            ->whereHas('booking', fn ($q) => $q->where('status', \App\Models\Booking::STATUS_APPROVED))
            ->when($filter === 'unassigned', fn ($q) => $q->where('status', JobOrder::STATUS_PENDING))
            ->when($filter === 'assigned',   fn ($q) => $q->where('status', JobOrder::STATUS_ASSIGNED))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Load available mechanics for the assignment dropdown
        $mechanics = User::mechanics()
            ->active()
            ->withCount(['assignedJobOrders as active_jobs_count' => fn ($q) => $q->assigned()])
            ->orderBy('name')
            ->get();

        $stats = [
            'unassigned'    => JobOrder::where('status', JobOrder::STATUS_PENDING)
                ->whereHas('booking', fn ($q) => $q->where('status', \App\Models\Booking::STATUS_APPROVED))
                ->count(),
            'assigned'      => JobOrder::where('status', JobOrder::STATUS_ASSIGNED)
                ->whereHas('booking', fn ($q) => $q->where('status', \App\Models\Booking::STATUS_APPROVED))
                ->count(),
            'in_progress'   => JobOrder::where('status', JobOrder::STATUS_IN_PROGRESS)->count(),
            'mechanics_available' => User::mechanics()->active()->count(),
        ];

        return view('admin.jobs.index', [
            'jobs'           => $jobs,
            'mechanics'      => $mechanics,
            'stats'          => $stats,
            'selectedFilter' => $filter,
        ]);
    }

    public function assign(AssignJobRequest $request, JobOrder $job, JobAssignmentService $service): RedirectResponse
    {
        try {
            $service->assign($job, $request->user(), $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $mechanic = User::find($request->validated('mechanic_id'));

        return back()->with(
            'success',
            "Job {$job->job_number} has been assigned to {$mechanic->name}."
        );
    }
}
