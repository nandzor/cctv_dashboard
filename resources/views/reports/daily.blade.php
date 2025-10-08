@extends('layouts.app')

@section('title', 'Daily Reports')

@section('content')
  <div class="max-w-7xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Daily Reports</h1>
      <p class="mt-2 text-gray-600">View daily activity reports</p>
    </div>

    <x-card class="mb-6">
      <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
          <x-input type="date" name="date" label="Select Date" :value="$date" />
        </div>
        <div class="flex-1 min-w-[200px]">
          <x-company-branch-select name="branch_id" label="Select Branch" :value="$branchId" placeholder="All Branches" />
        </div>
        <div class="flex items-end">
          <x-button type="submit" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filter
          </x-button>
        </div>
      </form>
    </x-card>

    <x-card title="Daily Report for {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}" :padding="false">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Devices</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Detections</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Events</th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Unique Persons</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Details</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @forelse($reports as $report)
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">
                {{ $report->branch->branch_name ?? 'Overall' }}
              </td>
              <td class="px-6 py-4 text-sm text-center font-semibold text-blue-600">{{ $report->total_devices }}</td>
              <td class="px-6 py-4 text-sm text-center font-semibold text-purple-600">
                {{ number_format($report->total_detections) }}</td>
              <td class="px-6 py-4 text-sm text-center font-semibold text-orange-600">
                {{ number_format($report->total_events) }}</td>
              <td class="px-6 py-4 text-sm text-center font-semibold text-green-600">{{ $report->unique_person_count }}
              </td>
              <td class="px-6 py-4 text-right">
                <x-button size="sm" variant="primary"
                  @click="showDetails{{ $report->id }} = !showDetails{{ $report->id }}">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                  View JSON
                </x-button>
              </td>
            </tr>
            @if ($report->report_data)
              <tr x-data="{ showDetails{{ $report->id }}: false }" x-show="showDetails{{ $report->id }}" x-cloak>
                <td colspan="6" class="px-6 py-4 bg-gray-50">
                  <pre class="text-xs bg-white p-4 rounded border overflow-x-auto">{{ json_encode($report->report_data, JSON_PRETTY_PRINT) }}</pre>
                </td>
              </tr>
            @endif
          @empty
            <tr>
              <td colspan="6" class="px-6 py-8 text-center text-gray-400">No reports found for this date</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </x-card>
  </div>
@endsection
