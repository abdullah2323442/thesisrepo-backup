@extends('layouts.supervisor')

@section('page-title', 'My Groups')
@section('page-description', 'View and manage your assigned groups and students')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Assigned Groups</h3>
    </div>

    <div class="p-6">
        @if($groups->isEmpty())
            <p class="text-gray-500">No groups assigned.</p>
        @else
            <div class="space-y-6">
                @foreach($groups as $group)
                    <div class="border rounded-lg">
                        <div class="p-4 bg-gray-50 flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $group->name }}</h4>
                                <p class="text-sm text-gray-600">Batch {{ $group->batch_number }} • Max Students: {{ $group->max_students }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800">Students: {{ $group->students->count() }}</span>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex items-center justify-between mb-4">
                                <p class="text-sm text-gray-700">Area of Interest: <span class="font-medium">{{ optional($group->matchedAreaOfInterest)->name ?? 'Not set' }}</span></p>
                                
                                @if($supervisor && $group->supervisor_id === $supervisor->id && $group->hasCoSupervisor())
                                    <!-- Co-supervisor Meeting Permission Toggle -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm text-gray-600">Co-supervisor can manage meetings:</span>
                                        <button 
                                            onclick="toggleCoSupervisorMeetings({{ $group->id }})"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $group->co_supervisor_can_manage_meetings ? 'bg-blue-600' : 'bg-gray-200' }}"
                                            id="toggle-{{ $group->id }}"
                                            data-current-state="{{ $group->co_supervisor_can_manage_meetings ? 'true' : 'false' }}"
                                        >
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $group->co_supervisor_can_manage_meetings ? 'translate-x-6' : 'translate-x-1' }}" id="toggle-dot-{{ $group->id }}"></span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            
                            @if($group->students->isEmpty())
                                <p class="text-gray-500">No students in this group.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-gray-600">
                                                <th class="py-2 pr-4">#</th>
                                                <th class="py-2 pr-4">Student Name</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-800">
                                            @foreach($group->students as $index => $student)
                                                <tr class="border-t">
                                                    <td class="py-2 pr-4">{{ $index + 1 }}</td>
                                                    <td class="py-2 pr-4">{{ $student->student_name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $groups->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleCoSupervisorMeetings(groupId) {
    const toggle = document.getElementById(`toggle-${groupId}`);
    const dot = document.getElementById(`toggle-dot-${groupId}`);
    
    // Get current state from data attribute
    const currentState = toggle.getAttribute('data-current-state') === 'true';
    const newState = !currentState;
    
    // Disable button during request
    toggle.disabled = true;
    toggle.style.opacity = '0.6';
    
    fetch('{{ route("supervisor.groups.toggle-co-supervisor-meetings") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            group_id: groupId,
            allow_meetings: newState
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update data attribute with new state
            toggle.setAttribute('data-current-state', data.can_manage_meetings ? 'true' : 'false');
            
            // Update toggle appearance
            if (data.can_manage_meetings) {
                toggle.classList.remove('bg-gray-200');
                toggle.classList.add('bg-blue-600');
                dot.classList.remove('translate-x-1');
                dot.classList.add('translate-x-6');
            } else {
                toggle.classList.remove('bg-blue-600');
                toggle.classList.add('bg-gray-200');
                dot.classList.remove('translate-x-6');
                dot.classList.add('translate-x-1');
            }
            
            // Show success message
            showNotification(data.message, 'success');
        } else {
            showNotification(data.error || 'Failed to update permission', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating permission', 'error');
    })
    .finally(() => {
        // Re-enable button
        toggle.disabled = false;
        toggle.style.opacity = '1';
    });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endpush
