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
      <form method="GET" class="flex gap-4">
        <input type="month" name="month" value="{{ $month }}"
          class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
        <select name="branch_id" class="px-4 py-2 border border-gray-300 rounded-lg">
          <option value="">All Branches</option>
          @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
              {{ $branch->branch_name }}
            </option>
          @endforeach
        </select>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          Generate Report
        </button>
        <button type="button" onclick="exportToCSV()"
          class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
          Export CSV
        </button>
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
            @php
              $groupedReports = $reports->groupBy('branch_id');
              $dailyDetections = [];
            @endphp

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
                  {{ $reports->unique('branch_id')->sum(function ($r) {return $r->total_devices;}) }}
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
                  {{ number_format($monthlyStats['total_detections'] / max($reports->count(), 1), 1) }}
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
            @php
              $branchStats = $reports
                  ->groupBy('branch_id')
                  ->map(function ($items) {
                      return [
                          'branch' => $items->first()->branch,
                          'total_detections' => $items->sum('total_detections'),
                          'total_events' => $items->sum('total_events'),
                          'unique_persons' => $items->max('unique_person_count'),
                          'avg_per_day' => $items->avg('total_detections'),
                      ];
                  })
                  ->sortByDesc('total_detections');

              $maxBranchDetections = $branchStats->max('total_detections') ?: 1;
            @endphp

            @foreach ($branchStats as $branchId => $stat)
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
    <div class="mt-6 flex justify-between items-center">
      <a href="{{ route('reports.dashboard') }}" class="text-blue-600 hover:text-blue-800">
        ← Back to Reports Dashboard
      </a>
      <div class="flex space-x-3">
        <a href="{{ route('reports.daily', ['date' => now()->toDateString()]) }}"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
          View Today's Report
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
          Print Report
        </button>
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
