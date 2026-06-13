@extends('layouts.dashboard')

@section('title', 'Reports & Analytics | AutoProject+')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Reports & Analytics</h1>
            <p class="text-gray-600 dark:text-gray-400">Comprehensive business insights and performance metrics.</p>
        </div>
        <div class="flex gap-3">
            <x-button variant="secondary" size="sm" @click="showToast.info('Selecting Date Range')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Date Range
            </x-button>
            <x-button variant="accent" size="sm" @click="showToast.success('Exporting complete analytics dataset...')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export All
            </x-button>
        </div>
    </div>

    {{-- Key Metrics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-white">
        <x-card class="bg-gradient-to-br from-[#E63946] to-[#D62839] border-transparent shadow-lg p-6">
            <p class="text-white/80 text-sm mb-1">Total Revenue</p>
            <p class="text-3xl font-bold">₱{{ number_format($totalRevenue) }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold">
                <x-icon name="chevron-right" class="w-4 h-4 rotate-270" />
                <span>+18% vs last period</span>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-[#457B9D] to-[#5A8FB0] border-transparent shadow-lg p-6">
            <p class="text-white/80 text-sm mb-1">Total Bookings</p>
            <p class="text-3xl font-bold">{{ $totalBookings }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold">
                <x-icon name="chevron-right" class="w-4 h-4 rotate-270" />
                <span>+12% vs last period</span>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-green-500 to-green-600 border-transparent shadow-lg p-6">
            <p class="text-white/80 text-sm mb-1">Completion Rate</p>
            <p class="text-3xl font-bold">{{ $completionRate }}%</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold">
                <x-icon name="chevron-right" class="w-4 h-4 rotate-270" />
                <span>+3% vs last period</span>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-[#1F2937] to-[#374151] border-transparent shadow-lg p-6">
            <p class="text-white/80 text-sm mb-1">Avg. Service Value</p>
            <p class="text-3xl font-bold">₱{{ number_format($avgServiceValue) }}</p>
            <div class="flex items-center gap-1.5 mt-2 text-xs font-semibold">
                <x-icon name="chevron-right" class="w-4 h-4 rotate-270" />
                <span>+5% vs last period</span>
            </div>
        </x-card>
    </div>

    {{-- Monthly Revenue & Bookings Area Chart --}}
    <x-card>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Monthly Revenue & Bookings</h2>
            <x-button variant="ghost" size="sm" @click="showToast.success('Exporting revenue report...')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
            </x-button>
        </div>
        <div x-data="{
            init() {
                const ctx = document.getElementById('monthlyRevenueBookingsChart').getContext('2d');
                
                const gradRevenue = ctx.createLinearGradient(0, 0, 0, 350);
                gradRevenue.addColorStop(0, 'rgba(230, 57, 70, 0.4)');
                gradRevenue.addColorStop(1, 'rgba(230, 57, 70, 0)');

                const gradBookings = ctx.createLinearGradient(0, 0, 0, 350);
                gradBookings.addColorStop(0, 'rgba(69, 123, 157, 0.4)');
                gradBookings.addColorStop(1, 'rgba(69, 123, 157, 0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @js($months),
                        datasets: [
                            {
                                label: 'Revenue (₱)',
                                data: @js($revenueData),
                                borderColor: '#E63946',
                                backgroundColor: gradRevenue,
                                borderWidth: 3,
                                fill: true,
                                tension: 0.3,
                                yAxisID: 'yRevenue'
                            },
                            {
                                label: 'Bookings',
                                data: @js($bookingsData),
                                borderColor: '#457B9D',
                                backgroundColor: gradBookings,
                                borderWidth: 3,
                                fill: true,
                                tension: 0.3,
                                yAxisID: 'yBookings'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: '#6b7280' }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.datasetIndex === 0) {
                                            label += '₱' + context.raw.toLocaleString();
                                        } else {
                                            label += context.raw;
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#6b7280' }
                            },
                            yRevenue: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: { color: 'rgba(107, 114, 128, 0.1)' },
                                ticks: {
                                    color: '#6b7280',
                                    callback: function(value) {
                                        return '₱' + (value / 1000) + 'K';
                                    }
                                }
                            },
                            yBookings: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: { color: '#6b7280' }
                            }
                        }
                    }
                });
            }
        }" class="h-[350px] relative">
            <canvas id="monthlyRevenueBookingsChart"></canvas>
        </div>
    </x-card>

    {{-- Popularity & Status distribution charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Service Popularity Horizontal Bar Chart --}}
        <x-card>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Service Popularity</h2>
                <x-button variant="ghost" size="sm" @click="showToast.success('Exporting service popularity report...')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export
                </x-button>
            </div>
            <div x-data="{
                init() {
                    const ctx = document.getElementById('servicePopularityChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: @js($servicePopularityLabels),
                            datasets: [{
                                label: 'Bookings Completed',
                                data: @js($servicePopularityCounts),
                                backgroundColor: '#E63946',
                                borderRadius: 8,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    grid: { color: 'rgba(107, 114, 128, 0.1)' },
                                    ticks: { color: '#6b7280' }
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { color: '#6b7280' }
                                }
                            }
                        }
                    });
                }
            }" class="h-[300px] relative">
                <canvas id="servicePopularityChart"></canvas>
            </div>
        </x-card>

        {{-- Booking Status Distribution Pie Chart --}}
        <x-card>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Booking Status Distribution</h2>
                <x-button variant="ghost" size="sm" @click="showToast.success('Exporting status report...')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export
                </x-button>
            </div>
            <div x-data="{
                init() {
                    const ctx = document.getElementById('bookingStatusDistributionChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: ['Completed', 'In Progress', 'Pending', 'Cancelled'],
                            datasets: [{
                                data: @js($statusCounts),
                                backgroundColor: ['#10B981', '#457B9D', '#F59E0B', '#EF4444'],
                                borderWidth: 2,
                                borderColor: document.documentElement.classList.contains('dark') ? '#151515' : '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        color: '#6b7280',
                                        boxWidth: 12
                                    }
                                }
                            }
                        }
                    });
                }
            }" class="h-[300px] relative">
                <canvas id="bookingStatusDistributionChart"></canvas>
            </div>
        </x-card>
    </div>

    {{-- Customer Activity Trends Line Chart --}}
    <x-card>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Customer Activity Trends</h2>
            <x-button variant="ghost" size="sm" @click="showToast.success('Exporting customer activity trends...')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
            </x-button>
        </div>
        <div x-data="{
            init() {
                const ctx = document.getElementById('customerActivityTrendsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @js($months),
                        datasets: [
                            {
                                label: 'New Customers',
                                data: @js($newCustomersData),
                                borderColor: '#E63946',
                                backgroundColor: 'transparent',
                                borderWidth: 3,
                                pointBackgroundColor: '#E63946',
                                tension: 0.3
                            },
                            {
                                label: 'Returning Customers',
                                data: @js($returningCustomersData),
                                borderColor: '#457B9D',
                                backgroundColor: 'transparent',
                                borderWidth: 3,
                                pointBackgroundColor: '#457B9D',
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: '#6b7280' }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#6b7280' }
                            },
                            y: {
                                grid: { color: 'rgba(107, 114, 128, 0.1)' },
                                ticks: { color: '#6b7280' }
                            }
                        }
                    }
                });
            }
        }" class="h-[300px] relative">
            <canvas id="customerActivityTrendsChart"></canvas>
        </div>
    </x-card>

    {{-- Service Performance Summary Table --}}
    <x-card>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Service Performance Summary</h2>
            <x-button variant="ghost" size="sm" @click="showToast.success('Exporting summary tables...')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
            </x-button>
        </div>
        <div class="p-0 overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
            <x-table>
                <x-table-header>
                    <x-table-row>
                        <x-table-head>Service</x-table-head>
                        <x-table-head>Bookings</x-table-head>
                        <x-table-head>Revenue</x-table-head>
                        <x-table-head>Avg. Value</x-table-head>
                        <x-table-head>Trend</x-table-head>
                    </x-table-row>
                </x-table-header>
                <x-table-body>
                    @foreach ($servicePerformance as $service)
                        <x-table-row>
                            <x-table-cell>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $service['name'] }}</span>
                            </x-table-cell>
                            <x-table-cell>
                                <span class="text-gray-600 dark:text-gray-400">{{ $service['bookings'] }}</span>
                            </x-table-cell>
                            <x-table-cell>
                                <span class="text-gray-600 dark:text-gray-400">₱{{ number_format($service['revenue']) }}</span>
                            </x-table-cell>
                            <x-table-cell>
                                <span class="text-gray-600 dark:text-gray-400">₱{{ number_format(round($service['revenue'] / $service['bookings'])) }}</span>
                            </x-table-cell>
                            <x-table-cell>
                                <span class="text-green-500 font-semibold flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                    {{ $service['trend'] }}
                                </span>
                            </x-table-cell>
                        </x-table-row>
                    @endforeach
                </x-table-body>
            </x-table>
        </div>
    </x-card>
</div>
@endsection
