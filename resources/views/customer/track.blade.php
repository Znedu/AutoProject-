@extends('layouts.dashboard')

@section('title', 'Track Service | AutoProject+')

@section('content')
<div 
    x-data="{
        selectedPhoto: null,
        trackingData: @js($trackingData)
    }"
    class="max-w-4xl mx-auto space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Track Service</h1>
        <p class="text-gray-600 dark:text-gray-400">Real-time tracking of your vehicle service.</p>
        <div class="mt-3 p-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-xl">
            <div class="flex items-start gap-2">
                <x-icon name="info" class="text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0 w-5 h-5" />
                <p class="text-sm text-green-800 dark:text-green-200">
                    <strong>Photo Updates Enabled:</strong> Our mechanics will send you photos showing the progress of your vehicle's service. Click on any photo to view it in full size.
                </p>
            </div>
        </div>
    </div>

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
    </x-card>

    {{-- Progress Bar --}}
    <x-card>
        <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white">Service Progress</h2>
        <div class="relative pl-8 sm:pl-12">
            {{-- Progress Line --}}
            <div class="absolute left-6 sm:left-10 top-0 bottom-0 w-1 bg-gray-300 dark:bg-gray-700">
                <div
                    class="bg-[#E63946] w-full transition-all duration-500"
                    :style="`height: ${(trackingData.currentStage / (trackingData.stages.length - 1)) * 100}%`"
                ></div>
            </div>

            {{-- Stages --}}
            <div class="space-y-8 relative">
                <template x-for="(stage, index) in trackingData.stages" :key="index">
                    <div class="flex items-start gap-4 relative">
                        <div
                            class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center z-10 font-bold transition-colors"
                            :class="stage.completed ? 'bg-[#E63946] text-white' : (index === trackingData.currentStage ? 'bg-[#457B9D] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500')"
                        >
                            <template x-if="stage.completed">
                                <x-icon name="check-square" class="w-6 h-6 text-white" />
                            </template>
                            <template x-if="!stage.completed && index === trackingData.currentStage">
                                <x-icon name="calendar" class="w-6 h-6 text-white" />
                            </template>
                            <template x-if="!stage.completed && index !== trackingData.currentStage">
                                <span class="text-lg" x-text="index + 1"></span>
                            </template>
                        </div>
                        <div class="flex-1 pt-2">
                            <h3 
                                class="font-bold mb-1"
                                :class="stage.completed ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                                x-text="stage.name"
                            ></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="stage.date"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </x-card>

    {{-- Service Notes --}}
    <x-card>
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Service Updates</h2>
            <x-icon name="wrench" class="text-[#E63946] w-5 h-5" />
        </div>
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
                            <div class="flex items-center gap-1 text-sm text-[#E63946] bg-red-50 dark:bg-red-950/30 px-2 py-1 rounded-lg">
                                <x-icon name="info" class="w-4 h-4 text-[#E63946]" />
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
                                    @click="selectedPhoto = photo.url"
                                >
                                    <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-xl overflow-hidden">
                                        <img
                                            :src="photo.url"
                                            :alt="photo.caption"
                                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                        />
                                    </div>
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors rounded-xl flex items-center justify-center">
                                        <x-icon name="chevron-right" class="text-white opacity-0 group-hover:opacity-100 transition-opacity w-8 h-8" />
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

    {{-- Photo Lightbox --}}
    <template x-if="selectedPhoto">
        <div 
            class="fixed inset-0 bg-black/95 flex items-center justify-center z-50 p-4"
            @click="selectedPhoto = null"
        >
            <button 
                class="absolute top-4 right-4 text-white hover:text-gray-300 cursor-pointer"
                @click="selectedPhoto = null"
            >
                <x-icon name="x" class="w-8 h-8 text-white" />
            </button>
            <img 
                :src="selectedPhoto" 
                alt="Full size preview" 
                class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                @click.stop
            />
        </div>
    </template>

    {{-- Estimated Completion --}}
    <x-card class="bg-gradient-to-r from-[#457B9D] to-[#5A8FB0] text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-white/80 mb-1">Estimated Completion</p>
                <p class="text-2xl font-extrabold" x-text="trackingData.estimated_completion"></p>
            </div>
            <x-icon name="calendar" class="w-12 h-12 text-white/50" />
        </div>
    </x-card>
</div>
@endsection
