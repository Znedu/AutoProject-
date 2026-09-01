<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-bind:class="$store.theme.value">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', isset($title) ? $title : config('app.name', 'AutoProject+'))</title>

    <!-- Preloads / Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Inline script to prevent FOUC (Flash of Unstyled Content) -->
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 
                (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(theme);
            if (theme === 'dark') {
                document.body.classList.add('dark');
            }
        })();
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-white dark:bg-[#0B0B0B] text-gray-900 dark:text-white transition-colors duration-200 overflow-x-hidden" x-data="{ sidebarOpen: false }">

    @php
        // Dynamically resolve the user's role: prop > authentication > default
        $currentUser = auth()->user();
        $resolvedRole = $role ?? $currentUser?->roleSlug() ?? 'customer';
        $displayRole = match ($resolvedRole) {
            'admin' => 'Admin',
            'staff' => 'Staff',
            'mechanic' => 'Mechanic',
            'customer' => 'Customer',
            default => ucfirst((string) $resolvedRole),
        };
        $displayName = $currentUser?->name ?? 'Guest';

        $menuItems = [
            'customer' => [
                ['icon' => 'layout-dashboard', 'label' => 'Dashboard', 'path' => '/customer'],
                ['icon' => 'calendar', 'label' => 'Book Service', 'path' => '/customer/book-service'],
                ['icon' => 'clipboard-list', 'label' => 'My Bookings', 'path' => '/customer/bookings'],
                ['icon' => 'box', 'label' => 'Parts & Catalog', 'path' => '/customer/inventory'],
                ['icon' => 'car', 'label' => 'My Vehicles', 'path' => '/customer/vehicles'],
                ['icon' => 'map-pin', 'label' => 'Track Service', 'path' => '/customer/track'],
                ['icon' => 'message-square', 'label' => 'Support Tickets', 'path' => '/customer/support'],
                ['icon' => 'user', 'label' => 'Profile', 'path' => '/customer/profile'],
            ],
            'mechanic' => [
                ['icon' => 'layout-dashboard', 'label' => 'Dashboard', 'path' => '/mechanic'],
                ['icon' => 'wrench', 'label' => 'Assigned Jobs', 'path' => '/mechanic/jobs'],
                ['icon' => 'box', 'label' => 'Shop Inventory', 'path' => '/mechanic/inventory'],
                ['icon' => 'clipboard-list', 'label' => 'Service Notes', 'path' => '/mechanic/notes'],
            ],
            'staff' => [
                ['icon' => 'layout-dashboard', 'label' => 'Dashboard',           'path' => '/staff'],
                ['icon' => 'clipboard-list',   'label' => 'Booking Queue',        'path' => '/staff/booking-queue'],
                ['icon' => 'user-plus',         'label' => 'Walk-In Booking',      'path' => '/staff/walk-in-booking'],
                ['icon' => 'box',               'label' => 'Inventory Control',    'path' => '/staff/inventory'],
                ['icon' => 'calendar',          'label' => 'Schedule',             'path' => '/staff/schedule'],
                ['icon' => 'users',             'label' => 'Customers',            'path' => '/staff/customers'],
                ['icon' => 'wrench',            'label' => 'Job Orders',           'path' => '/staff/jobs'],
                ['icon' => 'message-square',    'label' => 'Customer Assistance',  'path' => '/staff/assistance'],
            ],
            'admin' => [
                ['icon' => 'layout-dashboard', 'label' => 'Dashboard', 'path' => '/admin'],
                ['icon' => 'users', 'label' => 'User Management', 'path' => '/admin/users'],
                ['icon' => 'box', 'label' => 'Inventory System', 'path' => '/admin/inventory'],
                ['icon' => 'check-square', 'label' => 'Booking Approval', 'path' => '/admin/approvals'],
                ['icon' => 'wrench', 'label' => 'Job Assignment', 'path' => '/admin/jobs'],
                ['icon' => 'clipboard-list', 'label' => 'Booking History', 'path' => '/admin/bookings/history'],
                ['icon' => 'settings', 'label' => 'Service Management', 'path' => '/admin/services'],
                ['icon' => 'bar-chart-3', 'label' => 'Reports', 'path' => '/admin/reports'],
            ],
        ];

        $items = $menuItems[$resolvedRole] ?? [];
    @endphp

    <!-- Mobile Navigation Overlay -->
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="lg:hidden fixed inset-0 bg-black/70 z-40 backdrop-blur-sm"
    ></div>

    <!-- Sidebar Frame -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed top-0 left-0 h-full w-72 max-w-[85vw] lg:w-64 bg-white dark:bg-[#121212] border-r border-gray-200 dark:border-white/10 text-gray-900 dark:text-white z-50 transform transition-transform duration-300 ease-in-out shadow-2xl lg:translate-x-0 overflow-y-auto"
    >
        <div class="p-5 sm:p-6 min-h-full flex flex-col justify-between">
            <div>
                <!-- Brand Logo & Mobile Close Header -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-white/10 lg:border-none lg:pb-0">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white tracking-wider">
                            AUTO<span class="text-[#E63946]">PROJECT</span>+
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-white/60 mt-0.5 uppercase tracking-wider">
                            {{ $resolvedRole }} Portal
                        </p>
                    </div>
                    <button
                        class="lg:hidden p-2.5 rounded-xl text-gray-500 hover:text-gray-900 dark:text-white/70 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10 touch-target flex items-center justify-center cursor-pointer"
                        @click="sidebarOpen = false"
                        aria-label="Close sidebar"
                    >
                        <x-icon name="x" class="w-6 h-6" />
                    </button>
                </div>

                @if ($currentUser)
                    <div class="mb-6 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-3.5 sm:p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E63946] text-sm font-semibold text-white">
                                {{ $currentUser->initials }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $displayName }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-white/60">{{ $displayRole }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Role-Specific Navigation Menu -->
                <nav class="space-y-1">
                    @foreach ($items as $item)
                        @php
                            $path = trim($item['path'], '/');
                            if (in_array($path, ['customer', 'staff', 'mechanic', 'admin'])) {
                                $isActive = request()->is($path);
                            } else {
                                $isActive = request()->is($path) || request()->is($path . '/*');
                            }
                        @endphp
                        <a
                            href="{{ url($item['path']) }}"
                            @click="sidebarOpen = false"
                            class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 group touch-target {{ $isActive ? 'bg-[#E63946] text-white shadow-lg shadow-[#E63946]/20' : 'text-gray-600 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 hover:text-gray-900 dark:hover:text-white' }}"
                        >
                            <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 shrink-0 {{ $isActive ? '' : 'group-hover:scale-110 transition-transform duration-300' }}" />
                            <span class="font-medium text-sm truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="pt-6 mt-6 border-t border-gray-200 dark:border-white/10 space-y-2">
                <!-- Light/Dark Mode Theme Switcher -->
                <button
                    @click="$store.theme.toggle()"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 w-full text-gray-600 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-white/10 touch-target cursor-pointer"
                >
                    <span x-show="$store.theme.isDark" class="flex items-center gap-3.5" x-cloak>
                        <x-icon name="sun" class="w-5 h-5 shrink-0" />
                        <span class="font-medium text-sm">Light Mode</span>
                    </span>
                    <span x-show="!$store.theme.isDark" class="flex items-center gap-3.5">
                        <x-icon name="moon" class="w-5 h-5 shrink-0" />
                        <span class="font-medium text-sm">Dark Mode</span>
                    </span>
                </button>

                <!-- Authentication Logout Controls -->
                <form id="logout-form" action="{{ Route::has('logout') ? route('logout') : url('/login') }}" method="{{ Route::has('logout') ? 'POST' : 'GET' }}" class="hidden">
                    @csrf
                </form>
                <button
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 w-full text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 border border-red-200 dark:border-red-500/20 touch-target cursor-pointer"
                >
                    <x-icon name="log-out" class="w-5 h-5 shrink-0" />
                    <span class="font-medium text-sm">Logout</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main View Content Area -->
    <div class="lg:ml-64 min-h-screen flex flex-col overflow-x-hidden">
        <!-- Sticky Top Header Bar -->
        <header class="sticky top-0 z-30 flex items-center justify-between lg:justify-end h-16 px-4 sm:px-6 bg-white/90 dark:bg-[#0B0B0B]/90 backdrop-blur-md border-b border-gray-200 dark:border-white/10">
            <!-- Mobile Menu Hamburger Button -->
            <button
                class="lg:hidden p-2.5 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 touch-target flex items-center justify-center cursor-pointer"
                @click="sidebarOpen = true"
                aria-label="Open menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Brand Logo visible on Mobile header center -->
            <div class="lg:hidden">
                <span class="text-lg font-bold text-gray-900 dark:text-white tracking-wider">
                    AUTO<span class="text-[#E63946]">PROJECT</span>+
                </span>
            </div>

            <div class="flex items-center gap-3">
                <x-notification-bell />
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-full overflow-x-hidden">
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Confirmation Modal Dialog -->
    <x-confirm-dialog />

    @stack('scripts')
</body>
</html>
