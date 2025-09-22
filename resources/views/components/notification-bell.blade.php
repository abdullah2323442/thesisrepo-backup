@props(['user'])

@php
    $unreadCount = $user->unreadNotifications()->count();
    $notifications = $user->notifications()->latest()->take(10)->get();
@endphp

<div x-data="{ 
    open: false,
    unreadCount: {{ $unreadCount }},
    notifications: {{ $notifications->toJson() }}
}" class="relative">
    <!-- Notification Bell Button -->
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        <!-- Notification Count Badge -->
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full notification-badge">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notification Dropdown Panel -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         @click.away="open = false"
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 notification-dropdown"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                <div class="flex items-center gap-2">
                    @if($unreadCount > 0)
                        <span class="text-sm text-gray-500">{{ $unreadCount }} new</span>
                        <form action="{{ route('student.notifications.mark-all-read') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                Mark all read
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto notification-scroll">
            @if($notifications->isEmpty())
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">No notifications yet</p>
                </div>
            @else
                @foreach($notifications as $notification)
                    <a href="{{ isset($notification->data['report_id']) ? route('student.reports.show', $notification->data['report_id']) : '#' }}"
                       class="block px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors {{ !$notification->read_at ? 'notification-unread bg-blue-50' : '' }}">
                        
                        <div class="flex items-start gap-3">
                            <!-- Icon -->
                            <div class="flex-shrink-0 mt-1">
                                @if($notification->data['type'] === 'report_assigned')
                                    <div class="w-8 h-8 rounded-full bg-purple-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                @elseif($notification->data['type'] === 'report_comment')
                                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </div>
                                @elseif($notification->data['type'] === 'report_updated')
                                    <div class="w-8 h-8 rounded-full bg-orange-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </div>
                                @elseif($notification->data['type'] === 'report_annotation')
                                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                                
                                <!-- Feedback Preview for Annotation Notifications -->
                                @if(isset($notification->data['feedback_preview']) && $notification->data['type'] === 'report_annotation')
                                    <div class="bg-blue-50 border-l-2 border-blue-200 pl-2 py-1 mt-2 rounded-r">
                                        <p class="text-xs text-blue-700 font-medium">Supervisor Feedback:</p>
                                        <p class="text-xs text-blue-600 italic">"{{ $notification->data['feedback_preview'] }}"</p>
                                    </div>
                                @endif
                                
                                <!-- Additional Info -->
                                <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    @if(isset($notification->data['group_name']))
                                        <span>• {{ $notification->data['group_name'] }}</span>
                                    @endif
                                    @if(isset($notification->data['supervisor_name']))
                                        <span>• {{ $notification->data['supervisor_name'] }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Unread Indicator -->
                            @if(!$notification->read_at)
                                <div class="flex-shrink-0">
                                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            @endif
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <a href="{{ route('student.notifications') }}" class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                View All Notifications
            </a>
        </div>
    </div>
</div>