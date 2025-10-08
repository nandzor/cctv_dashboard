@extends('layouts.app')

@section('title', 'Monthly Reports')

@section('content')
  <div class="max-w-7xl mx-auto">
    <!-- Page Header with Export -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Monthly Reports</h1>
        <p class="mt-2 text-gray-600">Report for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</p>
      </div>

      @if ($reports->count() > 0)
        <div class="mt-4 sm:mt-0">
          <x-dropdown align="right" width="48">
            <x-slot name="trigger">
              <button type="button"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export Report
                <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
                </svg>
              </button>
            </x-slot>

            <x-dropdown-link :href="route('reports.monthly.export', [
                'month' => $month,
                'branch_id' => $branchId,
                'format' => 'excel',
            ])" variant="success">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
              </svg>
              Export to Excel
            </x-dropdown-link>

            <x-dropdown-link :href="route('reports.monthly.export', [
                'month' => $month,
                'branch_id' => $branchId,
                'format' => 'pdf',
            ])" variant="danger">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
              </svg>
              Export to PDF
            </x-dropdown-link>
          </x-dropdown>
        </div>
      @endif
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
      <form method="GET">
        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
          <!-- Filter Fields -->
          <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input type="month" name="month" label="Select Month" :value="$month" />
            <x-company-branch-select name="branch_id" label="Select Branch" :value="$branchId"
              placeholder="All Branches" />
          </div>

          <!-- Action Button -->
          <x-button type="submit" variant="primary" size="md" class="w-full md:w-auto">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Apply Filters
          </x-button>
        </div>
      </form>
    </x-card>

    <!-- Monthly Statistics Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <x-stat-card title="Total Detections" :value="number_format($monthlyStats['total_detections'])" icon="eye" color="blue" />
      <x-stat-card title="Unique Persons" :value="number_format($monthlyStats['unique_persons'])" icon="users" color="purple" />
      <x-stat-card title="Total Events" :value="number_format($monthlyStats['total_events'])" icon="chart-bar" color="orange" />
    </div>

    <!-- Monthly Report Table -->
    <x-card title="Monthly Report for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}" :padding="false">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="monthly-report-table">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Devices</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Detections</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Events</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Unique Persons</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg/Day</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @forelse($reports as $report)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  {{ \Carbon\Carbon::parse($report->report_date)->format('M d, Y') }}
                  <span class="text-xs text-gray-500 block">
                    {{ \Carbon\Carbon::parse($report->report_date)->format('l') }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ $report->branch->branch_name ?? 'Overall' }}
                  @if ($report->branch)
                    <span class="text-xs text-gray-500 block">{{ $report->branch->city }}</span>
                  @endif
                </td>
                <td class="px-6 py-4 text-sm text-center font-semibold text-blue-600">
                  {{ $report->total_devices }}
                </td>
                <td class="px-6 py-4 text-sm text-center font-semibold text-purple-600">
                  {{ number_format($report->total_detections) }}
                </td>
                <td class="px-6 py-4 text-sm text-center font-semibold text-orange-600">
                  {{ number_format($report->total_events) }}
                </td>
                <td class="px-6 py-4 text-sm text-center font-semibold text-green-600">
                  {{ $report->unique_person_count }}
                </td>
                <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                  {{ number_format($report->total_detections / max($report->total_devices, 1), 1) }}
                </td>
              </tr>
              @php
                $date = \Carbon\Carbon::parse($report->report_date)->format('Y-m-d');
                if (!isset($dailyDetections[$date])) {
                    $dailyDetections[$date] = 0;
                }
                $dailyDetections[$date] += $report->total_detections;
              @endphp
            @empty
              <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                  No reports found for this month
                </td>
              </tr>
            @endforelse
          </tbody>

          @if ($reports->isNotEmpty())
            <tfoot class="bg-gray-100 font-bold">
              <tr>
                <td colspan="2" class="px-6 py-4 text-sm text-gray-900">Monthly Total</td>
                <td class="px-6 py-4 text-sm text-center text-blue-600">
                  {{ $totalDevices }}
                </td>
                <td class="px-6 py-4 text-sm text-center text-purple-600">
                  {{ number_format($monthlyStats['total_detections']) }}
                </td>
                <td class="px-6 py-4 text-sm text-center text-orange-600">
                  {{ number_format($monthlyStats['total_events']) }}
                </td>
                <td class="px-6 py-4 text-sm text-center text-green-600">
                  {{ $monthlyStats['unique_persons'] }}
                </td>
                <td class="px-6 py-4 text-sm text-right text-gray-900">
                  {{ number_format($avgDetectionsPerDay, 1) }}
                </td>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </x-card>

    <!-- Daily Trend Chart -->
    @if ($reports->isNotEmpty() && count($dailyDetections) > 0)
      <div class="mt-6">
        <x-card title="Daily Detection Trend">
          <div class="h-64 flex items-end justify-between space-x-1">
            @php
              ksort($dailyDetections);
              $maxDetection = max(array_values($dailyDetections)) ?: 1;
            @endphp
            @foreach ($dailyDetections as $date => $count)
              <div class="flex-1 flex flex-col items-center group">
                <div
                  class="w-full bg-gradient-to-t from-blue-500 to-blue-400 rounded-t hover:from-blue-600 hover:to-blue-500 transition-all cursor-pointer relative"
                  style="height: {{ ($count / $maxDetection) * 100 }}%"
                  title="{{ $count }} detections on {{ \Carbon\Carbon::parse($date)->format('M d') }}">
                  <span
                    class="absolute -top-6 left-1/2 transform -translate-x-1/2 text-xs font-semibold text-gray-700 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap bg-white px-2 py-1 rounded shadow">
                    {{ number_format($count) }}
                  </span>
                </div>
                @if (\Carbon\Carbon::parse($date)->day % 5 === 1 || \Carbon\Carbon::parse($date)->day === 1)
                  <span class="text-xs text-gray-600 mt-2 rotate-45 origin-left">
                    {{ \Carbon\Carbon::parse($date)->format('d') }}
                  </span>
                @endif
              </div>
            @endforeach
          </div>
          <div class="mt-4 text-center text-xs text-gray-500">
            Daily detections for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
          </div>
        </x-card>
      </div>
    @endif

    <!-- Branch Comparison (if viewing all branches) -->
    @if (!$branchId && $reports->isNotEmpty())
      <div class="mt-6">
        <x-card title="Branch Performance Comparison">
          <div class="space-y-4">
            @foreach ($branchStats->sortByDesc('total_detections') as $branchId => $stat)
              <div class="pb-4 border-b border-gray-100 last:border-b-0">
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <p class="font-semibold text-gray-900">{{ $stat['branch']->branch_name ?? 'Unknown' }}</p>
                    <p class="text-xs text-gray-500">{{ $stat['branch']->city ?? '' }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-xl font-bold text-blue-600">{{ number_format($stat['total_detections']) }}</p>
                    <p class="text-xs text-gray-500">Total Detections</p>
                  </div>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                  <div class="bg-blue-600 h-2 rounded-full transition-all"
                    style="width: {{ ($stat['total_detections'] / $maxBranchDetections) * 100 }}%">
                  </div>
                </div>

                <div class="grid grid-cols-3 gap-4 text-sm">
                  <div>
                    <span class="text-gray-500">Events:</span>
                    <span class="font-semibold text-orange-600">{{ number_format($stat['total_events']) }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Persons:</span>
                    <span class="font-semibold text-green-600">{{ number_format($stat['unique_persons']) }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Avg/Day:</span>
                    <span class="font-semibold text-purple-600">{{ number_format($stat['avg_per_day'], 1) }}</span>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </x-card>
      </div>
    @endif

    <!-- Quick Actions -->
    <div class="mt-6 flex flex-wrap justify-between items-center gap-4">
      <x-button variant="secondary" :href="route('reports.dashboard')">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Reports Dashboard
      </x-button>
      <x-button variant="secondary" :href="route('reports.daily', ['date' => now()->toDateString()])">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        View Today's Report
      </x-button>
    </div>
  </div>

@endsection
