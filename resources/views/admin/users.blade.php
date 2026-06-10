@extends('layouts.dashboard')

@section('title', 'User Management | AutoProject+')

@section('content')
<div
    x-data="{
        searchQuery: '',
        selectedRole: 'all',
        users: [
            { id: 1, name: 'Juan Dela Cruz', email: 'juan.delacruz@email.com', phone: '+63 912 345 6789', role: 'Customer', status: 'Active', joinDate: 'Jan 15, 2024' },
            { id: 2, name: 'Maria Santos', email: 'maria.santos@email.com', phone: '+63 917 888 9999', role: 'Customer', status: 'Active', joinDate: 'Feb 20, 2024' },
            { id: 3, name: 'John Mechanic', email: 'john.m@autoproject.com', phone: '+63 920 111 2222', role: 'Mechanic', status: 'Active', joinDate: 'Jan 5, 2023' },
            { id: 4, name: 'Sarah Staff', email: 'sarah.s@autoproject.com', phone: '+63 923 333 4444', role: 'Staff', status: 'Active', joinDate: 'Mar 10, 2023' },
            { id: 5, name: 'Pedro Rodriguez', email: 'pedro.r@email.com', phone: '+63 915 555 6666', role: 'Customer', status: 'Inactive', joinDate: 'Dec 1, 2023' }
        ],
        filteredUsers() {
            return this.users.filter(user => {
                const matchesSearch = user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    user.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    user.phone.includes(this.searchQuery);
                const matchesRole = this.selectedRole === 'all' || user.role.toLowerCase() === this.selectedRole.toLowerCase();
                return matchesSearch && matchesRole;
            });
        },
        countRole(role) {
            return this.users.filter(u => u.role.toLowerCase() === role.toLowerCase()).length;
        },
        handleDelete(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                this.users = this.users.filter(u => u.id !== id);
                showToast.success('User ' + id + ' deleted');
            }
        },
        handleEdit(id) {
            showToast.info('Edit user ' + id);
        }
    }"
    class="space-y-6"
>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">User Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage system users and roles.</p>
        </div>
        <x-button variant="accent" @click="showToast.info('Add user form trigger')">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New User
        </x-button>
    </div>

    {{-- Filters & Search --}}
    <x-card>
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <x-input
                    placeholder="Search users by name, email, or phone..."
                    x-model="searchQuery"
                    class="pl-10"
                />
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <x-button 
                    ::variant="selectedRole === 'all' ? 'primary' : 'ghost'" 
                    size="sm" 
                    @click="selectedRole = 'all'"
                >All Users</x-button>
                <x-button 
                    ::variant="selectedRole === 'customer' ? 'primary' : 'ghost'" 
                    size="sm" 
                    @click="selectedRole = 'customer'"
                >Customers</x-button>
                <x-button 
                    ::variant="selectedRole === 'staff' ? 'primary' : 'ghost'" 
                    size="sm" 
                    @click="selectedRole = 'staff'"
                >Staff</x-button>
                <x-button 
                    ::variant="selectedRole === 'mechanic' ? 'primary' : 'ghost'" 
                    size="sm" 
                    @click="selectedRole = 'mechanic'"
                >Mechanics</x-button>
            </div>
        </div>
    </x-card>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Users</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white" x-text="users.length"></p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Customers</p>
            <p class="text-3xl font-bold text-[#457B9D]" x-text="countRole('Customer')"></p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Staff</p>
            <p class="text-3xl font-bold text-[#E63946]" x-text="countRole('Staff')"></p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Mechanics</p>
            <p class="text-3xl font-bold text-green-500" x-text="countRole('Mechanic')"></p>
        </x-card>
    </div>

    {{-- Users Table --}}
    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-table-header>
                <x-table-row>
                    <x-table-head>Name</x-table-head>
                    <x-table-head>Contact</x-table-head>
                    <x-table-head>Role</x-table-head>
                    <x-table-head>Status</x-table-head>
                    <x-table-head>Join Date</x-table-head>
                    <x-table-head>Actions</x-table-head>
                </x-table-row>
            </x-table-header>
            <x-table-body>
                <template x-for="user in filteredUsers()" :key="user.id">
                    <x-table-row>
                        <x-table-cell>
                            <p class="font-semibold text-gray-900 dark:text-white" x-text="user.name"></p>
                        </x-table-cell>
                        <x-table-cell>
                            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="user.email"></p>
                            <p class="text-xs text-gray-500" x-text="user.phone"></p>
                        </x-table-cell>
                        <x-table-cell>
                            <span :class="{
                                'bg-blue-500/10 text-blue-500 border border-blue-500/20': user.role === 'Customer',
                                'bg-green-500/10 text-green-500 border border-green-500/20': user.role === 'Mechanic',
                                'bg-purple-500/10 text-purple-500 border border-purple-500/20': user.role === 'Staff',
                                'bg-red-500/10 text-red-500 border border-red-500/20': user.role === 'Admin'
                            }" class="px-3 py-1 rounded-full text-xs font-semibold" x-text="user.role"></span>
                        </x-table-cell>
                        <x-table-cell>
                            <span :class="user.status === 'Active' 
                                ? 'bg-green-500/10 text-green-500 border border-green-500/20' 
                                : 'bg-gray-500/10 text-gray-500 border border-gray-500/20'" 
                                class="px-3 py-1 rounded-full text-xs font-semibold" 
                                x-text="user.status"></span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="user.joinDate"></span>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="flex gap-2">
                                <button
                                    @click="handleEdit(user.id)"
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4 text-[#457B9D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button
                                    @click="handleDelete(user.id)"
                                    class="p-2 hover:bg-red-500/10 rounded-lg transition-colors cursor-pointer"
                                >
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                </template>
            </x-table-body>
        </x-table>
    </x-card>
</div>
@endsection
