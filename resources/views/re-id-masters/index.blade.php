@extends('layouts.app')

@section('title', 'Person Tracking (Re-ID)')
@section('page-title', 'Person Tracking (Re-ID) Management')

@section('content')
  <!-- Statistics Cards -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <x-stat-card title="Total Records" :value="$statistics['total_records']" icon="users" color="blue" />
    <x-stat-card title="Active Tracking" :value="$statistics['active_records']" icon="eye" color="green" />
    <x-stat-card title="Unique Persons" :value="$statistics['unique_persons']" icon="users" color="purple" />
    <x-stat-card title="Total Detections" :value="$statistics['total_detections']" icon="chart-bar" color="orange" />
  </div>

  <x-card>
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('re-id-masters.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search by Re-ID or name..." class="rounded-r-none border-r-0" />
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
            <x-per-page-selector :options="$perPageOptions ?? [10, 25, 50, 100]" :current="$perPage ?? 10" :url="route('re-id-masters.index')" />
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-select name="status" :value="request('status')">
          <option value="">All Status</option>
          <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
          <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
    <x-table :headers="['Re-ID', 'Person Name', 'Detection Date', 'Branches', 'Detections', 'Status', 'Actions']">
      @forelse($persons as $person)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-md">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-mono text-gray-900">{{ Str::limit($person->re_id, 30) }}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $person->person_name ?: 'Unknown' }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ \Carbon\Carbon::parse($person->detection_date)->format('M d, Y') }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <x-badge variant="primary">
              {{ $person->total_detection_branch_count }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-center">
            <x-badge variant="purple">
              {{ $person->total_actual_count }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <x-badge :variant="$person->status === 'active' ? 'success' : 'danger'">
              {{ ucfirst($person->status) }}
            </x-badge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('re-id-masters.show', ['reId' => $person->re_id, 'date' => $person->detection_date])">
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <p class="text-gray-500 text-lg font-medium">No persons detected yet</p>
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
          <span class="font-medium">{{ $persons->firstItem() ?? 0 }}</span>
          to
          <span class="font-medium">{{ $persons->lastItem() ?? 0 }}</span>
          of
          <span class="font-medium">{{ $persons->total() }}</span>
          results
          @if (request()->has('search'))
            for "<span class="font-medium text-blue-600">{{ request()->get('search') }}</span>"
          @endif
        </div>

        <!-- Pagination Controls -->
        @if ($persons->hasPages())
          <x-pagination :paginator="$persons" />
        @endif
      </div>
    </div>
  </x-card>
@endsection


