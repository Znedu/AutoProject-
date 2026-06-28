<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // General stats
        $totalRevenue = Payment::verified()->sum('amount');
        $totalBookings = Booking::count();
        
        $completedJobs = JobOrder::status(JobOrder::STATUS_COMPLETED)->count();
        $totalJobs = JobOrder::count();
        $completionRate = $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100) : 0;
        
        $avgServiceValue = Payment::verified()->avg('amount') ?? 0;

        // Last 6 months labels
        $months = [];
        $revenueData = [];
        $bookingsData = [];
        $newCustomersData = [];
        $returningCustomersData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M');
            $months[] = $monthName;

            // Monthly Revenue
            $revenueData[] = (float) Payment::verified()
                ->whereYear('verified_at', $date->year)
                ->whereMonth('verified_at', $date->month)
                ->sum('amount');

            // Monthly Bookings
            $bookingsData[] = Booking::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            // New Customers registered
            $newCustomersData[] = User::customers()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            // Returning customers (placeholder / calculation: bookings > 1 in this month)
            $returningCount = Booking::select('user_id')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->groupBy('user_id')
                ->havingRaw('count(*) > 1')
                ->get()
                ->count();
            $returningCustomersData[] = $returningCount;
        }

        // Service Popularity
        $popularServices = DB::table('booking_services')
            ->join('services', 'booking_services.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as count'))
            ->groupBy('services.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $servicePopularityLabels = $popularServices->pluck('name')->toArray();
        $servicePopularityCounts = $popularServices->pluck('count')->toArray();

        // Booking Status Distribution
        $completedCount = Booking::status(Booking::STATUS_COMPLETED)->count();
        $inProgressCount = Booking::status(Booking::STATUS_IN_PROGRESS)->count();
        $pendingCount = Booking::status(Booking::STATUS_PENDING)->count();
        $cancelledCount = Booking::status(Booking::STATUS_CANCELLED)->count();

        // Service Performance Table
        $servicePerformance = DB::table('services')
            ->leftJoin('booking_services', 'services.id', '=', 'booking_services.service_id')
            ->leftJoin('bookings', 'booking_services.booking_id', '=', 'bookings.id')
            ->leftJoin('payments', function ($join) {
                $join->on('bookings.id', '=', 'payments.booking_id')
                     ->where('payments.status', '=', Payment::STATUS_VERIFIED);
            })
            ->select(
                'services.name',
                DB::raw('count(distinct bookings.id) as bookings_count'),
                DB::raw('coalesce(sum(payments.amount), 0) as total_revenue')
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('bookings_count')
            ->get()
            ->map(function ($row) {
                $avgValue = $row->bookings_count > 0 ? round($row->total_revenue / $row->bookings_count) : 0;
                return [
                    'name' => $row->name,
                    'bookings' => $row->bookings_count,
                    'revenue' => $row->total_revenue,
                    'avg' => $avgValue,
                    'trend' => '+0%', // Dynamic trend can be calculated if needed, placeholder is fine
                ];
            })
            ->toArray();

        // Fallbacks for empty database to keep dashboard beautiful
        if (empty($servicePopularityLabels)) {
            $servicePopularityLabels = ['Engine Customization', 'Paint Job', 'Body Kit', 'Turbo Install', 'Exhaust'];
            $servicePopularityCounts = [0, 0, 0, 0, 0];
        }
        if (empty($servicePerformance)) {
            $servicePerformance = [
                ['name' => 'Engine Customization', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
                ['name' => 'Paint Job', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
                ['name' => 'Body Kit', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
                ['name' => 'Turbo Installation', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
                ['name' => 'Exhaust Fabrication', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
            ];
        }

        return view('admin.reports', [
            'totalRevenue' => $totalRevenue,
            'totalBookings' => $totalBookings,
            'completionRate' => $completionRate,
            'avgServiceValue' => $avgServiceValue,
            'months' => $months,
            'revenueData' => $revenueData,
            'bookingsData' => $bookingsData,
            'servicePopularityLabels' => $servicePopularityLabels,
            'servicePopularityCounts' => $servicePopularityCounts,
            'statusCounts' => [$completedCount, $inProgressCount, $pendingCount, $cancelledCount],
            'newCustomersData' => $newCustomersData,
            'returningCustomersData' => $returningCustomersData,
            'servicePerformance' => $servicePerformance,
        ]);
    }
}
