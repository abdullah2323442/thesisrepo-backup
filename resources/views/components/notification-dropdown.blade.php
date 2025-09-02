@props(['user'])

<div x-data="notificationDropdown()" class="relative">
    <!-- Notification Bell Button -->
    <button @click="toggle" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        <!-- Notification Count Badge -->
        <span x-show="unreadCount > 0" 
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full notification-badge">
        </span>
    </button>

    <!-- Notification Dropdown Panel -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         @click.away="close"
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 notification-dropdown"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                <div class="flex items-center gap-2">
                    <span x-show="unreadCount > 0" class="text-sm text-gray-500">
                        <span x-text="unreadCount"></span> new
                    </span>
                    <button x-show="unreadCount > 0" 
                            @click="markAllAsRead"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        Mark all read
                    </button>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto notification-scroll">
            <div x-show="loading" class="p-4 text-center">
                <svg class="inline w-6 h-6 text-gray-400 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div x-show="!loading && notifications.length === 0" class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-gray-500 text-sm">No notifications yet</p>
            </div>

            <template x-for="notification in notifications" :key="notification.id">
                <div @click="handleNotificationClick(notification)"
                     :class="{'notification-unread bg-blue-50': !notification.read_at, 'hover:bg-gray-50': notification.read_at}"
                     class="px-4 py-3 border-b border-gray-100 cursor-pointer transition-all duration-200 hover:shadow-sm">
                    
                    <div class="flex items-start gap-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-1">
                            <div :class="getIconClass(notification.data.type)" class="w-8 h-8 rounded-full flex items-center justify-center">
                                <svg x-show="notification.data.type === 'report_assigned'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <svg x-show="notification.data.type === 'report_comment'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900" x-text="notification.data.title"></p>
                            <p class="text-sm text-gray-600 mt-1" x-text="notification.data.message"></p>
                            
                            <!-- Additional Info -->
                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                <span x-text="formatTime(notification.created_at)"></span>
                                <span x-show="notification.data.group_name" x-text="'• ' + notification.data.group_name"></span>
                            </div>
                        </div>

                        <!-- Unread Indicator -->
                        <div x-show="!notification.read_at" class="flex-shrink-0">
                            <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <a href="{{ route('student.notifications') }}" class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                View All Notifications
            </a>
        </div>
    </div>
</div>

<script>
function notificationDropdown() {
    return {
        open: false,
        loading: false,
        notifications: [],
        unreadCount: {{ $user->unreadNotifications->count() }},
        
        init() {
            this.loadNotifications();
            // Refresh notifications every 30 seconds
            setInterval(() => {
                if (!this.open) {
                    this.loadNotifications();
                }
            }, 30000);
        },
        
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.loadNotifications();
            }
        },
        
        close() {
            this.open = false;
        },
        
        async loadNotifications() {
            this.loading = true;
            try {
                const response = await fetch('/api/notifications', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.unread_count || 0;
                }
            } catch (error) {
                console.error('Failed to load notifications:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async markAllAsRead() {
            try {
                const response = await fetch('{{ route("student.notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    this.notifications = this.notifications.map(n => ({...n, read_at: new Date().toISOString()}));
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Failed to mark notifications as read:', error);
            }
        },
        
        async handleNotificationClick(notification) {
            // Mark as read if unread
            if (!notification.read_at) {
                try {
                    await fetch(`/student/notifications/${notification.id}/mark-read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });
                    
                    notification.read_at = new Date().toISOString();
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                } catch (error) {
                    console.error('Failed to mark notification as read:', error);
                }
            }
            
            // Navigate to relevant page
            if (notification.data.report_id) {
                window.location.href = `/student/reports/${notification.data.report_id}`;
            }
        },
        
        getIconClass(type) {
            const classes = {
                'report_assigned': 'bg-purple-500',
                'report_comment': 'bg-blue-500',
                'default': 'bg-gray-500'
            };
            return classes[type] || classes.default;
        },
        
        formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
            if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
            
            return date.toLocaleDateString();
        }
    }
}
</script>