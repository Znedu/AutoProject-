@php
    $user = auth()->user();
    $initialUnreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $initialNotifications = $user ? $user->notifications()->latest()->limit(20)->get()->map(function ($n) {
        return [
            'id'         => $n->id,
            'read_at'    => $n->read_at?->toIso8601String(),
            'is_read'    => $n->read(),
            'created_at' => $n->created_at->diffForHumans(),
            'data'       => $n->data,
        ];
    })->values() : collect();
@endphp

<div
    x-data="{
        open: false,
        activeTab: 'all',
        count: {{ $initialUnreadCount }},
        notifications: {{ \Illuminate\Support\Js::from($initialNotifications) }},
        loading: false,
        soundEnabled: localStorage.getItem('notif_sound_enabled') !== 'false',
        toastNotification: null,
        toastTimer: null,
        timer: null,

        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            localStorage.setItem('notif_sound_enabled', this.soundEnabled ? 'true' : 'false');
        },

        playAudioChime() {
            if (!this.soundEnabled) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5
                gain.gain.setValueAtTime(0.1, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            } catch (e) {
                // AudioContext not allowed before user gesture, ignore safely
            }
        },

        showToast(n) {
            this.toastNotification = n;
            if (this.toastTimer) clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => {
                this.toastNotification = null;
            }, 5000);
        },

        get filteredNotifications() {
            if (this.activeTab === 'unread') {
                return this.notifications.filter(n => !n.is_read);
            }
            return this.notifications;
        },

        async fetchNotifications() {
            try {
                const response = await fetch('{{ route('notifications.index') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.status === 401 || response.redirected) {
                    window.location.reload();
                    return;
                }
                if (response.ok) {
                    const data = await response.json();
                    const newCount = data.count || 0;
                    const newItems = data.notifications || [];

                    // If unread count increased, notify user visually and audibly
                    if (newCount > this.count && newItems.length > 0) {
                        const latestUnread = newItems.find(n => !n.is_read);
                        if (latestUnread) {
                            this.showToast(latestUnread);
                            this.playAudioChime();
                        }
                    }

                    this.count = newCount;
                    this.notifications = newItems;
                }
            } catch (e) {
                console.error('Failed to fetch notifications:', e);
            }
        },

        async markAsRead(notification, navigate = true) {
            const actionUrl = notification.data && notification.data.action_url
                ? notification.data.action_url
                : null;

            if (!notification.is_read) {
                try {
                    const res = await fetch(`/notifications/${notification.id}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    if (res.status === 401 || res.redirected) {
                        window.location.href = '{{ route('login') }}';
                        return;
                    }
                    notification.is_read = true;
                    this.count = Math.max(0, this.count - 1);
                } catch (e) {
                    console.error('Failed to mark notification as read:', e);
                }
            }

            if (navigate && actionUrl) {
                window.location.href = actionUrl;
            }
        },

        async markAllRead() {
            try {
                const res = await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (res.status === 401 || res.redirected) {
                    window.location.reload();
                    return;
                }
                this.count = 0;
                this.notifications.forEach(n => n.is_read = true);
            } catch (e) {
                console.error('Failed to mark all notifications as read:', e);
            }
        },

        async deleteNotification(notification, event) {
            if (event) event.stopPropagation();
            try {
                const res = await fetch(`/notifications/${notification.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    if (!notification.is_read) {
                        this.count = Math.max(0, this.count - 1);
                    }
                    this.notifications = this.notifications.filter(n => n.id !== notification.id);
                }
            } catch (e) {
                console.error('Failed to delete notification:', e);
            }
        },

        async clearAll() {
            if (!confirm('Clear all notifications?')) return;
            try {
                const res = await fetch('{{ route('notifications.clear-all') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    this.count = 0;
                    this.notifications = [];
                }
            } catch (e) {
                console.error('Failed to clear all notifications:', e);
            }
        },

        getIconSvg(name) {
            switch(name) {
                case 'clipboard-list':
                    return `<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01'></path></svg>`;
                case 'wrench':
                    return `<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z'></path></svg>`;
                case 'credit-card':
                    return `<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><rect width='20' height='14' x='2' y='5' rx='2'></rect><line x1='2' x2='22' y1='10' y2='10'></line></svg>`;
                case 'message-square':
                    return `<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z'></path></svg>`;
                case 'check-circle':
                    return `<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M22 11.08V12a10 10 0 1 1-5.93-9.14'></path><polyline points='22 4 12 14.01 9 11.01'></polyline></svg>`;
                case 'alert-triangle':
                    return `<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z'></path><line x1='12' y1='9' x2='12' y2='13'></line><line x1='12' y1='17' x2='12.01' y2='17'></line></svg>`;
                case 'calendar':
                    return `<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z'></path></svg>`;
                default:
                    return `<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10.3 21a1.94 1.94 0 0 0 3.4 0'></path></svg>`;
            }
        },

        init() {
            // Poll every 10 seconds for real-time notification updates
            this.timer = setInterval(() => this.fetchNotifications(), 10000);
        }
    }"
    @click.away="open = false"
    class="relative"
>
    <!-- Bell Trigger Button -->
    <button
        @click="open = !open"
        type="button"
        class="relative p-2.5 rounded-xl text-gray-600 dark:text-white/80 hover:bg-gray-100 dark:hover:bg-white/10 hover:text-gray-900 dark:hover:text-white transition-colors duration-200 focus:outline-none cursor-pointer"
        aria-label="Notifications"
    >
        <x-icon name="bell" class="w-5 h-5" />

        <!-- Unread Badge with Pulse Effect -->
        <span
            x-show="count > 0"
            x-cloak
            class="absolute top-1 right-1 flex items-center justify-center min-w-[18px] h-[18px]"
        >
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#E63946] opacity-75"></span>
            <span
                x-text="count > 99 ? '99+' : count"
                class="relative inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-[#E63946] rounded-full shadow-md"
            >{{ $initialUnreadCount > 99 ? '99+' : ($initialUnreadCount > 0 ? $initialUnreadCount : '') }}</span>
        </span>
    </button>

    <!-- Real-Time Floating Live Toast Notification Banner -->
    <div
        x-show="toastNotification"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        @click="if (toastNotification) markAsRead(toastNotification)"
        class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full bg-white dark:bg-[#1A1A1A] border-l-4 border-l-[#E63946] border border-gray-200 dark:border-white/10 p-4 rounded-2xl shadow-2xl flex items-start gap-3 cursor-pointer"
    >
        <div class="p-2 rounded-xl bg-[#E63946]/10 text-[#E63946] shrink-0 mt-0.5" x-html="getIconSvg(toastNotification?.data?.icon)"></div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
                <p class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="toastNotification?.data?.title || 'New Notification'"></p>
                <span class="text-[10px] text-gray-600 dark:text-white/60">Just now</span>
            </div>
            <p class="text-xs text-gray-600 dark:text-white/80 line-clamp-2 mt-0.5" x-text="toastNotification?.data?.message"></p>
        </div>
        <button
            @click.stop="toastNotification = null"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1"
        >
            <x-icon name="x" class="w-4 h-4" />
        </button>
    </div>

    <!-- Notification Dropdown Panel -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
        class="absolute right-0 mt-3 w-80 sm:w-96 bg-white dark:bg-[#121212] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl z-50 overflow-hidden"
    >
        <!-- Dropdown Header -->
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between bg-gray-50/50 dark:bg-white/5">
            <div class="flex items-center gap-2">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Notifications</h3>
                <span
                    x-show="count > 0"
                    x-cloak
                    x-text="count"
                    class="px-2 py-0.5 text-xs font-semibold bg-[#E63946]/10 text-[#E63946] rounded-full"
                ></span>
            </div>

            <div class="flex items-center gap-2">
                <!-- Sound Chime Toggle -->
                <button
                    @click="toggleSound()"
                    type="button"
                    title="Toggle Notification Sound"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-200/50 dark:hover:bg-white/10 transition-colors"
                >
                    <template x-if="soundEnabled">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path></svg>
                    </template>
                    <template x-if="!soundEnabled">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path></svg>
                    </template>
                </button>

                <button
                    x-show="count > 0"
                    x-cloak
                    @click="markAllRead()"
                    type="button"
                    class="text-xs text-[#E63946] hover:underline font-medium cursor-pointer"
                >
                    Mark all read
                </button>
            </div>
        </div>

        <!-- Dropdown Subheader Tabs (All / Unread) -->
        <div class="px-5 py-2 border-b border-gray-100 dark:border-white/5 bg-gray-50/30 dark:bg-white/[0.02] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <button
                    @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'text-[#E63946] font-semibold border-b-2 border-[#E63946]' : 'text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white'"
                    class="text-xs pb-1 transition-colors cursor-pointer"
                >
                    All
                </button>
                <button
                    @click="activeTab = 'unread'"
                    :class="activeTab === 'unread' ? 'text-[#E63946] font-semibold border-b-2 border-[#E63946]' : 'text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white'"
                    class="text-xs pb-1 transition-colors cursor-pointer flex items-center gap-1"
                >
                    Unread
                    <span x-show="count > 0" class="px-1.5 py-0.2 text-[10px] bg-[#E63946]/10 text-[#E63946] rounded-full" x-text="count"></span>
                </button>
            </div>

            <button
                x-show="notifications.length > 0"
                @click="clearAll()"
                type="button"
                class="text-[11px] text-gray-400 hover:text-red-500 transition-colors"
            >
                Clear list
            </button>
        </div>

        <!-- Notification List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
            <template x-for="item in filteredNotifications" :key="item.id">
                <div
                    @click="markAsRead(item)"
                    :class="item.is_read ? 'opacity-70 bg-transparent' : 'bg-red-50/40 dark:bg-[#E63946]/5 font-medium'"
                    class="p-3.5 flex items-start gap-3 hover:bg-gray-50 dark:hover:bg-white/10 transition-colors duration-150 cursor-pointer group relative"
                >
                    <!-- Contextual Icon Container -->
                    <div
                        class="p-2 rounded-xl transition-colors shrink-0 mt-0.5"
                        :class="item.is_read ? 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-white/60' : 'bg-[#E63946]/10 text-[#E63946]'"
                        x-html="getIconSvg(item.data.icon)"
                    ></div>

                    <!-- Notification Content -->
                    <div class="flex-1 min-w-0 pr-6">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate" x-text="item.data.title || 'Notification'"></p>
                            <span class="text-[10px] text-gray-400 dark:text-white/50 whitespace-nowrap" x-text="item.created_at"></span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-white/70 line-clamp-2 leading-relaxed" x-text="item.data.message"></p>
                    </div>

                    <!-- Unread Dot & Individual Delete Action -->
                    <div class="absolute right-3 top-3.5 flex items-center gap-1.5">
                        <span
                            x-show="!item.is_read"
                            x-cloak
                            class="w-2 h-2 rounded-full bg-[#E63946] shrink-0"
                        ></span>
                        <button
                            @click="deleteNotification(item, $event)"
                            title="Delete notification"
                            class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-500 rounded-lg transition-opacity"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div x-show="filteredNotifications.length === 0" class="py-10 px-4 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400 dark:text-white/50">
                    <x-icon name="bell" class="w-6 h-6" />
                </div>
                <p class="text-xs font-medium text-gray-600 dark:text-white/70">No notifications</p>
                <p class="text-[11px] text-gray-400 dark:text-white/50 mt-0.5">
                    <span x-show="activeTab === 'unread'">You have no unread notifications!</span>
                    <span x-show="activeTab === 'all'">You're all caught up!</span>
                </p>
            </div>
        </div>

        <!-- Dropdown Footer -->
        <div class="p-3 border-t border-gray-100 dark:border-white/10 text-center bg-gray-50/50 dark:bg-white/5">
            <a
                href="{{ route('notifications.page') }}"
                class="text-xs font-semibold text-[#E63946] hover:underline flex items-center justify-center gap-1.5"
            >
                View All Notifications
                <x-icon name="arrow-right" class="w-3.5 h-3.5" />
            </a>
        </div>
    </div>
</div>
