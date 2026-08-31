<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Terms of Service & Privacy Policy | AutoProject+</title>
    <meta name="description" content="Terms of Service and Privacy Policy for AutoProject-D Custom Garage.">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0B0B0B] text-white p-4 md:p-8">

    <div class="max-w-4xl mx-auto space-y-8">

        <!-- Back to Register Header -->
        <div class="flex items-center justify-between pb-6 border-b border-white/10">
            <div>
                <h1 class="text-3xl font-bold tracking-wider">
                    AUTO<span class="text-[#E63946]">PROJECT</span>+
                </h1>
                <p class="text-sm text-[#B8B8B8] mt-1">AutoProject-D Custom Garage</p>
            </div>
            <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-sm font-semibold transition-all inline-flex items-center gap-2">
                ← Back to Registration
            </a>
        </div>

        <div class="glass-card p-6 md:p-10 rounded-2xl space-y-8 border border-white/10">
            
            <!-- Document Header -->
            <div class="text-center pb-6 border-b border-white/10">
                <h2 class="text-3xl font-bold text-white mb-2">Terms of Service & Privacy Policy</h2>
                <p class="text-sm text-[#B8B8B8]">Effective Date: August 30, 2026</p>
            </div>

            <!-- Terms of Service -->
            <section class="space-y-4">
                <h3 class="text-xl font-bold text-[#E63946] flex items-center gap-2">
                    <x-icon name="file-text" class="w-5 h-5" />
                    1. Terms of Service
                </h3>
                
                <div class="space-y-3 text-sm text-[#B8B8B8] leading-relaxed">
                    <h4 class="font-semibold text-white">1.1 Account Registration & Security</h4>
                    <p>
                        By creating an account on AutoProject+, you agree to provide accurate and complete information. You are solely responsible for maintaining the confidentiality of your account password and for all activities conducted under your account.
                    </p>

                    <h4 class="font-semibold text-white">1.2 Service Bookings & Appointments</h4>
                    <p>
                        Service appointments scheduled through AutoProject+ are Subject to confirmation by shop administrators. AutoProject-D Custom Garage reserves the right to adjust or reschedule appointments based on shop capacity and parts availability.
                    </p>

                    <h4 class="font-semibold text-white">1.3 Cost Estimations & Final Invoicing</h4>
                    <p>
                        Automated cost estimations generated on the platform are preliminary figures based on selected service packages and standard labor rates. Final billing will reflect verified shop inspection, actual parts utilized, and approved custom modifications.
                    </p>

                    <h4 class="font-semibold text-white">1.4 Vehicle Care & Liability</h4>
                    <p>
                        Vehicles serviced at AutoProject-D Custom Garage are handled with utmost professional care in secured workshop facilities. Customers are advised to remove personal valuables prior to surrendering vehicles for service.
                    </p>
                </div>
            </section>

            <!-- Privacy Policy -->
            <section class="space-y-4 pt-6 border-t border-white/10">
                <h3 class="text-xl font-bold text-[#E63946] flex items-center gap-2">
                    <x-icon name="shield" class="w-5 h-5" />
                    2. Privacy Policy
                </h3>

                <div class="space-y-3 text-sm text-[#B8B8B8] leading-relaxed">
                    <h4 class="font-semibold text-white">2.1 Information We Collect</h4>
                    <p>
                        We collect personal identification details (Name, Email Address, Contact Number) and vehicle information (Make, Model, Year, Plate Number) required to process service bookings, track service progress, and generate official invoices.
                    </p>

                    <h4 class="font-semibold text-white">2.2 Data Protection & Encryption</h4>
                    <p>
                        All user data is encrypted and securely stored. AutoProject+ strictly adheres to data protection practices. We never sell, rent, or trade your personal information to third-party advertisers.
                    </p>

                    <h4 class="font-semibold text-white">2.3 Automated System Notifications</h4>
                    <p>
                        By registering an account, you consent to receive essential service updates, booking confirmations, payment receipts, and customer support notifications via email and in-app notifications.
                    </p>
                </div>
            </section>

            <!-- Footer Action -->
            <div class="pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-gray-400">
                    If you have questions regarding these terms, please contact <span class="text-white">support@autoproject.com</span>.
                </p>
                <a href="{{ route('register') }}" class="px-6 py-2.5 rounded-xl bg-[#E63946] hover:bg-[#E63946]/90 text-white font-semibold text-sm transition-all shadow-lg shadow-[#E63946]/20">
                    Return to Registration
                </a>
            </div>

        </div>

    </div>

</body>
</html>
