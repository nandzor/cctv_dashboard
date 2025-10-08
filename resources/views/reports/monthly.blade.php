@extends('layouts.app')

@section('title', 'Monthly Reports')

@section('content')
  <div class="max-w-7xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Monthly Reports</h1>
      <p class="mt-2 text-gray-600">View monthly aggregated activity reports</p>
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
      <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
          <x-input type="month" name="month" label="Select Month" :value="$month" />
        </div>
        <div class="flex-1 min-w-[200px]">
          <x-company-branch-select name="branch_id" label="Select Branch" :value="$branchId" placeholder="All Branches" />
        </div>
        <div class="flex items-end gap-2">
          <x-button type="submit" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Generate Report
          </x-button>
          <x-button type="button" variant="success" onclick="exportToCSV()">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export CSV
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
      <div class="flex space-x-3">
        <x-button variant="secondary" :href="route('reports.daily', ['date' => now()->toDateString()])">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          View Today's Report
        </x-button>
        <x-button variant="secondary" onclick="window.print()">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          Print Report
        </x-button>
      </div>
    </div>
  </div>

  <script>
    function exportToCSV() {
      const table = document.getElementById('monthly-report-table');
      let csv = [];

      // Headers
      const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
      csv.push(headers.join(','));

      // Rows
      const rows = table.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const cols = Array.from(row.querySelectorAll('td')).map(td => {
          return '"' + td.textContent.trim().replace(/"/g, '""') + '"';
        });
        if (cols.length > 0) {
          csv.push(cols.join(','));
        }
      });

      // Download
      const csvContent = csv.join('\n');
      const blob = new Blob([csvContent], {
        type: 'text/csv;charset=utf-8;'
      });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', 'monthly_report_{{ $month }}.csv');
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  </script>

  <style>
    @media print {

      .no-print,
      nav,
      button,
      a[href*="create"],
      a[href*="edit"] {
        display: none !important;
      }
    }
  </style>
@endsection
