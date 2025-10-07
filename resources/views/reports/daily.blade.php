@extends('layouts.app')

@section('title', 'Daily Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Daily Reports</h1>
        <p class="mt-2 text-gray-600">View daily activity reports</p>
    </div>

    <x-card class="mb-6">
        <form method="GET" class="flex gap-4">
            <input type="date" name="date" value="{{ $date }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
            <select name="branch_id" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
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
                        <td class="px-6 py-4 text-sm text-center font-semibold text-purple-600">{{ number_format($report->total_detections) }}</td>
                        <td class="px-6 py-4 text-sm text-center font-semibold text-orange-600">{{ number_format($report->total_events) }}</td>
                        <td class="px-6 py-4 text-sm text-center font-semibold text-green-600">{{ $report->unique_person_count }}</td>
                        <td class="px-6 py-4 text-right">
                            <button @click="showDetails{{ $report->id }} = !showDetails{{ $report->id }}" class="text-blue-600 hover:text-blue-900 text-sm">
                                View JSON
                            </button>
                        </td>
                    </tr>
                    @if($report->report_data)
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


