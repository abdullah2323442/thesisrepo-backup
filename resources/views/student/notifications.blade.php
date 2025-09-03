@extends('layouts.student')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Notifications</h1>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('student.notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Mark All as Read
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($notifications->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Notifications</h3>
            <p class="text-gray-600">You don't have any notifications yet.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($notifications as $notification)
                <div class="bg-white rounded-lg shadow-md p-4 {{ $notification->read_at ? 'opacity-75' : 'border-l-4 border-blue-500' }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                @if($notification->data['type'] === 'report_assigned')
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                @elseif($notification->data['type'] === 'report_comment')
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                @elseif($notification->data['type'] === 'report_updated')
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                @endif
                                <h3 class="font-semibold text-gray-800">{{ $notification->data['title'] ?? 'Notification' }}</h3>
                                @if(!$notification->read_at)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">New</span>
                                @endif
                            </div>
                            
                            <p class="text-gray-700 mb-2">{{ $notification->data['message'] ?? '' }}</p>
                            
                            @if(isset($notification->data['comment_preview']))
                                <div class="bg-gray-50 p-2 rounded mb-2">
                                    <p class="text-sm text-gray-600 italic">"{{ $notification->data['comment_preview'] }}"</p>
                                </div>
                            @endif
                            
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                                @if(isset($notification->data['group_name']))
                                    <span>Group: {{ $notification->data['group_name'] }}</span>
                                @endif
                                @if(isset($notification->data['teacher_name']))
                                    <span>By: {{ $notification->data['teacher_name'] }}</span>
                                @elseif(isset($notification->data['created_by']))
                                    <span>By: {{ $notification->data['created_by'] }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex gap-2 ml-4">
                            @if(isset($notification->data['report_id']))
                                <a href="{{ route('student.reports.show', $notification->data['report_id']) }}" 
                                   class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors">
                                    View Report
                                </a>
                            @endif
                            
                            @if(!$notification->read_at)
                                <form action="{{ route('student.notifications.mark-read', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50 transition-colors">
                                        Mark as Read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection