@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
  <div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
      <p class="mt-2 text-gray-600">Welcome back! Here's what's happening with your CCTV system today.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <x-stat-card title="Total Groups" :value="$totalGroups" icon="building" color="blue" />

      <x-stat-card title="Total Branches" :value="$totalBranches" icon="building" color="green" />

      <x-stat-card title="Total Devices" :value="$totalDevices" icon="camera" color="purple" />

      <x-stat-card title="Today's Detections" :value="$reIdStats['total_records']" icon="eye" color="orange" />
    </div>

    <!-- Re-ID Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <x-card title="Person Detection Stats (Today)">
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Records:</span>
            <span class="text-2xl font-bold text-gray-900">{{ $reIdStats['total_records'] }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Active:</span>
            <span class="text-xl font-semibold text-green-600">{{ $reIdStats['active_records'] }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Inactive:</span>
            <span class="text-xl font-semibold text-red-600">{{ $reIdStats['inactive_records'] }}</span>
          </div>
          <div class="flex justify-between items-center pt-4 border-t border-gray-200">
            <span class="text-gray-600">Unique Persons:</span>
            <span class="text-xl font-semibold text-blue-600">{{ $reIdStats['unique_persons'] }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Total Detections:</span>
            <span class="text-xl font-semibold text-purple-600">{{ $reIdStats['total_detections'] }}</span>
          </div>
        </div>
      </x-card>

      <x-card title="Detection Trend (Last 7 Days)" class="lg:col-span-2">
        <div class="h-64 flex items-end justify-between space-x-2">
          @php
            $maxCount = $maxDetectionCount ?? 1;
          @endphp
          @forelse($detectionTrend as $trend)
            <div class="flex-1 flex flex-col items-center">
              <div class="w-full bg-blue-500 rounded-t hover:bg-blue-600 transition-colors cursor-pointer"
                style="height: {{ ($trend->count / $maxCount) * 100 }}%" title="{{ $trend->count }} detections">
              </div>
              <span class="text-xs text-gray-600 mt-2">{{ \Carbon\Carbon::parse($trend->date)->format('M d') }}</span>
              <span class="text-sm font-semibold text-gray-900">{{ $trend->count }}</span>
            </div>
          @empty
            <div class="w-full h-full flex items-center justify-center text-gray-400">
              No detection data available
            </div>
          @endforelse
        </div>
      </x-card>
    </div>

    <!-- Recent Detections & Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Detections -->
      <x-card title="Recent Detections" :padding="false">
        <div class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Re-ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($recentDetections as $detection)
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ Str::limit($detection->reIdMaster->re_id ?? 'N/A', 20) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $detection->branch->branch_name ?? 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $detection->device->device_name ?? 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $detection->detection_timestamp->diffForHumans() }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                    No recent detections
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($hasRecentDetections)
          <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            <a href="{{ route('re-id-masters.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
              View all detections →
            </a>
          </div>
        @endif
      </x-card>

      <!-- Recent Events -->
      <x-card title="Recent Events" :padding="false">
        <div class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($recentEvents as $event)
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if ($event->event_type === 'detection') bg-green-100 text-green-800
                                        @elseif($event->event_type === 'alert') bg-red-100 text-red-800
                                        @elseif($event->event_type === 'motion') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                      {{ ucfirst($event->event_type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $event->branch->branch_name ?? 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $event->device->device_name ?? 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $event->event_timestamp->diffForHumans() }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                    No recent events
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($hasRecentEvents)
          <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
              View all events →
            </a>
          </div>
        @endif
      </x-card>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
      <x-card title="Quick Actions">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <a href="{{ route('company-groups.create') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Add Group</span>
          </a>

          <a href="{{ route('company-branches.create') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Add Branch</span>
          </a>

          <a href="{{ route('device-masters.create') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Add Device</span>
          </a>

          <a href="{{ route('cctv-layouts.create') }}"
            class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-colors">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="text-sm font-medium text-gray-700">Add Layout</span>
          </a>
        </div>
      </x-card>
    </div>
  </div>
@endsection
