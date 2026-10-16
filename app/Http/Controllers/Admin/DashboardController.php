<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats
        $totalBookings = Booking::count();
        $activeServicesCount = Booking::active()->count();
        $completedJobsCount = JobOrder::status(JobOrder::STATUS_COMPLETED)->count();
        $totalRevenue = (float) Payment::verified()->sum('amount');

        // Current Month Revenue (reflects all verified payments this month, including reservation fees)
        $currentMonthRevenue = (float) Payment::verified()
            ->where(function ($q) {
                $q->whereYear('verified_at', now()->year)
                  ->whereMonth('verified_at', now()->month)
                  ->orWhere(function ($q2) {
                      $q2->whereNull('verified_at')
                         ->whereYear('paid_at', now()->year)
                         ->whereMonth('paid_at', now()->month);
                  })
                  ->orWhere(function ($q3) {
                      $q3->whereNull('verified_at')
                         ->whereNull('paid_at')
                         ->whereYear('created_at', now()->year)
                         ->whereMonth('created_at', now()->month);
                  });
            })
            ->sum('amount');

        $lastMonthRevenue = (float) Payment::verified()
            ->where(function ($q) {
                $q->whereYear('verified_at', now()->subMonth()->year)
                  ->whereMonth('verified_at', now()->subMonth()->month)
                  ->orWhere(function ($q2) {
                      $q2->whereNull('verified_at')
                         ->whereYear('paid_at', now()->subMonth()->year)
                         ->whereMonth('paid_at', now()->subMonth()->month);
                  })
                  ->orWhere(function ($q3) {
                      $q3->whereNull('verified_at')
                         ->whereNull('paid_at')
                         ->whereYear('created_at', now()->subMonth()->year)
                         ->whereMonth('created_at', now()->subMonth()->month);
                  });
            })
            ->sum('amount');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : ($currentMonthRevenue > 0 ? 100 : 0);

        // Recent Booking Requests
        $recentBookings = Booking::with(['services', 'user'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->booking_number,
                    'customer' => $booking->customer_name ?? ($booking->user?->name ?? 'Unknown'),
                    'service' => $booking->services->first()?->name ?? 'Custom Customization',
                    'date' => $booking->preferred_date ? $booking->preferred_date->format('M d, Y') : '',
                    'status' => $booking->status,
                ];
            });

        // Quick Stats
        $totalCustomers = User::customers()->count();
        $activeMechanics = User::mechanics()->active()->count();
        $todayAppointments = Booking::scheduledOn(now()->toDateString())->count();

        $totalJobs = JobOrder::count();
        $completionRate = $totalJobs > 0
            ? round(($completedJobsCount / $totalJobs) * 100)
            : 0;

        // Chart 1: Monthly Services Completed (Last 6 Months)
        $months = [];
        $servicesData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            $months[] = $monthName;

            $count = Booking::status(Booking::STATUS_COMPLETED)
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->count();
            $servicesData[] = $count;
        }

        // Chart 2: Revenue Analytics (₱) (Last 6 Months)
        $revenueData = [];
        foreach ($months as $index => $monthName) {
            $date = now()->subMonths(5 - $index);
            $sum = Payment::verified()
                ->where(function ($q) use ($date) {
                    $q->whereYear('verified_at', $date->year)
                      ->whereMonth('verified_at', $date->month)
                      ->orWhere(function ($q2) use ($date) {
                          $q2->whereNull('verified_at')
                             ->whereYear('paid_at', $date->year)
                             ->whereMonth('paid_at', $date->month);
                      })
                      ->orWhere(function ($q3) use ($date) {
                          $q3->whereNull('verified_at')
                             ->whereNull('paid_at')
                             ->whereYear('created_at', $date->year)
                             ->whereMonth('created_at', $date->month);
                      });
                })
                ->sum('amount');
            $revenueData[] = (float) $sum;
        }

        // Chart 3: Service Popularity (Pie Chart)
        $popularServices = DB::table('booking_services')
            ->join('services', 'booking_services.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as count'))
            ->groupBy('services.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $serviceLabels = $popularServices->pluck('name')->toArray();
        $serviceCounts = $popularServices->pluck('count')->toArray();

        // Fallbacks for empty states to keep visual appeal if DB is empty
        if (empty($serviceLabels)) {
            $serviceLabels = ['Engine Customization', 'Paint Job', 'Body Kit', 'Turbo Install', 'Exhaust'];
            $serviceCounts = [0, 0, 0, 0, 0];
        }

        return view('admin.dashboard', [
            'totalBookings' => $totalBookings,
            'activeServicesCount' => $activeServicesCount,
            'completedJobsCount' => $completedJobsCount,
            'totalRevenue' => $totalRevenue,
            'currentMonthRevenue' => $currentMonthRevenue,
            'revenueGrowth' => $revenueGrowth,
            'recentBookings' => $recentBookings,
            'totalCustomers' => $totalCustomers,
            'activeMechanics' => $activeMechanics,
            'todayAppointments' => $todayAppointments,
            'completionRate' => $completionRate,
            'chartMonths' => $months,
            'servicesData' => $servicesData,
            'revenueData' => $revenueData,
            'serviceLabels' => $serviceLabels,
            'serviceCounts' => $serviceCounts,
        ]);
    }
}
