@extends('layouts.app')

@section('title', 'Event Logs')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Event Logs</h1>
        <p class="mt-2 text-gray-600">Real-time detection and alert events</p>
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <select name="event_type" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Event Types</option>
                <option value="detection" {{ request('event_type') === 'detection' ? 'selected' : '' }}>Detection</option>
                <option value="alert" {{ request('event_type') === 'alert' ? 'selected' : '' }}>Alert</option>
                <option value="motion" {{ request('event_type') === 'motion' ? 'selected' : '' }}>Motion</option>
                <option value="manual" {{ request('event_type') === 'manual' ? 'selected' : '' }}>Manual</option>
            </select>
            <select name="branch_id" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From Date" class="px-4 py-2 border border-gray-300 rounded-lg">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
        </form>
    </x-card>

    <!-- Events Table -->
    <x-card :padding="false">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Re-ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Notifications</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($events as $event)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded 
                                @if($event->event_type === 'detection') bg-green-100 text-green-800
                                @elseif($event->event_type === 'alert') bg-red-100 text-red-800
                                @elseif($event->event_type === 'motion') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($event->event_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $event->branch->branch_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $event->device->device_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ $event->re_id ? Str::limit($event->re_id, 20) : 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::parse($event->event_timestamp)->format('M d, H:i:s') }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($event->notification_sent)
                                <svg class="w-5 h-5 text-green-500 inline" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-gray-300 inline" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                                </svg>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <a href="{{ route('event-logs.show', $event) }}" class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">No events found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($events->hasPages())
            <div class="px-6 py-4 border-t">{{ $events->links() }}</div>
        @endif
    </x-card>
</div>
@endsection


