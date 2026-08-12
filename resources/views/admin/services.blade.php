@extends('layouts.dashboard')

@section('title', 'Service Management | AutoProject+')

@section('content')
<div
    x-data="adminServiceManagement()"
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Category select with inline "Add New Category" --}}
                    <div class="w-full">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-gray-700 dark:text-[#B8B8B8] font-medium">
                                Service Category <span class="text-[#E63946] ml-1">*</span>
                            </label>
                            <button type="button"
                                @click="showAddCategoryForm = !showAddCategoryForm"
                                class="text-xs font-semibold flex items-center gap-1 transition-colors"
                                :class="showAddCategoryForm ? 'text-gray-500 dark:text-gray-400 hover:text-red-500' : 'text-[#457B9D] hover:text-[#E63946]'"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        :d="showAddCategoryForm ? 'M6 18L18 6M6 6l12 12' : 'M12 4v16m8-8H4'" />
                                </svg>
                                <span x-text="showAddCategoryForm ? 'Cancel' : '+ New Category'"></span>
                            </button>
                        </div>
                        <select
                            x-model="newService.service_category_id"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all"
                        >
                            <option value="">Select a category</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                        {{-- Inline add-category mini-form --}}
                        <div
                            x-show="showAddCategoryForm"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            style="display:none;"
                            class="mt-2 p-3 rounded-xl border border-dashed border-[#457B9D]/40 bg-[#457B9D]/5 dark:bg-[#457B9D]/10"
                        >
                            <p class="text-xs text-[#457B9D] dark:text-[#6baac8] font-semibold mb-2">New category name:</p>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    x-model="newCategoryName"
                                    placeholder="e.g., Suspension Systems"
                                    @keydown.enter.prevent="handleAddCategory()"
                                    class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all"
                                />
                                <button
                                    type="button"
                                    @click="handleAddCategory()"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-[#E63946] text-white hover:bg-[#D62839] transition-colors whitespace-nowrap"
                                >
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                    <x-input
                        label="Service Code (Auto-generated if blank)"
                        placeholder="e.g., ext-004"
                        x-model="newService.code"
                    />
                </div>
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

    {{-- Edit Service Modal (inline, controlled by parent Alpine scope) --}}
    <div
        x-show="showEditModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        style="display: none;"
        @keydown.escape.window="showEditModal = false"
    >
        {{-- Backdrop --}}
        <div
            class="fixed inset-0 bg-black/60 backdrop-blur-sm"
            @click="showEditModal = false"
        ></div>

        {{-- Modal Panel --}}
        <div
            @click.stop
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="relative z-10 bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl shadow-xl w-full sm:max-w-2xl max-h-[90vh] overflow-y-auto"
        >
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Service</h3>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                    <x-icon name="close" class="h-5 w-5" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-4">
                <form @submit.prevent="handleUpdateService()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="w-full">
                            <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                                Service Category <span class="text-[#E63946] ml-1">*</span>
                            </label>
                            <select
                                x-model="editingService.service_category_id"
                                required
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all"
                            >
                                <option value="">Select a category</option>
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                        </div>
                        <x-input
                            label="Service Code"
                            placeholder="e.g., ext-001"
                            x-model="editingService.code"
                            required
                        />
                    </div>
                    <x-input
                        label="Service Name"
                        placeholder="e.g., Suspension Upgrade"
                        x-model="editingService.name"
                        required
                    />
                    <x-textarea
                        label="Description"
                        placeholder="Detailed description of the service..."
                        x-model="editingService.description"
                        required
                    />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input
                            label="Minimum Cost (₱)"
                            type="number"
                            placeholder="20000"
                            x-model="editingService.minCost"
                            required
                        />
                        <x-input
                            label="Maximum Cost (₱)"
                            type="number"
                            placeholder="60000"
                            x-model="editingService.maxCost"
                            required
                        />
                    </div>
                    <x-input
                        label="Estimated Duration"
                        placeholder="e.g., 3-4 days"
                        x-model="editingService.duration"
                        required
                    />
                    <div class="flex gap-3 justify-end pt-4 border-t border-gray-200 dark:border-white/10">
                        <x-button type="button" variant="outline" @click="showEditModal = false">Cancel</x-button>
                        <x-button type="submit" variant="accent">Save Changes</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function adminServiceManagement() {
        return {
            showAddForm: false,
            showEditModal: false,
            showAddCategoryForm: false,
            newCategoryName: '',
            categories: @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])),
            newService: { name: '', description: '', minCost: '', maxCost: '', duration: '', service_category_id: '', code: '' },
            editingService: { id: null, name: '', description: '', minCost: 0, maxCost: 0, duration: '', service_category_id: '', code: '' },
            services: @json($services),

            handleAddService() {
                fetch('/admin/services', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.newService)
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => { throw err; });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        this.services.push(data.service);
                        showToast.success(data.message);
                        this.showAddForm = false;
                        this.newService = { name: '', description: '', minCost: '', maxCost: '', duration: '', service_category_id: '', code: '' };
                    } else {
                        showToast.error(data.message || 'Failed to add service.');
                    }
                })
                .catch(err => {
                    const errMsg = err.message || (err.errors ? Object.values(err.errors).flat().join('\n') : 'An error occurred.');
                    showToast.error(errMsg);
                });
            },

            handleAddCategory() {
                const name = this.newCategoryName.trim();
                if (!name) {
                    showToast.error('Please enter a category name.');
                    return;
                }
                fetch('/admin/services/categories', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ name })
                })
                .then(res => {
                    if (!res.ok) return res.json().then(err => { throw err; });
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        this.categories.push(data.category);
                        this.newService.service_category_id = data.category.id;
                        this.newCategoryName = '';
                        this.showAddCategoryForm = false;
                        showToast.success(data.message);
                    } else {
                        showToast.error(data.message || 'Failed to create category.');
                    }
                })
                .catch(err => {
                    const msg = err.message || (err.errors ? Object.values(err.errors).flat().join('\n') : 'An error occurred.');
                    showToast.error(msg);
                });
            },

            handleEdit(id) {
                const svc = this.services.find(s => s.id === id);
                if (svc) {
                    this.editingService = { ...svc };
                    this.showEditModal = true;
                }
            },

            handleUpdateService() {
                fetch(`/admin/services/${this.editingService.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.editingService)
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => { throw err; });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        const index = this.services.findIndex(s => s.id === this.editingService.id);
                        if (index !== -1) {
                            this.services[index] = data.service;
                        }
                        showToast.success(data.message);
                        this.showEditModal = false;
                    } else {
                        showToast.error(data.message || 'Failed to update service.');
                    }
                })
                .catch(err => {
                    const errMsg = err.message || (err.errors ? Object.values(err.errors).flat().join('\n') : 'An error occurred.');
                    showToast.error(errMsg);
                });
            },

            handleToggleStatus(id) {
                fetch(`/admin/services/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => { throw err; });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        const index = this.services.findIndex(s => s.id === id);
                        if (index !== -1) {
                            this.services[index] = data.service;
                        }
                        showToast.success(data.message);
                    } else {
                        showToast.error(data.message || 'Failed to update status.');
                    }
                })
                .catch(err => {
                    showToast.error(err.message || 'An error occurred.');
                });
            },

            handleDelete(id) {
                window.showConfirm({
                    title: 'Delete Service',
                    message: 'Are you sure you want to delete this service? This action cannot be undone.',
                    confirmText: 'Delete Service',
                    variant: 'danger',
                    onConfirm: () => {
                        fetch(`/admin/services/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(res => {
                            if (!res.ok) {
                                return res.json().then(err => { throw err; });
                            }
                            return res.json();
                        })
                        .then(data => {
                            if (data.success) {
                                this.services = this.services.filter(s => s.id !== id);
                                showToast.success(data.message);
                            } else {
                                showToast.error(data.message || 'Failed to delete service.');
                            }
                        })
                        .catch(err => {
                            showToast.error(err.message || 'An error occurred.');
                        });
                    }
                });
            }
        };
    }
</script>
@endpush
