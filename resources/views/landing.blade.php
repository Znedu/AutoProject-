<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>AutoProject+ | Smart Automotive Service Management</title>
    <meta name="description" content="Modern automotive service management platform for AutoProject-D Custom Garage. Book services, track progress, and manage your vehicle online.">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-[#0B0B0B]">

    {{-- Navigation --}}
    <nav class="fixed w-full top-0 z-50 glass-card border-b border-white/10" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-white tracking-wider">
                        AUTO<span class="text-[#E63946]">PROJECT</span>+
                    </h1>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#home" class="text-[#B8B8B8] hover:text-white transition-colors duration-300">Home</a>
                    <a href="#services" class="text-[#B8B8B8] hover:text-white transition-colors duration-300">Services</a>
                    <a href="#features" class="text-[#B8B8B8] hover:text-white transition-colors duration-300">Features</a>
                    <a href="#about" class="text-[#B8B8B8] hover:text-white transition-colors duration-300">About</a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/login') }}">
                        <button class="px-6 py-2.5 text-white border border-white/20 rounded-lg hover:border-[#E63946] hover:text-[#E63946] transition-all duration-300 cursor-pointer">
                            Login
                        </button>
                    </a>
                    <a href="{{ url('/register') }}">
                        <button class="px-6 py-2.5 bg-gradient-red text-white rounded-lg hover:shadow-lg hover:shadow-[#E63946]/50 transition-all duration-300 glow-red-hover cursor-pointer">
                            Get Started
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section
        id="home"
        class="relative min-h-screen flex items-center justify-center overflow-hidden pt-20"
        style="background-image: url('https://images.unsplash.com/photo-1763087978864-fe5b2778c9f7?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920'); background-size: cover; background-position: center; background-attachment: fixed;"
    >
        {{-- Dark Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/70 to-black/90"></div>

        {{-- Content --}}
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold text-white mb-6 tracking-tight">
                    Smart Automotive
                    <br />
                    <span class="text-glow text-[#E63946]">Service Management</span>
                </h1>
                <p class="text-xl sm:text-2xl text-[#B8B8B8] mb-12 max-w-3xl mx-auto leading-relaxed">
                    Manage bookings, customization services, and vehicle maintenance efficiently with
                    <span class="text-white font-semibold"> AutoProject+</span>
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a href="{{ url('/register') }}">
                        <button class="group px-10 py-4 bg-gradient-red text-white rounded-xl text-lg font-semibold hover:shadow-2xl hover:shadow-[#E63946]/50 transition-all duration-300 glow-red flex items-center justify-center gap-3 cursor-pointer">
                            Book a Service
                            <x-icon name="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                        </button>
                    </a>
                    <a href="#services">
                        <button class="px-10 py-4 bg-white/10 backdrop-blur-sm text-white rounded-xl text-lg font-semibold border border-white/20 hover:bg-white/20 hover:border-[#E63946] transition-all duration-300 cursor-pointer">
                            Explore Services
                        </button>
                    </a>
                </div>
            </div>

            {{-- Scroll Indicator --}}
            <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
                <x-icon name="chevron-right" class="w-8 h-8 text-white/50 rotate-90" />
            </div>
        </div>
    </section>

    {{-- Feature Highlights Section --}}
    <section id="features" class="py-24 bg-gradient-to-b from-[#0B0B0B] to-[#151515]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                    Platform Features
                </h2>
                <p class="text-lg text-[#B8B8B8] max-w-2xl mx-auto">
                    Modern features designed for seamless automotive service management
                </p>
            </div>

            @php
                $features = [
                    ['icon' => 'calendar', 'title' => 'Online Booking System', 'description' => 'Book your service appointments online 24/7 with real-time slot availability'],
                    ['icon' => 'dollar-sign', 'title' => 'Automated Cost Estimation', 'description' => 'Get instant cost estimates with brand-specific pricing for transparency'],
                    ['icon' => 'map-pin', 'title' => 'Service Tracking Dashboard', 'description' => 'Track your vehicle service progress in real-time with live updates'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($features as $feature)
                    <div class="glass-card glass-hover p-8 rounded-2xl group cursor-pointer">
                        <div class="mb-6 text-[#E63946] group-hover:scale-110 transition-transform duration-300">
                            <x-icon name="{{ $feature['icon'] }}" class="w-10 h-10" />
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-4">{{ $feature['title'] }}</h3>
                        <p class="text-[#B8B8B8] leading-relaxed">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Service Categories Section --}}
    <section id="services" class="py-24 bg-[#151515]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                    Our Garage Services
                </h2>
                <p class="text-lg text-[#B8B8B8] max-w-2xl mx-auto">
                    Comprehensive automotive customization and maintenance services
                </p>
            </div>

            @php
                $services = [
                    ['title' => 'Customization Services', 'description' => 'Body kits, paint jobs, wraps, and performance modifications', 'image' => 'https://images.unsplash.com/photo-1570762574066-a238075b62f9?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080', 'icon' => 'wrench'],
                    ['title' => 'Maintenance Services', 'description' => 'Oil changes, brake service, engine tune-ups, and routine maintenance', 'image' => 'https://images.unsplash.com/photo-1759189196663-209ff7cda669?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080', 'icon' => 'settings'],
                    ['title' => 'Diagnostics & Inspection', 'description' => 'Computer diagnostics, pre-purchase inspection, and system checks', 'image' => 'https://images.unsplash.com/photo-1664530550244-d616a32ed041?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080', 'icon' => 'gauge'],
                    ['title' => 'Performance Upgrades', 'description' => 'Turbo installation, exhaust systems, ECU tuning, and suspension', 'image' => 'https://images.unsplash.com/photo-1768387666438-b3da75373846?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080', 'icon' => 'gauge'],
                    ['title' => 'Interior Customization', 'description' => 'Upholstery, sound systems, ambient lighting, and trim upgrades', 'image' => 'https://images.unsplash.com/photo-1664530550244-d616a32ed041?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080', 'icon' => 'sparkles'],
                    ['title' => 'Detailing Services', 'description' => 'Ceramic coating, paint protection film, and professional detailing', 'image' => 'https://images.unsplash.com/photo-1763087978864-fe5b2778c9f7?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080', 'icon' => 'sparkles'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($services as $service)
                    <div class="group relative overflow-hidden rounded-2xl glass-card glass-hover cursor-pointer h-80">
                        {{-- Background Image --}}
                        <div
                            class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110"
                            style="background-image: url('{{ $service['image'] }}')"
                        ></div>

                        {{-- Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-transparent"></div>

                        {{-- Content --}}
                        <div class="relative h-full flex flex-col justify-end p-6">
                            <div class="mb-4 text-[#E63946] group-hover:scale-110 transition-transform duration-300">
                                <x-icon name="{{ $service['icon'] }}" class="w-8 h-8" />
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-3">{{ $service['title'] }}</h3>
                            <p class="text-[#B8B8B8] mb-4">{{ $service['description'] }}</p>
                            <button class="flex items-center gap-2 text-[#E63946] font-semibold group-hover:gap-4 transition-all duration-300 cursor-pointer">
                                Learn More
                                <x-icon name="chevron-right" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- About Section --}}
    <section id="about" class="py-24 bg-gradient-to-b from-[#151515] to-[#0B0B0B]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                        About AutoProject-D Custom Garage
                    </h2>
                    <p class="text-lg text-[#B8B8B8] mb-6 leading-relaxed">
                        We are a premium automotive customization and service center specializing in
                        performance upgrades, aesthetic modifications, and professional maintenance services.
                    </p>
                    <p class="text-lg text-[#B8B8B8] mb-8 leading-relaxed">
                        With AutoProject+, we bring transparency, efficiency, and modern technology to
                        automotive service management. Track your bookings, monitor service progress, and
                        communicate with our team - all from one platform.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="glass-card p-6 rounded-xl">
                            <div class="text-3xl font-bold text-[#E63946] mb-2">500+</div>
                            <div class="text-[#B8B8B8]">Completed Projects</div>
                        </div>
                        <div class="glass-card p-6 rounded-xl">
                            <div class="text-3xl font-bold text-[#E63946] mb-2">98%</div>
                            <div class="text-[#B8B8B8]">Customer Satisfaction</div>
                        </div>
                    </div>
                </div>
                <div
                    class="relative h-96 lg:h-full rounded-2xl overflow-hidden glass-card"
                    style="background-image: url('https://images.unsplash.com/photo-1759189196663-209ff7cda669?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080'); background-size: cover; background-position: center;"
                >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-24 bg-[#151515] relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-1/2 left-1/4 w-96 h-96 bg-[#E63946] rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-[#E63946] rounded-full blur-3xl"></div>
        </div>
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to Transform Your Vehicle?
            </h2>
            <p class="text-xl text-[#B8B8B8] mb-10">
                Join AutoProject+ today and experience the future of automotive service management
            </p>
            <a href="{{ url('/register') }}">
                <button class="px-12 py-5 bg-gradient-red text-white rounded-xl text-lg font-semibold hover:shadow-2xl hover:shadow-[#E63946]/50 transition-all duration-300 glow-red cursor-pointer">
                    Create Your Account
                </button>
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-[#0B0B0B] border-t border-white/10 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-xl font-bold text-white mb-4">
                        AUTO<span class="text-[#E63946]">PROJECT</span>+
                    </h3>
                    <p class="text-[#B8B8B8] text-sm">
                        Modern automotive service management platform for AutoProject-D Custom Garage
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Services</h4>
                    <ul class="space-y-2 text-[#B8B8B8] text-sm">
                        <li>Customization</li>
                        <li>Maintenance</li>
                        <li>Diagnostics</li>
                        <li>Performance</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Platform</h4>
                    <ul class="space-y-2 text-[#B8B8B8] text-sm">
                        <li>Booking</li>
                        <li>Tracking</li>
                        <li>Support</li>
                        <li>Reports</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-[#B8B8B8] text-sm">
                        <li>support@autoproject.com</li>
                        <li>+63 912 345 6789</li>
                        <li>Manila, Philippines</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 pt-8 text-center text-[#B8B8B8] text-sm">
                <p>&copy; {{ date('Y') }} AutoProject+. All rights reserved. AutoProject-D Custom Garage.</p>
            </div>
        </div>
    </footer>

    <!-- Toast Notifications -->
    <x-toast />

</body>
</html>
