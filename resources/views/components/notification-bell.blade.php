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
        count: {{ $initialUnreadCount }},
        notifications: {{ \Illuminate\Support\Js::from($initialNotifications) }},
        loading: false,
        timer: null,

        async fetchNotifications() {
            try {
                const response = await fetch('{{ route('notifications.index') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.count = data.count || 0;
                    this.notifications = data.notifications || [];
                }
            } catch (e) {
                console.error('Failed to fetch notifications:', e);
            }
        },

        async markAsRead(notification) {
            if (!notification.is_read) {
                try {
                    await fetch(`/notifications/${notification.id}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    notification.is_read = true;
                    this.count = Math.max(0, this.count - 1);
                } catch (e) {
                    console.error('Failed to mark notification as read:', e);
                }
            }
            if (notification.data && notification.data.action_url) {
                window.location.href = notification.data.action_url;
            }
        },

        async markAllRead() {
            try {
                await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                this.count = 0;
                this.notifications.forEach(n => n.is_read = true);
            } catch (e) {
                console.error('Failed to mark all notifications as read:', e);
            }
        },

        init() {
            this.timer = setInterval(() => this.fetchNotifications(), 60000);
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

        <!-- Unread Badge -->
        <span
            x-show="count > 0"
            x-cloak
            x-text="count > 99 ? '99+' : count"
            class="absolute top-1 right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-[#E63946] rounded-full shadow-md"
        >{{ $initialUnreadCount > 99 ? '99+' : ($initialUnreadCount > 0 ? $initialUnreadCount : '') }}</span>
    </button>

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
        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between bg-gray-50/50 dark:bg-white/5">
            <div class="flex items-center gap-2">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Notifications</h3>
                <span
                    x-show="count > 0"
                    x-cloak
                    x-text="count"
                    class="px-2 py-0.5 text-xs font-semibold bg-[#E63946]/10 text-[#E63946] rounded-full"
                ></span>
            </div>
            <button
                x-show="count > 0"
                x-cloak
                @click="markAllRead()"
                type="button"
                class="text-xs text-[#E63946] hover:underline font-medium cursor-pointer"
            >
                Mark all as read
            </button>
        </div>

        <!-- Notification List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
            <template x-for="item in notifications" :key="item.id">
                <div
                    @click="markAsRead(item)"
                    :class="item.is_read ? 'opacity-70 bg-transparent' : 'bg-red-50/40 dark:bg-[#E63946]/5 font-medium'"
                    class="p-4 flex items-start gap-3.5 hover:bg-gray-50 dark:hover:bg-white/10 transition-colors duration-150 cursor-pointer group"
                >
                    <!-- Notification Icon Container -->
                    <div class="p-2 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white/90 group-hover:bg-[#E63946]/10 group-hover:text-[#E63946] transition-colors shrink-0 mt-0.5">
                        <x-icon name="bell" class="w-4 h-4" />
                    </div>

                    <!-- Notification Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <p class="text-xs font-semibold text-gray-900 dark:text-white truncate" x-text="item.data.title || 'Notification'"></p>
                            <span class="text-[10px] text-gray-600 dark:text-white/60 whitespace-nowrap" x-text="item.created_at"></span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-white/70 line-clamp-2 leading-relaxed" x-text="item.data.message"></p>
                    </div>

                    <!-- Unread Indicator Dot -->
                    <span
                        x-show="!item.is_read"
                        x-cloak
                        class="w-2 h-2 rounded-full bg-[#E63946] shrink-0 mt-1.5"
                    ></span>
                </div>
            </template>

            <!-- Empty State -->
            <div x-show="notifications.length === 0" class="py-10 px-4 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-600 dark:text-white/60">
                    <x-icon name="bell" class="w-6 h-6" />
                </div>
                <p class="text-xs font-medium text-gray-600 dark:text-white/70">No notifications</p>
                <p class="text-[11px] text-gray-600 dark:text-white/60 mt-1">You're all caught up!</p>
            </div>
        </div>
    </div>
</div>
