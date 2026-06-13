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
<body class="min-h-screen bg-white dark:bg-[#0B0B0B] text-gray-900 dark:text-white transition-colors duration-200" x-data="{ sidebarOpen: false }">

    @php
        // Dynamically resolve the user's role: prop > authentication > default
        $resolvedRole = $role ?? auth()->user()?->roleSlug() ?? 'customer';

        $menuItems = [
            'customer' => [
                ['icon' => 'layout-dashboard', 'label' => 'Dashboard', 'path' => '/customer'],
                ['icon' => 'calendar', 'label' => 'Book Service', 'path' => '/customer/book-service'],
                ['icon' => 'clipboard-list', 'label' => 'My Bookings', 'path' => '/customer/bookings'],
                ['icon' => 'map-pin', 'label' => 'Track Service', 'path' => '/customer/track'],
                ['icon' => 'message-square', 'label' => 'Support Tickets', 'path' => '/customer/support'],
                ['icon' => 'user', 'label' => 'Profile', 'path' => '/customer/profile'],
            ],
            'mechanic' => [
                ['icon' => 'layout-dashboard', 'label' => 'Dashboard', 'path' => '/mechanic'],
                ['icon' => 'wrench', 'label' => 'Assigned Jobs', 'path' => '/mechanic/jobs'],
                ['icon' => 'clipboard-list', 'label' => 'Service Notes', 'path' => '/mechanic/notes'],
            ],
            'staff' => [
                ['icon' => 'layout-dashboard', 'label' => 'Dashboard', 'path' => '/staff'],
                ['icon' => 'clipboard-list', 'label' => 'Booking Queue', 'path' => '/staff/booking-queue'],
                ['icon' => 'message-square', 'label' => 'Customer Assistance', 'path' => '/staff/assistance'],
            ],
            'admin' => [
                ['icon' => 'layout-dashboard', 'label' => 'Dashboard', 'path' => '/admin'],
                ['icon' => 'users', 'label' => 'User Management', 'path' => '/admin/users'],
                ['icon' => 'check-square', 'label' => 'Booking Approval', 'path' => '/admin/approvals'],
                ['icon' => 'clipboard-list', 'label' => 'Booking History', 'path' => '/admin/bookings/history'],
                ['icon' => 'settings', 'label' => 'Service Management', 'path' => '/admin/services'],
                ['icon' => 'bar-chart-3', 'label' => 'Reports', 'path' => '/admin/reports'],
            ],
        ];

        $items = $menuItems[$resolvedRole] ?? [];
    @endphp

    <!-- Mobile Menu Toggle Button -->
    <button
        class="lg:hidden fixed top-4 left-4 z-50 p-3 rounded-xl bg-white dark:glass-card text-gray-900 dark:text-white shadow-lg border border-gray-300 dark:border-white/10 cursor-pointer transition-transform active:scale-95"
        @click="sidebarOpen = !sidebarOpen"
        aria-label="Toggle Menu"
    >
        <!-- Menu Hamburger (shown when sidebar closed) -->
        <svg x-show="!sidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <!-- X Icon (shown when sidebar open) -->
        <svg x-show="sidebarOpen" class="w-6 h-6" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

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
        class="lg:hidden fixed inset-0 bg-black bg-opacity-70 z-30 backdrop-blur-sm"
    ></div>

    <!-- Sidebar Frame -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed top-0 left-0 h-full w-64 bg-white dark:glass-card border-r border-gray-300 dark:border-white/10 text-gray-900 dark:text-white z-40 transform transition-transform duration-300 ease-in-out shadow-2xl lg:translate-x-0"
    >
        <div class="p-6 h-full flex flex-col">
            <!-- Brand Logo -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-wider">
                    AUTO<span class="text-[#E63946]">PROJECT</span>+
                </h1>
                <p class="text-xs text-gray-600 dark:text-white/60 mt-1 uppercase tracking-wider">
                    {{ $resolvedRole }} Portal
                </p>
            </div>

            <!-- Role-Specific Navigation Menu -->
            <nav class="space-y-1 flex-1">
                @foreach ($items as $item)
                    @php
                        $path = trim($item['path'], '/');
                        // Highlight dashboard strictly if matching base segment, otherwise check sub-routes
                        if (in_array($path, ['customer', 'staff', 'mechanic', 'admin'])) {
                            $isActive = request()->is($path);
                        } else {
                            $isActive = request()->is($path) || request()->is($path . '/*');
                        }
                    @endphp
                    <a
                        href="{{ url($item['path']) }}"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-300 group {{ $isActive ? 'bg-[#E63946] text-white shadow-lg shadow-[#E63946]/20' : 'text-gray-600 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 hover:text-gray-900 dark:hover:text-white' }}"
                    >
                        <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 {{ $isActive ? '' : 'group-hover:scale-110 transition-transform duration-300' }}" />
                        <span class="font-medium">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <!-- Light/Dark Mode Theme Switcher -->
            <button
                @click="$store.theme.toggle()"
                class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-300 w-full text-gray-600 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-white/10 hover:border-gray-400 dark:hover:border-white/20 mb-2 cursor-pointer"
            >
                <!-- Sun Icon: Visible in Dark Mode -->
                <span x-show="$store.theme.isDark" class="flex items-center gap-4" x-cloak>
                    <x-icon name="sun" class="w-5 h-5" />
                    <span class="font-medium">Light Mode</span>
                </span>
                <!-- Moon Icon: Visible in Light Mode -->
                <span x-show="!$store.theme.isDark" class="flex items-center gap-4">
                    <x-icon name="moon" class="w-5 h-5" />
                    <span class="font-medium">Dark Mode</span>
                </span>
            </button>

            <!-- Authentication Logout Controls -->
            <form id="logout-form" action="{{ Route::has('logout') ? route('logout') : url('/login') }}" method="{{ Route::has('logout') ? 'POST' : 'GET' }}" class="hidden">
                @csrf
            </form>
            <button
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-300 w-full text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-300 border border-red-300 dark:border-red-500/20 hover:border-red-400 dark:hover:border-red-500/40 cursor-pointer"
            >
                <x-icon name="log-out" class="w-5 h-5" />
                <span class="font-medium">Logout</span>
            </button>
        </div>
    </aside>

    <!-- Main View Content Area -->
    <div class="lg:ml-64">
        <main class="p-6 lg:p-8">
            @if (isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

</body>
</html>
