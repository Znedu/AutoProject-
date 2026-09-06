@extends('layouts.dashboard')

@section('title', 'Notifications | AutoProject+')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-fade-in">
    {{-- Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-[#121212] border border-gray-200 dark:border-white/10 p-6 rounded-2xl shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-[#E63946]/10 text-[#E63946]">
                    <x-icon name="bell" class="w-6 h-6" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications Center</h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-white/60">
                        Stay updated on your bookings, job progress, billing, and support requests.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                    @csrf
                    <x-button type="submit" variant="secondary" size="sm">
                        <x-icon name="check-circle" class="w-4 h-4 mr-1.5" />
                        Mark All Read
                    </x-button>
                </form>
            @endif

            @if ($totalCount > 0)
                <form action="{{ route('notifications.clear-all') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all notifications?');" class="inline">
                    @csrf
                    <x-button type="submit" variant="danger" size="sm">
                        <x-icon name="trash" class="w-4 h-4 mr-1.5" />
                        Clear All
                    </x-button>
                </form>
            @endif
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-[#121212] border border-gray-200 dark:border-white/10 p-4 rounded-xl">
        {{-- Tabs --}}
        <div class="flex items-center gap-1 bg-gray-100 dark:bg-white/5 p-1 rounded-xl">
            <a href="{{ route('notifications.page', ['filter' => 'all', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentFilter === 'all' ? 'bg-white dark:bg-[#1E1E1E] text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white' }}">
                All ({{ $totalCount }})
            </a>
            <a href="{{ route('notifications.page', ['filter' => 'unread', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentFilter === 'unread' ? 'bg-[#E63946] text-white shadow-sm' : 'text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white' }}">
                Unread ({{ $unreadCount }})
            </a>
            <a href="{{ route('notifications.page', ['filter' => 'read', 'search' => $search]) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentFilter === 'read' ? 'bg-white dark:bg-[#1E1E1E] text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-white/60 hover:text-gray-900 dark:hover:text-white' }}">
                Read
            </a>
        </div>

        {{-- Search Form --}}
        <form method="GET" action="{{ route('notifications.page') }}" class="flex items-center gap-2">
            <input type="hidden" name="filter" value="{{ $currentFilter }}">
            <div class="relative flex-1 sm:w-64">
                <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search notifications..."
                    class="w-full pl-9 pr-4 py-1.5 text-xs bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-[#E63946] focus:outline-none text-gray-900 dark:text-white"
                />
            </div>
            <x-button type="submit" variant="ghost" size="sm">Search</x-button>
        </form>
    </div>

    {{-- Notification List --}}
    <div class="bg-white dark:bg-[#121212] border border-gray-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-sm">
        @forelse ($notifications as $n)
            @php
                $data = $n->data;
                $iconName = $data['icon'] ?? 'bell';
                $title = $data['title'] ?? 'Notification';
                $message = $data['message'] ?? '';
                $actionUrl = $data['action_url'] ?? null;
                $isRead = !is_null($n->read_at);
            @endphp
            <div class="p-4 sm:p-5 flex items-start justify-between gap-4 border-b border-gray-100 dark:border-white/5 last:border-b-0 hover:bg-gray-50/70 dark:hover:bg-white/5 transition-colors duration-150 {{ $isRead ? 'opacity-75' : 'bg-red-50/30 dark:bg-[#E63946]/5' }}">
                <div class="flex items-start gap-4 flex-1 min-w-0">
                    {{-- Icon Container --}}
                    <div class="p-3 rounded-2xl shrink-0 mt-0.5 {{ $isRead ? 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-white/60' : 'bg-[#E63946]/10 text-[#E63946]' }}">
                        <x-icon name="{{ $iconName }}" class="w-5 h-5" />
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $title }}
                            </h3>
                            @if (! $isRead)
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-[#E63946] text-white rounded-full">
                                    New
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 dark:text-white/70 leading-relaxed mb-2">
                            {{ $message }}
                        </p>
                        <div class="flex items-center gap-4 text-[11px] text-gray-600 dark:text-white/60">
                            <span>{{ $n->created_at->diffForHumans() }}</span>
                            <span>•</span>
                            <span>{{ $n->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    @if ($actionUrl)
                        <a href="{{ $actionUrl }}">
                            <x-button variant="secondary" size="sm">
                                View
                                <x-icon name="arrow-right" class="w-3.5 h-3.5 ml-1" />
                            </x-button>
                        </a>
                    @endif

                    @if (! $isRead)
                        <form action="{{ route('notifications.read', $n->id) }}" method="POST">
                            @csrf
                            <button type="submit" title="Mark as read" class="p-2 text-gray-400 hover:text-green-600 dark:hover:text-green-400 hover:bg-gray-100 dark:hover:bg-white/10 rounded-xl transition-colors">
                                <x-icon name="check" class="w-4 h-4" />
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('notifications.destroy', $n->id) }}" method="POST" onsubmit="return confirm('Delete this notification?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Delete notification" class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-white/10 rounded-xl transition-colors">
                            <x-icon name="trash" class="w-4 h-4" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-600 dark:text-white/60">
                    <x-icon name="bell" class="w-8 h-8" />
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">No notifications found</h3>
                <p class="text-xs text-gray-600 dark:text-white/60 mt-1 max-w-sm mx-auto">
                    @if (! empty($search))
                        No notifications matched your search query "{{ $search }}".
                    @elseif ($currentFilter === 'unread')
                        Awesome! You have no unread notifications right now.
                    @else
                        You don't have any notifications saved yet.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($notifications->hasPages())
        <div class="pt-2">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
