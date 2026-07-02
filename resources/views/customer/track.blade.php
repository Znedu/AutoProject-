@extends('layouts.dashboard')

@section('title', 'Track Service | AutoProject+')

@section('content')
{{-- Inject PHP data as global JS variables (most reliable Alpine pattern) --}}
<script>
    window._trackingData    = @json($trackingData);
    window._bookingSelector = @json($bookingSelector);
</script>

<div
    x-data="trackService()"
    class="max-w-4xl mx-auto space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Track Service</h1>
                <p class="text-gray-600 dark:text-gray-400">Real-time tracking of your vehicle service.</p>
            </div>
            {{-- Manual Refresh Button --}}
            <button
                id="track-refresh-btn"
                @click="fetchLatest()"
                :disabled="refreshing"
                class="flex-shrink-0 flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#151515] text-gray-700 dark:text-gray-300 hover:border-[#E63946] hover:text-[#E63946] transition-all text-sm font-medium disabled:opacity-60 disabled:cursor-not-allowed mt-1"
            >
                <svg :class="refreshing ? 'animate-spin' : ''" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span x-text="refreshing ? 'Refreshing…' : 'Refresh'"></span>
            </button>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-600 mt-1" x-show="lastUpdated" x-text="'Last updated: ' + lastUpdated"></p>

        {{-- Photo Updates Notice --}}
        <div class="mt-3 p-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-xl">
            <div class="flex items-start gap-2">
                <x-icon name="camera" class="text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0 w-5 h-5" />
                <p class="text-sm text-green-800 dark:text-green-200">
                    <strong>Photo Updates Enabled:</strong> Our mechanics will send you photos showing the progress of your vehicle's service. Click on any photo to view it in full size.
                </p>
            </div>
        </div>
    </div>

    {{-- Booking Selector (shown when customer has multiple bookings) --}}
    <template x-if="bookingSelector.length > 1">
        <x-card>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                    Tracking Booking:
                </label>
                <select
                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#151515] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#E63946] transition-all text-sm"
                    @change="switchBooking($event.target.value)"
                >
                    <template x-for="b in bookingSelector" :key="b.id">
                        <option
                            :value="b.id"
                            :selected="b.id === trackingData.selected_booking_id"
                            x-text="b.booking_number + ' — ' + b.service + ' (' + b.vehicle + ')'"
                        ></option>
                    </template>
                </select>
            </div>
        </x-card>
    </template>

    {{-- Empty State: No Bookings --}}
    <template x-if="!trackingData.bookingId">
        <x-card>
            <div class="text-center py-16 space-y-4">
                <div class="flex justify-center">
                    <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center">
                        <x-icon name="wrench" class="w-10 h-10 text-gray-400 dark:text-gray-600" />
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Active Service</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm max-w-sm mx-auto">
                        You don't have any active service bookings to track. Book a service to get started.
                    </p>
                </div>
                <a href="{{ route('customer.book-service') }}">
                    <x-button variant="accent" class="text-white mt-2">
                        Book a Service
                    </x-button>
                </a>
            </div>
        </x-card>
    </template>

    {{-- Content (shown only when a booking exists) --}}
    <template x-if="trackingData.bookingId">
        <div class="space-y-6">
            {{-- Service Info --}}
            <x-card>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center md:text-left">
                    <div>
                        <p class="text-sm mb-1 text-gray-600 dark:text-gray-400">Service Type</p>
                        <p class="font-bold text-gray-900 dark:text-white text-lg" x-text="trackingData.service"></p>
                    </div>
                    <div>
                        <p class="text-sm mb-1 text-gray-600 dark:text-gray-400">Vehicle</p>
                        <p class="font-bold text-gray-900 dark:text-white text-lg" x-text="trackingData.vehicle"></p>
                    </div>
                    <div>
                        <p class="text-sm mb-1 text-gray-600 dark:text-gray-400">Booking ID</p>
                        <p class="font-bold text-gray-900 dark:text-white text-lg" x-text="trackingData.bookingId"></p>
                    </div>
                </div>

                {{-- Overall Progress Bar (shown when job has started) --}}
                <template x-if="trackingData.progress > 0">
                    <div class="mt-5 pt-5 border-t border-gray-200 dark:border-white/10">
                        <div class="flex justify-between items-center text-sm mb-2">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Overall Progress</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="trackingData.progress + '%'"></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-zinc-800 rounded-full h-3 overflow-hidden">
                            <div
                                class="h-3 rounded-full bg-[#E63946] transition-all duration-700"
                                :class="trackingData.progress >= 100 ? 'bg-green-500' : 'bg-[#E63946]'"
                                :style="'width: ' + trackingData.progress + '%'"
                            ></div>
                        </div>
                    </div>
                </template>
            </x-card>

            {{-- Service Progress Timeline --}}
            <x-card>
                <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white">Service Progress</h2>
                <div class="relative pl-8 sm:pl-12">
                    {{-- Progress Line --}}
                    <div class="absolute left-6 sm:left-10 top-0 bottom-0 w-1 bg-gray-300 dark:bg-gray-700">
                        <div
                            class="bg-[#E63946] w-full transition-all duration-500"
                            :style="'height: ' + (trackingData.stages.length > 1 ? (trackingData.currentStage / (trackingData.stages.length - 1)) * 100 : 0) + '%'"
                        ></div>
                    </div>

                    {{-- Stages --}}
                    <div class="space-y-8 relative">
                        <template x-for="(stage, index) in trackingData.stages" :key="index">
                            <div class="flex items-start gap-4 relative">
                                {{-- Stage Icon --}}
                                <div
                                    class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center z-10 font-bold transition-colors"
                                    :class="stage.completed
                                        ? 'bg-[#E63946] text-white'
                                        : (index === trackingData.currentStage ? 'bg-[#457B9D] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500')"
                                >
                                    <template x-if="stage.completed">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </template>
                                    <template x-if="!stage.completed && index === trackingData.currentStage">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                                        </svg>
                                    </template>
                                    <template x-if="!stage.completed && index !== trackingData.currentStage">
                                        <span class="text-lg" x-text="index + 1"></span>
                                    </template>
                                </div>

                                {{-- Stage Info --}}
                                <div class="flex-1 pt-2">
                                    <h3
                                        class="font-bold mb-1"
                                        :class="stage.completed || index === trackingData.currentStage
                                            ? 'text-gray-900 dark:text-white'
                                            : 'text-gray-400 dark:text-gray-500'"
                                        x-text="stage.name"
                                    ></h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400" x-text="stage.date"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </x-card>

            {{-- Service Updates Feed --}}
            <x-card>
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Service Updates</h2>
                    <x-icon name="camera" class="text-[#E63946] w-5 h-5" />
                </div>

                {{-- Empty state --}}
                <template x-if="trackingData.notes.length === 0">
                    <div class="text-center py-10">
                        <div class="flex justify-center mb-3">
                            <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center">
                                <x-icon name="clipboard-list" class="w-7 h-7 text-gray-400 dark:text-gray-600" />
                            </div>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No updates yet. Your mechanic will post progress notes here.</p>
                    </div>
                </template>

                {{-- Updates List --}}
                <div class="space-y-6">
                    <template x-for="(note, index) in trackingData.notes" :key="index">
                        <div class="border-l-4 border-[#E63946] pl-4 py-3 bg-gray-50 dark:bg-[#0B0B0B]/40 rounded-xl">
                            <div class="flex items-start justify-between gap-4 mb-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                        <span x-text="note.date"></span> • <span x-text="note.time"></span>
                                    </p>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-1" x-text="note.author"></p>
                                </div>
                                <template x-if="note.photos && note.photos.length > 0">
                                    <div class="flex items-center gap-1 text-sm text-[#E63946] bg-red-50 dark:bg-red-950/30 px-2 py-1 rounded-lg flex-shrink-0">
                                        <x-icon name="camera" class="w-4 h-4 text-[#E63946]" />
                                        <span><span x-text="note.photos.length"></span> photo<span x-text="note.photos.length > 1 ? 's' : ''"></span></span>
                                    </div>
                                </template>
                            </div>
                            <p class="mb-3 text-gray-900 dark:text-gray-200" x-text="note.message"></p>

                            {{-- Photo Gallery --}}
                            <template x-if="note.photos && note.photos.length > 0">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-3">
                                    <template x-for="(photo, photoIndex) in note.photos" :key="photoIndex">
                                        <div
                                            class="relative group cursor-pointer"
                                            @click="openLightbox(photo.url)"
                                        >
                                            <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-xl overflow-hidden">
                                                <img
                                                    :src="photo.url"
                                                    :alt="photo.caption"
                                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                                />
                                            </div>
                                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors rounded-xl flex items-center justify-center">
                                                <svg class="text-white opacity-0 group-hover:opacity-100 transition-opacity w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                </svg>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-1" x-text="photo.caption"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </x-card>

            {{-- Estimated Completion --}}
            <x-card class="bg-gradient-to-r from-[#457B9D] to-[#5A8FB0] text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-white/80 mb-1">Estimated Completion</p>
                        <template x-if="trackingData.estimated_completion">
                            <p class="text-2xl font-extrabold" x-text="trackingData.estimated_completion"></p>
                        </template>
                        <template x-if="!trackingData.estimated_completion">
                            <p class="text-lg font-semibold text-white/70">To be determined</p>
                        </template>
                    </div>
                    <x-icon name="calendar" class="w-12 h-12 text-white/40" />
                </div>
            </x-card>
        </div>
    </template>

    {{-- Photo Lightbox --}}
    <div
        x-show="lightboxPhoto"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/95 flex items-center justify-center z-50 p-4"
        style="display:none;"
        @click="closeLightbox()"
        @keydown.escape.window="closeLightbox()"
    >
        <button
            class="absolute top-4 right-4 text-white hover:text-gray-300 cursor-pointer transition-colors"
            @click="closeLightbox()"
        >
            <x-icon name="x" class="w-8 h-8" />
        </button>
        <img
            :src="lightboxPhoto"
            alt="Full size preview"
            class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
            @click.stop
        />
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Register with window so Alpine can access it regardless of module scope
    window.trackService = function() {
        return {
            trackingData: window._trackingData || {},
            bookingSelector: window._bookingSelector || [],
            lightboxPhoto: null,
            refreshing: false,
            lastUpdated: null,
            _pollTimer: null,

            init() {
                console.log('TrackService init - bookingId:', this.trackingData.bookingId, '| selector:', this.bookingSelector.length);
                // Start auto-polling every 30 seconds
                this._startPolling();
                // Refresh immediately when the tab becomes visible again
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) this.fetchLatest();
                });
            },

            _startPolling() {
                this._pollTimer = setInterval(() => {
                    if (!document.hidden) this.fetchLatest();
                }, 30000);
            },

            fetchLatest() {
                if (this.refreshing) return;
                this.refreshing = true;

                const bookingId = this.trackingData.selected_booking_id || '';
                const url = '{{ route('customer.track.refresh') }}' + (bookingId ? '?booking_id=' + bookingId : '');

                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then(res => res.json())
                .then(data => {
                    this.trackingData = data;
                    const now = new Date();
                    this.lastUpdated = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                })
                .catch(err => console.warn('Track refresh failed:', err))
                .finally(() => { this.refreshing = false; });
            },

            switchBooking(bookingId) {
                if (bookingId) {
                    window.location.href = '{{ route('customer.track') }}?booking_id=' + bookingId;
                }
            },

            openLightbox(url) {
                this.lightboxPhoto = url;
                document.body.classList.add('overflow-hidden');
            },

            closeLightbox() {
                this.lightboxPhoto = null;
                document.body.classList.remove('overflow-hidden');
            },
        };
    };

    // Also register via Alpine.data() if Alpine is already loaded
    document.addEventListener('alpine:init', () => {
        Alpine.data('trackService', window.trackService);
    });
</script>
@endpush
