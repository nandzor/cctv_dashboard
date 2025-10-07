@extends('layouts.app')

@section('title', 'Event Logs')
@section('page-title', 'Event Logs Management')

@section('content')
  <x-card>
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('event-logs.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search events..." class="rounded-r-none border-r-0" />
            @if (request()->has('per_page'))
              <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
            @endif
            <button type="submit"
              class="px-6 py-2 bg-gray-600 text-white rounded-r-lg hover:bg-gray-700 transition-colors">
              Search
            </button>
          </form>
        </div>

        <div class="flex items-center space-x-4">
          <!-- Per Page Selector -->
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Show:</span>
            <x-per-page-selector :options="$perPageOptions ?? [10, 25, 50, 100]" :current="$perPage ?? 10" :url="route('event-logs.index')" />
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-select name="event_type" :value="request('event_type')">
          <option value="">All Event Types</option>
          <option value="detection" {{ request('event_type') === 'detection' ? 'selected' : '' }}>Detection</option>
          <option value="alert" {{ request('event_type') === 'alert' ? 'selected' : '' }}>Alert</option>
          <option value="motion" {{ request('event_type') === 'motion' ? 'selected' : '' }}>Motion</option>
          <option value="manual" {{ request('event_type') === 'manual' ? 'selected' : '' }}>Manual</option>
        </x-select>
        
        <x-select name="branch_id" :value="request('branch_id')">
          <option value="">All Branches</option>
          @foreach($branches as $branch)
            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
          @endforeach
        </x-select>
        
        <x-input type="date" name="date_from" :value="request('date_from')" placeholder="From Date" />
        
        <x-button type="submit" variant="primary" size="sm">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
          </svg>
          Filter
        </x-button>
      </form>
    </div>

    <!-- Table -->
    <x-table :headers="['Event Type', 'Branch', 'Device', 'Re-ID', 'Time', 'Notifications', 'Actions']">
      @forelse($events as $event)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <x-badge :variant="$event->event_type === 'detection' ? 'success' : ($event->event_type === 'alert' ? 'danger' : ($event->event_type === 'motion' ? 'warning' : 'gray'))">
                  {{ ucfirst($event->event_type) }}
                </x-badge>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ $event->branch->branch_name ?? 'N/A' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ $event->device->device_name ?? 'N/A' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
            {{ $event->re_id ? Str::limit($event->re_id, 20) : 'N/A' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ \Carbon\Carbon::parse($event->event_timestamp)->format('M d, H:i:s') }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            @if($event->notification_sent)
              <x-badge variant="success">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                </svg>
                Sent
              </x-badge>
            @else
              <x-badge variant="gray">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                </svg>
                Pending
              </x-badge>
            @endif
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('event-logs.show', $event)">
                👁️ View Details
              </x-dropdown-link>
            </x-action-dropdown>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No events found</p>
              <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
            </div>
          </td>
        </tr>
      @endforelse
    </x-table>

    <!-- Pagination Info & Controls -->
    <div class="px-6 py-4 border-t border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-700">
          Showing
          <span class="font-medium">{{ $events->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $events->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $events->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($events->hasPages())
          <x-pagination :paginator="$events" />
        @endif
      </div>
    </div>
  </x-card>
@endsection


