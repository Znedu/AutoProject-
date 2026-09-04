<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $preset = $request->input('preset', 'all');
        $startDateParam = $request->input('start_date');
        $endDateParam = $request->input('end_date');

        $startDate = null;
        $endDate = null;
        $dateRangeLabel = 'All Time';

        switch ($preset) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                $dateRangeLabel = 'Today (' . now()->format('M j, Y') . ')';
                break;
            case 'this_month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                $dateRangeLabel = 'This Month (' . now()->format('F Y') . ')';
                break;
            case 'last_30_days':
                $startDate = now()->subDays(30)->startOfDay();
                $endDate = now()->endOfDay();
                $dateRangeLabel = 'Last 30 Days (' . $startDate->format('M j') . ' - ' . $endDate->format('M j, Y') . ')';
                break;
            case 'last_90_days':
                $startDate = now()->subDays(90)->startOfDay();
                $endDate = now()->endOfDay();
                $dateRangeLabel = 'Last 90 Days (' . $startDate->format('M j') . ' - ' . $endDate->format('M j, Y') . ')';
                break;
            case 'this_year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                $dateRangeLabel = 'This Year (' . now()->format('Y') . ')';
                break;
            case 'custom':
                if ($startDateParam && $endDateParam) {
                    $startDate = Carbon::parse($startDateParam)->startOfDay();
                    $endDate = Carbon::parse($endDateParam)->endOfDay();
                    $dateRangeLabel = $startDate->format('M j, Y') . ' - ' . $endDate->format('M j, Y');
                } else {
                    $preset = 'all';
                    $dateRangeLabel = 'All Time';
                }
                break;
            case 'all':
            default:
                $preset = 'all';
                $dateRangeLabel = 'All Time';
                break;
        }

        // 1. Payments Query
        $paymentsQuery = Payment::verified();
        if ($startDate) {
            $paymentsQuery->where(function ($q) use ($startDate) {
                $q->where('verified_at', '>=', $startDate)
                  ->orWhere(function ($q2) use ($startDate) {
                      $q2->whereNull('verified_at')->where('created_at', '>=', $startDate);
                  });
            });
        }
        if ($endDate) {
            $paymentsQuery->where(function ($q) use ($endDate) {
                $q->where('verified_at', '<=', $endDate)
                  ->orWhere(function ($q2) use ($endDate) {
                      $q2->whereNull('verified_at')->where('created_at', '<=', $endDate);
                  });
            });
        }

        $totalRevenue = (float) (clone $paymentsQuery)->sum('amount');
        $reservationFeesCollected = (float) (clone $paymentsQuery)->where('type', Payment::TYPE_RESERVATION_FEE)->sum('amount');
        $avgServiceValue = (float) ((clone $paymentsQuery)->avg('amount') ?? 0);

        // 2. Finalized Revenue & Outstanding Balances (Quotations)
        $quotationsQuery = Quotation::where('type', Quotation::TYPE_FINAL)->where('status', Quotation::STATUS_APPROVED);
        if ($startDate) {
            $quotationsQuery->where('updated_at', '>=', $startDate);
        }
        if ($endDate) {
            $quotationsQuery->where('updated_at', '<=', $endDate);
        }

        $totalFinalizedRevenue = (float) (clone $quotationsQuery)->sum('final_total');
        $outstandingBalances = (float) (clone $quotationsQuery)->where('balance_due_snapshot', '>', 0)->sum('balance_due_snapshot');

        // 3. Bookings
        $bookingsQuery = Booking::query();
        if ($startDate) {
            $bookingsQuery->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $bookingsQuery->where('created_at', '<=', $endDate);
        }

        $totalBookings = (clone $bookingsQuery)->count();
        $completedCount = (clone $bookingsQuery)->status(Booking::STATUS_COMPLETED)->count();
        $inProgressCount = (clone $bookingsQuery)->status(Booking::STATUS_IN_PROGRESS)->count();
        $pendingCount = (clone $bookingsQuery)->status(Booking::STATUS_PENDING)->count();
        $cancelledCount = (clone $bookingsQuery)->status(Booking::STATUS_CANCELLED)->count();

        // 4. Job Orders & Completion Rate
        $jobsQuery = JobOrder::query();
        if ($startDate) {
            $jobsQuery->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $jobsQuery->where('created_at', '<=', $endDate);
        }

        $totalJobs = (clone $jobsQuery)->count();
        $completedJobs = (clone $jobsQuery)->where('status', JobOrder::STATUS_COMPLETED)->count();
        $completionRate = $totalJobs > 0 ? round(($completedJobs / $totalJobs) * 100) : 0;

        // 5. Periodic Chart Timeline (6 months default or span of range)
        $chartEnd = $endDate ? $endDate->copy() : now();
        $chartStart = $startDate ? $startDate->copy() : now()->subMonths(5)->startOfMonth();

        $diffInMonths = max(5, (int) $chartStart->diffInMonths($chartEnd));
        if ($diffInMonths > 12) {
            $diffInMonths = 11;
        }

        $months = [];
        $revenueData = [];
        $bookingsData = [];
        $newCustomersData = [];
        $returningCustomersData = [];

        for ($i = $diffInMonths; $i >= 0; $i--) {
            $date = $chartEnd->copy()->subMonths($i);
            $months[] = $date->format('M');

            // Monthly Revenue
            $rev = (float) Payment::verified()
                ->where(function ($q) use ($date) {
                    $q->whereYear('verified_at', $date->year)
                      ->whereMonth('verified_at', $date->month)
                      ->orWhere(function ($q2) use ($date) {
                          $q2->whereNull('verified_at')
                             ->whereYear('created_at', $date->year)
                             ->whereMonth('created_at', $date->month);
                      });
                })->sum('amount');
            $revenueData[] = $rev;

            // Monthly Bookings
            $bCount = Booking::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $bookingsData[] = $bCount;

            // New Customers registered
            $ncCount = User::customers()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $newCustomersData[] = $ncCount;

            // Returning customers
            $returningCount = Booking::select('user_id')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->groupBy('user_id')
                ->havingRaw('count(*) > 1')
                ->get()
                ->count();
            $returningCustomersData[] = $returningCount;
        }

        // 6. Service Popularity
        $popQuery = DB::table('booking_services')
            ->join('services', 'booking_services.service_id', '=', 'services.id')
            ->join('bookings', 'booking_services.booking_id', '=', 'bookings.id')
            ->select('services.name', DB::raw('count(*) as count'));

        if ($startDate) {
            $popQuery->where('bookings.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $popQuery->where('bookings.created_at', '<=', $endDate);
        }

        $popularServices = $popQuery
            ->groupBy('services.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $servicePopularityLabels = $popularServices->pluck('name')->toArray();
        $servicePopularityCounts = array_map('intval', $popularServices->pluck('count')->toArray());

        // 7. Service Performance Summary Table
        $perfQuery = DB::table('services')
            ->leftJoin('booking_services', 'services.id', '=', 'booking_services.service_id')
            ->leftJoin('bookings', function ($join) use ($startDate, $endDate) {
                $join->on('booking_services.booking_id', '=', 'bookings.id');
                if ($startDate) {
                    $join->where('bookings.created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $join->where('bookings.created_at', '<=', $endDate);
                }
            })
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
                $bCount = (int) $row->bookings_count;
                $tRev = (float) $row->total_revenue;
                $avgValue = $bCount > 0 ? round($tRev / $bCount) : 0;

                return [
                    'name' => $row->name,
                    'bookings' => $bCount,
                    'revenue' => $tRev,
                    'avg' => $avgValue,
                    'trend' => '+0%',
                ];
            })
            ->toArray();

        // Fallbacks for empty database to keep charts visually complete
        if (empty($servicePopularityLabels)) {
            $servicePopularityLabels = ['Engine Customization', 'Paint Job', 'Body Kit', 'Turbo Install', 'Exhaust'];
            $servicePopularityCounts = [0, 0, 0, 0, 0];
        }
        if (empty($perfQuery)) {
            $perfQuery = [
                ['name' => 'Engine Customization', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
                ['name' => 'Paint Job', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
                ['name' => 'Body Kit', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
                ['name' => 'Turbo Installation', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
                ['name' => 'Exhaust Fabrication', 'bookings' => 0, 'revenue' => 0, 'avg' => 0, 'trend' => '+0%'],
            ];
        }

        // 8. CSV Export handling
        if ($request->has('export')) {
            $exportType = $request->input('export');
            $filename = "autoproject_report_{$exportType}_" . now()->format('Y-m-d') . ".csv";

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use (
                $exportType, $months, $revenueData, $bookingsData,
                $servicePopularityLabels, $servicePopularityCounts,
                $newCustomersData, $returningCustomersData,
                $perfQuery, $dateRangeLabel, $totalRevenue,
                $totalFinalizedRevenue, $outstandingBalances,
                $totalBookings, $completionRate
            ) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM for Microsoft Excel compatibility
                fputs($file, "\xEF\xBB\xBF");

                if ($exportType === 'revenue') {
                    fputcsv($file, ['AUTOPROJECT+ - MONTHLY REVENUE & BOOKINGS']);
                    fputcsv($file, ['Date Range:', $dateRangeLabel]);
                    fputcsv($file, []);
                    fputcsv($file, ['Month', 'Revenue (PHP)', 'Bookings Count']);
                    foreach ($months as $idx => $m) {
                        fputcsv($file, [$m, $revenueData[$idx] ?? 0, $bookingsData[$idx] ?? 0]);
                    }
                } elseif ($exportType === 'popularity') {
                    fputcsv($file, ['AUTOPROJECT+ - SERVICE POPULARITY']);
                    fputcsv($file, ['Date Range:', $dateRangeLabel]);
                    fputcsv($file, []);
                    fputcsv($file, ['Service Name', 'Bookings Count']);
                    foreach ($servicePopularityLabels as $idx => $label) {
                        fputcsv($file, [$label, $servicePopularityCounts[$idx] ?? 0]);
                    }
                } elseif ($exportType === 'trends') {
                    fputcsv($file, ['AUTOPROJECT+ - CUSTOMER ACTIVITY TRENDS']);
                    fputcsv($file, ['Date Range:', $dateRangeLabel]);
                    fputcsv($file, []);
                    fputcsv($file, ['Month', 'New Customers', 'Returning Customers']);
                    foreach ($months as $idx => $m) {
                        fputcsv($file, [$m, $newCustomersData[$idx] ?? 0, $returningCustomersData[$idx] ?? 0]);
                    }
                } elseif ($exportType === 'summary') {
                    fputcsv($file, ['AUTOPROJECT+ - SERVICE PERFORMANCE SUMMARY']);
                    fputcsv($file, ['Date Range:', $dateRangeLabel]);
                    fputcsv($file, []);
                    fputcsv($file, ['Service Name', 'Bookings Count', 'Revenue (PHP)', 'Avg Value (PHP)', 'Trend']);
                    foreach ($perfQuery as $row) {
                        fputcsv($file, [$row['name'], $row['bookings'], $row['revenue'], $row['avg'], $row['trend']]);
                    }
                } else { // 'all'
                    fputcsv($file, ['AUTOPROJECT+ EXECUTIVE BUSINESS & ANALYTICS REPORT']);
                    fputcsv($file, ['Date Range:', $dateRangeLabel]);
                    fputcsv($file, ['Generated On:', now()->format('F j, Y h:i A')]);
                    fputcsv($file, []);
                    fputcsv($file, ['KEY PERFORMANCE INDICATORS']);
                    fputcsv($file, ['Finalized Billing Revenue', 'PHP ' . number_format($totalFinalizedRevenue, 2)]);
                    fputcsv($file, ['Total Payments Collected', 'PHP ' . number_format($totalRevenue, 2)]);
                    fputcsv($file, ['Outstanding Balances', 'PHP ' . number_format($outstandingBalances, 2)]);
                    fputcsv($file, ['Total Bookings', $totalBookings]);
                    fputcsv($file, ['Completion Rate', $completionRate . '%']);
                    fputcsv($file, []);
                    fputcsv($file, ['MONTHLY OVERVIEW']);
                    fputcsv($file, ['Month', 'Revenue (PHP)', 'Bookings', 'New Customers', 'Returning Customers']);
                    foreach ($months as $idx => $m) {
                        fputcsv($file, [
                            $m,
                            $revenueData[$idx] ?? 0,
                            $bookingsData[$idx] ?? 0,
                            $newCustomersData[$idx] ?? 0,
                            $returningCustomersData[$idx] ?? 0,
                        ]);
                    }
                    fputcsv($file, []);
                    fputcsv($file, ['SERVICE PERFORMANCE SUMMARY']);
                    fputcsv($file, ['Service Name', 'Bookings Count', 'Revenue (PHP)', 'Avg Value (PHP)', 'Trend']);
                    foreach ($perfQuery as $row) {
                        fputcsv($file, [$row['name'], $row['bookings'], $row['revenue'], $row['avg'], $row['trend']]);
                    }
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('admin.reports', [
            'preset' => $preset,
            'startDateParam' => $startDateParam,
            'endDateParam' => $endDateParam,
            'dateRangeLabel' => $dateRangeLabel,
            'totalRevenue' => $totalRevenue,
            'totalFinalizedRevenue' => $totalFinalizedRevenue,
            'outstandingBalances' => $outstandingBalances,
            'reservationFeesCollected' => $reservationFeesCollected,
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
            'servicePerformance' => $perfQuery,
        ]);
    }
}
