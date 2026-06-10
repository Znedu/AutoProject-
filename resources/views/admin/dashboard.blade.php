@extends('layouts.dashboard')

@section('title', 'Admin Dashboard | AutoProject+')

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Admin Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400">System overview and analytics.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card
            title="Total Bookings"
            value="248"
            color="blue"
            :trend="['value' => '+12% from last month', 'isPositive' => true]"
        >
            <x-slot:icon>
                <x-icon name="calendar" class="w-6 h-6" />
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card
            title="Active Services"
            value="18"
            color="red"
            :trend="['value' => '+5 this week', 'isPositive' => true]"
        >
            <x-slot:icon>
                <x-icon name="check-circle" class="w-6 h-6" />
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card
            title="Completed Jobs"
            value="196"
            color="green"
            :trend="['value' => '+18% completion rate', 'isPositive' => true]"
        >
            <x-slot:icon>
                <x-icon name="check-circle" class="w-6 h-6" />
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card
            title="Monthly Revenue"
            value="₱410K"
            color="charcoal"
            :trend="['value' => '+8% from last month', 'isPositive' => true]"
        >
            <x-slot:icon>
                <x-icon name="dollar-sign" class="w-6 h-6" />
            </x-slot:icon>
        </x-stat-card>
    </div>

    {{-- Charts Row 1 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Monthly Services Chart --}}
        <x-card>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Monthly Services</h2>
            <div x-data="{
                init() {
                    const ctx = document.getElementById('monthlyServicesChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                            datasets: [{
                                label: 'Services Completed',
                                data: [45, 52, 61, 48, 73, 68],
                                backgroundColor: '#E63946',
                                borderRadius: 8,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(107, 114, 128, 0.1)' },
                                    ticks: { color: '#6b7280' }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#6b7280' }
                                }
                            }
                        }
                    });
                }
            }" class="h-[300px] relative">
                <canvas id="monthlyServicesChart"></canvas>
            </div>
        </x-card>

        {{-- Revenue Chart --}}
        <x-card>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Revenue Analytics (₱)</h2>
            <div x-data="{
                init() {
                    const ctx = document.getElementById('revenueChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                            datasets: [{
                                label: 'Revenue (₱)',
                                data: [285000, 320000, 380000, 295000, 450000, 410000],
                                borderColor: '#457B9D',
                                backgroundColor: 'transparent',
                                borderWidth: 3,
                                pointBackgroundColor: '#457B9D',
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return '₱' + context.raw.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(107, 114, 128, 0.1)' },
                                    ticks: { 
                                        color: '#6b7280',
                                        callback: function(value) {
                                            return '₱' + (value / 1000) + 'K';
                                        }
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#6b7280' }
                                }
                            }
                        }
                    });
                }
            }" class="h-[300px] relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </x-card>
    </div>

    {{-- Charts Row 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Service Distribution --}}
        <x-card>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Service Popularity</h2>
            <div x-data="{
                init() {
                    const ctx = document.getElementById('serviceDistributionChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: ['Engine Customization', 'Paint Job', 'Body Kit', 'Turbo Install', 'Exhaust'],
                            datasets: [{
                                data: [30, 25, 20, 15, 10],
                                backgroundColor: ['#E63946', '#457B9D', '#1F2937', '#F59E0B', '#10B981'],
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
                                        boxWidth: 12,
                                        font: { size: 12 }
                                    }
                                }
                            }
                        }
                    });
                }
            }" class="h-[300px] relative">
                <canvas id="serviceDistributionChart"></canvas>
            </div>
        </x-card>

        {{-- Recent Bookings --}}
        <x-card>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Recent Booking Requests</h2>
            <div class="space-y-4">
                @php
                    $recentBookings = [
                        ['id' => 'bk-1', 'customer' => 'Carlos Reyes', 'service' => 'Body Kit Installation', 'date' => 'April 8, 2026', 'status' => 'pending'],
                        ['id' => 'bk-2', 'customer' => 'Ana Garcia', 'service' => 'Exhaust Fabrication', 'date' => 'April 10, 2026', 'status' => 'pending'],
                        ['id' => 'bk-3', 'customer' => 'Juan Dela Cruz', 'service' => 'Engine Customization', 'date' => 'April 5, 2026', 'status' => 'approved'],
                    ];
                @endphp
                @foreach ($recentBookings as $booking)
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-white/10 last:border-0">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $booking['customer'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $booking['service'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $booking['date'] }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{
                            $booking['status'] === 'approved' 
                                ? 'bg-green-500/10 text-green-500 border border-green-500/20' 
                                : 'bg-yellow-500/10 text-yellow-500 border border-yellow-500/20'
                        }}">
                            {{ ucfirst($booking['status']) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    {{-- Quick Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-card class="text-center hover:scale-[1.02] transition-transform duration-300">
            <div class="mx-auto mb-3 text-[#457B9D] flex justify-center">
                <x-icon name="users" class="w-8 h-8" />
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Customers</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">342</p>
        </x-card>

        <x-card class="text-center hover:scale-[1.02] transition-transform duration-300">
            <div class="mx-auto mb-3 text-[#E63946] flex justify-center">
                <x-icon name="wrench" class="w-8 h-8" />
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Active Mechanics</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">8</p>
        </x-card>

        <x-card class="text-center hover:scale-[1.02] transition-transform duration-300">
            <div class="mx-auto mb-3 text-green-500 flex justify-center">
                <x-icon name="calendar" class="w-8 h-8" />
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Today's Appointments</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">6</p>
        </x-card>

        <x-card class="text-center hover:scale-[1.02] transition-transform duration-300">
            <div class="mx-auto mb-3 text-gray-700 dark:text-gray-300 flex justify-center">
                <x-icon name="check-square" class="w-8 h-8" />
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Completion Rate</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">94%</p>
        </x-card>
    </div>
</div>
@endsection
