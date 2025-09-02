@extends('layouts.supervisor')

@section('page-title', 'Meeting Management')
@section('page-description', 'Create meetings and record attendance for your groups')

@section('content')
<!-- Success Message -->
@if (session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

<div class="space-y-8">
    <!-- Create Meeting Card -->
    <div class="bg-white shadow-xl rounded-xl border border-gray-100">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-white">Create New Meeting</h3>
            </div>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('supervisor.meetings.store') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Group</label>
                            <div class="relative">
                                <select name="group_id" id="group_id" class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-lg shadow-sm" required>
                                    <option value="">Choose a group...</option>
                                    @foreach($groups as $g)
                                        <option value="{{ $g->id }}">{{ $g->name }} (Batch {{ $g->batch_number }})</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            @error('group_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Meeting Date</label>
                            <input type="date" name="meeting_date" class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            @error('meeting_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Discussed Topics</label>
                            <textarea name="discussed_topics" rows="3" class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Enter topics discussed in the meeting..."></textarea>
                            @error('discussed_topics')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Outcomes of Discussion</label>
                            <textarea name="outcomes" rows="3" class="block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="Enter meeting outcomes and decisions..."></textarea>
                            @error('outcomes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Attendance Section -->
                <div id="attendance-container" class="hidden">
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Mark Attendance
                        </h4>
                        <div id="attendance-list" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
                        @error('attendance')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2v-7H3v7a2 2 0 002 2z"/>
                        </svg>
                        Save Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Meetings List -->
    <div class="bg-white shadow-xl rounded-xl border border-gray-100">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-white">Meeting History</h3>
                </div>
                <div class="text-sm text-gray-300">
                    {{ $meetings->total() }} total meetings
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Filters -->
            <div class="mb-6 bg-gray-50 rounded-lg p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Filter by Group</label>
                        <select name="group_id" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Groups</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" @selected(request('group_id')==$g->id)>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-700 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                            </svg>
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Group PDF Downloads -->
            @if($groups->isNotEmpty())
            <div class="mb-6 bg-blue-50 rounded-lg p-4 border border-blue-200">
                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                    <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Meeting Reports by Group
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($groups as $group)
                        <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $group->name }}</div>
                                <div class="text-xs text-gray-500">Batch {{ $group->batch_number }} • {{ $group->students->count() }} students</div>
                            </div>
                            <a href="{{ route('supervisor.meetings.group.pdf', $group) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 transition-colors">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                PDF
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($meetings->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2v-7H3v7a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No meetings found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first meeting.</p>
                </div>
            @else
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Group</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topics</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outcomes</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($meetings as $m)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2v-7H3v7a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ \Illuminate\Support\Carbon::parse($m->meeting_date)->format('M j, Y') }}
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $m->group->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @php
                                                    $attendanceRate = $m->total_count > 0 ? ($m->present_count / $m->total_count) * 100 : 0;
                                                @endphp
                                                @if($attendanceRate >= 80)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ $m->present_count }}/{{ $m->total_count }}
                                                    </span>
                                                @elseif($attendanceRate >= 60)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        {{ $m->present_count }}/{{ $m->total_count }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        {{ $m->present_count }}/{{ $m->total_count }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="ml-2 text-xs text-gray-500">
                                                {{ number_format($attendanceRate, 0) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">
                                            {{ $m->discussed_topics ?: 'No topics recorded' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">
                                            {{ $m->outcomes ?: 'No outcomes recorded' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('supervisor.meetings.show', $m) }}" 
                                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                View
                                            </a>
                                            <a href="{{ route('supervisor.meetings.edit', $m) }}" 
                                               class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-6">
                    {{ $meetings->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAttendance(index, isPresent) {
  const hiddenInput = document.getElementById(`present_${index}`);
  const presentBtn = document.getElementById(`present_btn_${index}`);
  const absentBtn = document.getElementById(`absent_btn_${index}`);
  const presentIcon = presentBtn.querySelector('svg');
  const absentIcon = absentBtn.querySelector('svg');
  
  if (isPresent) {
    // Mark as present
    hiddenInput.value = '1';
    presentBtn.classList.add('bg-green-500');
    presentBtn.classList.remove('hover:bg-green-50');
    presentIcon.classList.remove('hidden');
    presentIcon.classList.add('text-white');
    presentIcon.classList.remove('text-green-500');
    
    absentBtn.classList.remove('bg-red-500');
    absentBtn.classList.add('hover:bg-red-50');
    absentIcon.classList.add('hidden');
  } else {
    // Mark as absent
    hiddenInput.value = '0';
    absentBtn.classList.add('bg-red-500');
    absentBtn.classList.remove('hover:bg-red-50');
    absentIcon.classList.remove('hidden');
    
    presentBtn.classList.remove('bg-green-500');
    presentBtn.classList.add('hover:bg-green-50');
    presentIcon.classList.add('hidden');
    presentIcon.classList.add('text-green-500');
    presentIcon.classList.remove('text-white');
  }
}

(function(){
  const groupSelect = document.getElementById('group_id');
  const container = document.getElementById('attendance-container');
  const list = document.getElementById('attendance-list');
  if (!groupSelect) return;
  groupSelect.addEventListener('change', async function(){
    const groupId = this.value; list.innerHTML='';
    if (!groupId) { container.classList.add('hidden'); return; }
    try {
      const url = new URL("{{ route('supervisor.meetings.students') }}", window.location.origin);
      url.searchParams.set('group_id', groupId);
      const res = await fetch(url.toString());
      if (!res.ok) throw new Error('Failed');
      const students = await res.json();
      students.forEach((s, i) => {
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between border rounded p-2';
        row.innerHTML = `
          <div>
            <div class="font-medium">${s.name}</div>
            <div class="text-sm text-gray-500">ID: ${s.student_id || s.id}</div>
          </div>
          <div class="flex items-center space-x-2">
            <input type="hidden" name="attendance[${i}][group_student_id]" value="${s.id}">
            <input type="hidden" name="attendance[${i}][present]" value="0" id="present_${i}">
            <button type="button" onclick="toggleAttendance(${i}, true)" 
                    class="attendance-btn present-btn w-8 h-8 rounded-full border-2 border-green-500 flex items-center justify-center hover:bg-green-50 transition-colors" 
                    id="present_btn_${i}">
              <svg class="w-5 h-5 text-green-500 hidden" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </button>
            <button type="button" onclick="toggleAttendance(${i}, false)" 
                    class="attendance-btn absent-btn w-8 h-8 rounded-full border-2 border-red-500 flex items-center justify-center hover:bg-red-50 transition-colors bg-red-500" 
                    id="absent_btn_${i}">
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
              </svg>
            </button>
          </div>`;
        list.appendChild(row);
      });
      container.classList.remove('hidden');
    } catch(e){ container.classList.add('hidden'); }
  });
})();
</script>
@endpush
