@extends('layouts.dashboard')

@section('title', 'Service Management | AutoProject+')

@section('content')
<div
    x-data="{
        showAddForm: false,
        newService: { name: '', description: '', minCost: '', maxCost: '', duration: '' },
        services: @js($services),
        handleAddService() {
            const id = this.services.length + 1;
            this.services.push({
                id: id,
                name: this.newService.name,
                description: this.newService.description,
                minCost: Number(this.newService.minCost),
                maxCost: Number(this.newService.maxCost),
                duration: this.newService.duration,
                status: 'Active'
            });
            showToast.success('Service added successfully!');
            this.showAddForm = false;
            this.newService = { name: '', description: '', minCost: '', maxCost: '', duration: '' };
        },
        handleEdit(id) {
            showToast.info('Edit service ' + id);
        },
        handleToggleStatus(id) {
            const svc = this.services.find(s => s.id === id);
            if (svc) {
                svc.status = svc.status === 'Active' ? 'Inactive' : 'Active';
                showToast.success('Service status updated!');
            }
        },
        handleDelete(id) {
            if (confirm('Are you sure you want to delete this service?')) {
                this.services = this.services.filter(s => s.id !== id);
                showToast.success('Service deleted successfully!');
            }
        }
    }"
    class="space-y-6"
>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Service Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage available services and cost estimates.</p>
        </div>
        <x-button variant="accent" @click="showAddForm = !showAddForm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Service
        </x-button>
    </div>

    {{-- Add Service Form --}}
    <div x-show="showAddForm" x-transition style="display: none;">
        <x-card>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add New Service</h2>
            <form @submit.prevent="handleAddService()" class="space-y-4">
                <x-input
                    label="Service Name"
                    placeholder="e.g., Suspension Upgrade"
                    x-model="newService.name"
                    required
                />
                <x-textarea
                    label="Description"
                    placeholder="Detailed description of the service..."
                    x-model="newService.description"
                    required
                />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Minimum Cost (₱)"
                        type="number"
                        placeholder="20000"
                        x-model="newService.minCost"
                        required
                    />
                    <x-input
                        label="Maximum Cost (₱)"
                        type="number"
                        placeholder="60000"
                        x-model="newService.maxCost"
                        required
                    />
                </div>
                <x-input
                    label="Estimated Duration"
                    placeholder="e.g., 3-4 days"
                    x-model="newService.duration"
                    required
                />
                <div class="flex gap-3">
                    <x-button type="submit" variant="accent">Add Service</x-button>
                    <x-button type="button" variant="outline" @click="showAddForm = false">Cancel</x-button>
                </div>
            </form>
        </x-card>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Services</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white" x-text="services.length"></p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Active Services</p>
            <p class="text-3xl font-bold text-green-500" x-text="services.filter(s => s.status === 'Active').length"></p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Most Popular</p>
            <p class="text-lg font-bold text-[#E63946]">Engine Custom.</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Avg. Cost Range</p>
            <p class="text-lg font-bold text-[#457B9D]">₱25K-₱90K</p>
        </x-card>
    </div>

    {{-- Services List --}}
    <div class="space-y-4">
        <template x-for="service in services" :key="service.id">
            <x-card>
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1 space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2" x-text="service.name"></h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed" x-text="service.description"></p>
                            </div>
                            <span :class="service.status === 'Active' 
                                ? 'bg-green-500/10 text-green-500 border border-green-500/20' 
                                : 'bg-gray-500/10 text-gray-500 border border-gray-500/20'" 
                                class="px-3 py-1 rounded-full text-xs font-semibold flex-shrink-0" 
                                x-text="service.status"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm pt-2">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 mb-1 text-xs">Cost Range</p>
                                <p class="font-bold text-[#E63946]">
                                    ₱<span x-text="service.minCost.toLocaleString()"></span> - ₱<span x-text="service.maxCost.toLocaleString()"></span>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 mb-1 text-xs">Duration</p>
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="service.duration"></p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 mb-1 text-xs">Service ID</p>
                                <p class="font-semibold text-gray-900 dark:text-white">SRV-<span x-text="service.id"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex lg:flex-col gap-2 justify-end lg:justify-start">
                        <x-button
                            variant="secondary"
                            size="sm"
                            @click="handleEdit(service.id)"
                        >
                            Edit
                        </x-button>
                        <x-button
                            variant="outline"
                            size="sm"
                            @click="handleToggleStatus(service.id)"
                            x-text="service.status === 'Active' ? 'Deactivate' : 'Activate'"
                        >
                        </x-button>
                        <x-button
                            variant="outline"
                            size="sm"
                            @click="handleDelete(service.id)"
                            class="text-red-500 border-red-500/20 hover:bg-red-500/10 hover:border-red-500"
                        >
                            Delete
                        </x-button>
                    </div>
                </div>
            </x-card>
        </template>
    </div>

    {{-- Cost Guidelines Footer Card --}}
    <x-card class="bg-[#457B9D]/10 border border-[#457B9D]/20 shadow-none">
        <h3 class="font-bold text-[#457B9D] mb-3 text-lg flex items-center gap-2">
            💡 Cost Estimation Guidelines
        </h3>
        <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <li>• Set realistic cost ranges based on parts, labor, and materials</li>
            <li>• Consider variations in vehicle models and customization complexity</li>
            <li>• Include buffer for unexpected issues or additional work</li>
            <li>• Review and update costs regularly based on market prices</li>
            <li>• Provide detailed breakdown during booking approval process</li>
        </ul>
    </x-card>
</div>
@endsection
