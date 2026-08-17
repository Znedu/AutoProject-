@extends('layouts.dashboard')

@section('title', 'User Management | AutoProject+')

@section('content')
<div
    x-data="adminUserManagement()"
    class="space-y-6"
>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">User Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage system users, roles, and account statuses.</p>
        </div>
        <x-button variant="accent" @click="openAddModal()">
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
                <x-button 
                    ::variant="selectedRole === 'admin' ? 'primary' : 'ghost'" 
                    size="sm" 
                    @click="selectedRole = 'admin'"
                >Admins</x-button>
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
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="user.name"></p>
                                <template x-if="user.id === currentUserId">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#E63946]/20 text-[#E63946] border border-[#E63946]/30">You</span>
                                </template>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="user.email"></p>
                            <p class="text-xs text-gray-500" x-text="user.phone"></p>
                        </x-table-cell>
                        <x-table-cell>
                            <span :class="{
                                'bg-blue-500/10 text-blue-500 border border-blue-500/20': user.role_slug === 'customer' || user.role === 'Customer',
                                'bg-green-500/10 text-green-500 border border-green-500/20': user.role_slug === 'mechanic' || user.role === 'Mechanic',
                                'bg-purple-500/10 text-purple-500 border border-purple-500/20': user.role_slug === 'staff' || user.role === 'Staff',
                                'bg-red-500/10 text-red-500 border border-red-500/20': user.role_slug === 'admin' || user.role === 'Administrator' || user.role === 'Admin'
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
                                {{-- Edit Button --}}
                                <button
                                    @click="openEditModal(user)"
                                    class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors cursor-pointer"
                                    title="Edit user"
                                >
                                    <svg class="w-4 h-4 text-[#457B9D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                {{-- Delete Button (Disabled for Self) --}}
                                <button
                                    @click="handleDelete(user)"
                                    :disabled="user.id === currentUserId"
                                    :class="user.id === currentUserId ? 'opacity-30 cursor-not-allowed text-gray-400' : 'hover:bg-red-500/10 text-red-500 cursor-pointer'"
                                    class="p-2 rounded-lg transition-colors"
                                    :title="user.id === currentUserId ? 'You cannot delete your own admin account' : 'Delete user'"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    {{-- Add User Modal --}}
    <x-modal name="add-user-modal" title="Add New User">
        <form @submit.prevent="submitAddUser" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name <span class="text-red-500">*</span></label>
                <x-input x-model="addForm.name" placeholder="Enter full name" required />
                <template x-if="addForm.errors.name">
                    <p class="text-red-500 text-xs mt-1" x-text="addForm.errors.name[0]"></p>
                </template>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <x-input type="email" x-model="addForm.email" placeholder="user@example.com" required />
                    <template x-if="addForm.errors.email">
                        <p class="text-red-500 text-xs mt-1" x-text="addForm.errors.email[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                    <x-input x-model="addForm.phone" placeholder="09123456789" />
                    <template x-if="addForm.errors.phone">
                        <p class="text-red-500 text-xs mt-1" x-text="addForm.errors.phone[0]"></p>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role <span class="text-red-500">*</span></label>
                    <x-select x-model="addForm.role" required>
                        <template x-for="r in rolesList" :key="r.slug">
                            <option :value="r.slug" x-text="r.name"></option>
                        </template>
                    </x-select>
                    <template x-if="addForm.errors.role">
                        <p class="text-red-500 text-xs mt-1" x-text="addForm.errors.role[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Status <span class="text-red-500">*</span></label>
                    <x-select x-model="addForm.status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </x-select>
                    <template x-if="addForm.errors.status">
                        <p class="text-red-500 text-xs mt-1" x-text="addForm.errors.status[0]"></p>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password <span class="text-red-500">*</span></label>
                    <x-input type="password" x-model="addForm.password" placeholder="Min. 8 characters" required />
                    <template x-if="addForm.errors.password">
                        <p class="text-red-500 text-xs mt-1" x-text="addForm.errors.password[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                    <x-input type="password" x-model="addForm.password_confirmation" placeholder="Confirm password" required />
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-gray-200 dark:border-white/10">
                <x-button type="button" variant="ghost" @click="$dispatch('close-modal', { name: 'add-user-modal' })">Cancel</x-button>
                <x-button type="submit" variant="primary" ::disabled="isSaving">
                    <span x-show="!isSaving">Create User</span>
                    <span x-show="isSaving">Saving...</span>
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit User Modal --}}
    <x-modal name="edit-user-modal" title="Edit User">
        <form @submit.prevent="submitEditUser" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name <span class="text-red-500">*</span></label>
                <x-input x-model="editForm.name" placeholder="Enter full name" required />
                <template x-if="editForm.errors.name">
                    <p class="text-red-500 text-xs mt-1" x-text="editForm.errors.name[0]"></p>
                </template>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <x-input type="email" x-model="editForm.email" placeholder="user@example.com" required />
                    <template x-if="editForm.errors.email">
                        <p class="text-red-500 text-xs mt-1" x-text="editForm.errors.email[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                    <x-input x-model="editForm.phone" placeholder="09123456789" />
                    <template x-if="editForm.errors.phone">
                        <p class="text-red-500 text-xs mt-1" x-text="editForm.errors.phone[0]"></p>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role <span class="text-red-500">*</span></label>
                    <x-select x-model="editForm.role" required>
                        <template x-for="r in rolesList" :key="r.slug">
                            <option :value="r.slug" x-text="r.name" :selected="r.slug === editForm.role"></option>
                        </template>
                    </x-select>
                    <template x-if="editForm.errors.role">
                        <p class="text-red-500 text-xs mt-1" x-text="editForm.errors.role[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Status <span class="text-red-500">*</span></label>
                    <x-select x-model="editForm.status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </x-select>
                    <template x-if="editForm.errors.status">
                        <p class="text-red-500 text-xs mt-1" x-text="editForm.errors.status[0]"></p>
                    </template>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 space-y-3">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Change Password (Optional)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input type="password" x-model="editForm.password" placeholder="New password" />
                        <template x-if="editForm.errors.password">
                            <p class="text-red-500 text-xs mt-1" x-text="editForm.errors.password[0]"></p>
                        </template>
                    </div>
                    <div>
                        <x-input type="password" x-model="editForm.password_confirmation" placeholder="Confirm new password" />
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-gray-200 dark:border-white/10">
                <x-button type="button" variant="ghost" @click="$dispatch('close-modal', { name: 'edit-user-modal' })">Cancel</x-button>
                <x-button type="submit" variant="primary" ::disabled="isSaving">
                    <span x-show="!isSaving">Update User</span>
                    <span x-show="isSaving">Saving...</span>
                </x-button>
            </div>
        </form>
    </x-modal>
</div>
@endsection

@push('scripts')
<script>
    function adminUserManagement() {
        return {
            searchQuery: '',
            selectedRole: 'all',
            users: @json($users),
            rolesList: @json($roles),
            currentUserId: @json($currentUserId),
            isSaving: false,

            addForm: {
                name: '',
                email: '',
                phone: '',
                role: 'customer',
                status: 'active',
                password: '',
                password_confirmation: '',
                errors: {}
            },

            editForm: {
                id: null,
                name: '',
                email: '',
                phone: '',
                role: 'customer',
                status: 'active',
                password: '',
                password_confirmation: '',
                errors: {}
            },

            filteredUsers() {
                return this.users.filter(user => {
                    const matchesSearch =
                        user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        user.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        user.phone.toLowerCase().includes(this.searchQuery.toLowerCase());
                    
                    const userRoleLower = (user.role_slug || user.role).toLowerCase();
                    const matchesRole = this.selectedRole === 'all' || userRoleLower === this.selectedRole.toLowerCase();
                    return matchesSearch && matchesRole;
                });
            },

            countRole(roleName) {
                return this.users.filter(u => {
                    const r = (u.role || '').toLowerCase();
                    const rs = (u.role_slug || '').toLowerCase();
                    const target = roleName.toLowerCase();
                    return r === target || rs === target || (target === 'customer' && r.includes('customer')) || (target === 'staff' && r.includes('staff')) || (target === 'mechanic' && r.includes('mechanic'));
                }).length;
            },

            openAddModal() {
                this.addForm = {
                    name: '',
                    email: '',
                    phone: '',
                    role: this.rolesList.length ? this.rolesList[0].slug : 'customer',
                    status: 'active',
                    password: '',
                    password_confirmation: '',
                    errors: {}
                };
                this.$dispatch('open-modal', { name: 'add-user-modal' });
            },

            async submitAddUser() {
                this.isSaving = true;
                this.addForm.errors = {};

                try {
                    const response = await fetch("{{ route('admin.users.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            name: this.addForm.name,
                            email: this.addForm.email,
                            phone: this.addForm.phone,
                            role: this.addForm.role,
                            status: this.addForm.status,
                            password: this.addForm.password,
                            password_confirmation: this.addForm.password_confirmation
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            this.addForm.errors = data.errors;
                        } else {
                            showToast.error(data.message || 'Failed to create user.');
                        }
                        return;
                    }

                    this.users.unshift(data.user);
                    this.$dispatch('close-modal', { name: 'add-user-modal' });
                    showToast.success(data.message || 'User created successfully.');
                } catch (err) {
                    showToast.error('An unexpected error occurred.');
                } finally {
                    this.isSaving = false;
                }
            },

            openEditModal(user) {
                this.editForm = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    phone: user.phone === 'N/A' ? '' : user.phone,
                    role: user.role_slug || 'customer',
                    status: user.status_raw || (user.status ? user.status.toLowerCase() : 'active'),
                    password: '',
                    password_confirmation: '',
                    errors: {}
                };
                this.$dispatch('open-modal', { name: 'edit-user-modal' });
            },

            async submitEditUser() {
                this.isSaving = true;
                this.editForm.errors = {};

                try {
                    const url = `/admin/users/${this.editForm.id}`;
                    const response = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            name: this.editForm.name,
                            email: this.editForm.email,
                            phone: this.editForm.phone,
                            role: this.editForm.role,
                            status: this.editForm.status,
                            password: this.editForm.password,
                            password_confirmation: this.editForm.password_confirmation
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            this.editForm.errors = data.errors;
                        } else {
                            showToast.error(data.message || 'Failed to update user.');
                        }
                        return;
                    }

                    const idx = this.users.findIndex(u => u.id === this.editForm.id);
                    if (idx !== -1) {
                        this.users[idx] = data.user;
                    }

                    this.$dispatch('close-modal', { name: 'edit-user-modal' });
                    showToast.success(data.message || 'User updated successfully.');
                } catch (err) {
                    showToast.error('An unexpected error occurred.');
                } finally {
                    this.isSaving = false;
                }
            },

            handleDelete(user) {
                if (user.id === this.currentUserId) {
                    showToast.error('You cannot delete your own admin account.');
                    return;
                }

                window.showConfirm({
                    title: 'Delete User',
                    message: `Are you sure you want to delete user "${user.name}"? This action cannot be undone.`,
                    confirmText: 'Delete User',
                    variant: 'danger',
                    onConfirm: async () => {
                        try {
                            const response = await fetch(`/admin/users/${user.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                showToast.error(data.message || 'Failed to delete user.');
                                return;
                            }

                            this.users = this.users.filter(u => u.id !== user.id);
                            showToast.success(data.message || 'User deleted successfully.');
                        } catch (err) {
                            showToast.error('An unexpected error occurred while deleting user.');
                        }
                    }
                });
            }
        };
    }
</script>
@endpush
