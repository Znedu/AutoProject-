@extends('layouts.dashboard')

@section('title', 'My Vehicles | AutoProject+')

@php
    $vehiclesJson = $vehicles->map(fn ($v) => [
        'id'           => $v->id,
        'make'         => $v->make,
        'model'        => $v->model,
        'year'         => $v->year,
        'plate_number' => $v->plate_number,
        'color'        => $v->color ?? '',
        'notes'        => $v->notes ?? '',
        'display_name' => $v->display_name,
    ])->values();
@endphp

@section('content')
<div
    x-data="customerVehicles()"
    class="max-w-4xl mx-auto space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">My Vehicles</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your registered vehicles for faster booking.</p>
        </div>
        <x-button variant="accent" @click="showAddForm = !showAddForm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Vehicle
        </x-button>
    </div>

    {{-- Add Vehicle Form --}}
    <div x-show="showAddForm" x-transition x-collapse style="display:none;">
        <x-card>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-icon name="car" class="w-6 h-6 text-[#E63946]" />
                Add New Vehicle
            </h2>
            <form @submit.prevent="handleAddVehicle()" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Vehicle Make" placeholder="e.g., Honda, Toyota" x-model="newVehicle.make" required />
                    <x-input label="Vehicle Model" placeholder="e.g., Civic, Supra" x-model="newVehicle.model" required />
                    <x-input label="Year" type="number" placeholder="e.g., 2020" x-model="newVehicle.year" required />
                    <x-input label="Plate Number" placeholder="e.g., ABC 1234" x-model="newVehicle.plate_number" required />
                    <x-input label="Color (Optional)" placeholder="e.g., Midnight Black" x-model="newVehicle.color" />
                    <x-textarea label="Notes (Optional)" placeholder="e.g., modified suspension, custom wrap..." x-model="newVehicle.notes" rows="2" />
                </div>
                <div class="flex gap-3 pt-2">
                    <x-button type="submit" variant="accent">Save Vehicle</x-button>
                    <x-button type="button" variant="outline" @click="showAddForm = false; resetNewVehicle()">Cancel</x-button>
                </div>
            </form>
        </x-card>
    </div>

    {{-- Stats bar --}}
    <div class="grid grid-cols-2 gap-4">
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Vehicles</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white" x-text="vehicles.length"></p>
        </x-card>
        <x-card class="text-center bg-[#457B9D]/10 border border-[#457B9D]/20 shadow-none">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Ready for Booking</p>
            <p class="text-3xl font-bold text-[#457B9D]" x-text="vehicles.length"></p>
        </x-card>
    </div>

    {{-- Empty state --}}
    <template x-if="vehicles.length === 0">
        <x-card>
            <div class="text-center py-12">
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center">
                        <x-icon name="car" class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No vehicles added yet</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Add your vehicle to quickly fill in booking details next time.</p>
                <x-button variant="accent" @click="showAddForm = true">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Vehicle
                </x-button>
            </div>
        </x-card>
    </template>

    {{-- Vehicles List --}}
    <div class="space-y-4">
        <template x-for="vehicle in vehicles" :key="vehicle.id">
            <x-card>
                <div class="flex flex-col sm:flex-row gap-4 sm:items-start">
                    {{-- Icon --}}
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-xl bg-[#E63946]/10 dark:bg-[#E63946]/20 flex items-center justify-center border border-[#E63946]/20">
                            <x-icon name="car" class="w-6 h-6 text-[#E63946]" />
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1" x-text="vehicle.display_name"></h3>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <span>🔖 <span x-text="vehicle.plate_number"></span></span>
                            <template x-if="vehicle.color">
                                <span>🎨 <span x-text="vehicle.color"></span></span>
                            </template>
                            <template x-if="vehicle.notes">
                                <span class="truncate max-w-xs">📝 <span x-text="vehicle.notes"></span></span>
                            </template>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2 flex-shrink-0">
                        <x-button variant="secondary" size="sm" @click="handleEdit(vehicle)">Edit</x-button>
                        <x-button
                            variant="outline"
                            size="sm"
                            @click="handleDelete(vehicle.id)"
                            class="text-red-500 border-red-500/20 hover:bg-red-500/10 hover:border-red-500"
                        >Delete</x-button>
                    </div>
                </div>
            </x-card>
        </template>
    </div>

    {{-- Tip card --}}
    <x-card class="bg-[#457B9D]/10 border border-[#457B9D]/20 shadow-none">
        <div class="flex gap-3">
            <x-icon name="info" class="w-5 h-5 text-[#457B9D] flex-shrink-0 mt-0.5" />
            <div class="text-sm text-gray-700 dark:text-gray-300">
                <p class="font-semibold text-[#457B9D] mb-1">💡 Pro Tip</p>
                <p>Once you save your vehicle here, it will appear as a selectable option on the <a href="{{ route('customer.book-service') }}" class="underline text-[#457B9D] hover:text-[#E63946] transition-colors">Book Service</a> page so you never have to type your vehicle details again.</p>
            </div>
        </div>
    </x-card>

    {{-- Edit Vehicle Modal --}}
    <div
        x-show="showEditModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        style="display:none;"
        @keydown.escape.window="showEditModal = false"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showEditModal = false"></div>

        {{-- Panel --}}
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
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="car" class="w-5 h-5 text-[#E63946]" />
                    Edit Vehicle
                </h3>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                    <x-icon name="close" class="h-5 w-5" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-4">
                <form @submit.prevent="handleUpdateVehicle()" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input label="Vehicle Make" placeholder="e.g., Honda, Toyota" x-model="editingVehicle.make" required />
                        <x-input label="Vehicle Model" placeholder="e.g., Civic, Supra" x-model="editingVehicle.model" required />
                        <x-input label="Year" type="number" placeholder="e.g., 2020" x-model="editingVehicle.year" required />
                        <x-input label="Plate Number" placeholder="e.g., ABC 1234" x-model="editingVehicle.plate_number" required />
                        <x-input label="Color (Optional)" placeholder="e.g., Midnight Black" x-model="editingVehicle.color" />
                        <x-textarea label="Notes (Optional)" placeholder="e.g., modified suspension..." x-model="editingVehicle.notes" rows="2" />
                    </div>
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
    function customerVehicles() {
        return {
            showAddForm: false,
            showEditModal: false,
            vehicles: @json($vehiclesJson),
            newVehicle: { make: '', model: '', year: '', plate_number: '', color: '', notes: '' },
            editingVehicle: { id: null, make: '', model: '', year: '', plate_number: '', color: '', notes: '' },

            resetNewVehicle() {
                this.newVehicle = { make: '', model: '', year: '', plate_number: '', color: '', notes: '' };
            },

            handleAddVehicle() {
                fetch('/customer/vehicles', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.newVehicle)
                })
                .then(res => {
                    if (!res.ok) return res.json().then(err => { throw err; });
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        this.vehicles.unshift(data.vehicle);
                        showToast.success(data.message);
                        this.showAddForm = false;
                        this.resetNewVehicle();
                    } else {
                        showToast.error(data.message || 'Failed to add vehicle.');
                    }
                })
                .catch(err => {
                    const msg = err.message || (err.errors ? Object.values(err.errors).flat().join('\n') : 'An error occurred.');
                    showToast.error(msg);
                });
            },

            handleEdit(vehicle) {
                this.editingVehicle = { ...vehicle };
                this.showEditModal = true;
            },

            handleUpdateVehicle() {
                fetch(`/customer/vehicles/${this.editingVehicle.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.editingVehicle)
                })
                .then(res => {
                    if (!res.ok) return res.json().then(err => { throw err; });
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        const idx = this.vehicles.findIndex(v => v.id === this.editingVehicle.id);
                        if (idx !== -1) this.vehicles[idx] = data.vehicle;
                        showToast.success(data.message);
                        this.showEditModal = false;
                    } else {
                        showToast.error(data.message || 'Failed to update vehicle.');
                    }
                })
                .catch(err => {
                    const msg = err.message || (err.errors ? Object.values(err.errors).flat().join('\n') : 'An error occurred.');
                    showToast.error(msg);
                });
            },

            handleDelete(id) {
                if (!confirm('Remove this vehicle from your garage?')) return;
                fetch(`/customer/vehicles/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => {
                    if (!res.ok) return res.json().then(err => { throw err; });
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        this.vehicles = this.vehicles.filter(v => v.id !== id);
                        showToast.success(data.message);
                    } else {
                        showToast.error(data.message || 'Failed to delete vehicle.');
                    }
                })
                .catch(err => showToast.error(err.message || 'An error occurred.'));
            }
        };
    }
</script>
@endpush
