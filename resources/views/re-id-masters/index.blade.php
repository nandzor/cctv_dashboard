@extends('layouts.app')

@section('title', 'Person Tracking (Re-ID)')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Person Tracking (Re-ID)</h1>
        <p class="mt-2 text-gray-600">Track persons detected across all branches</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <x-stat-card title="Total Records" :value="$statistics['total_records']" icon="users" color="blue" />
        <x-stat-card title="Active Tracking" :value="$statistics['active_records']" icon="eye" color="green" />
        <x-stat-card title="Unique Persons" :value="$statistics['unique_persons']" icon="users" color="purple" />
        <x-stat-card title="Total Detections" :value="$statistics['total_detections']" icon="chart-bar" color="orange" />
    </div>

    <!-- Search & Filter -->
    <x-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Re-ID or name..." class="px-4 py-2 border border-gray-300 rounded-lg">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
        </form>
    </x-card>

    <!-- Persons Table -->
    <x-card :padding="false">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Re-ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Person Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detection Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branches</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detections</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($persons->items() as $person)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ Str::limit($person->re_id, 30) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $person->person_name ?: 'Unknown' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::parse($person->detection_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm text-center font-semibold text-blue-600">{{ $person->total_detection_branch_count }}</td>
                        <td class="px-6 py-4 text-sm text-center font-semibold text-purple-600">{{ $person->total_actual_count }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $person->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($person->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <a href="{{ route('re-id-masters.show', ['reId' => $person->re_id, 'date' => $person->detection_date]) }}" class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">No persons detected yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($persons->hasPages())
            <div class="px-6 py-4 border-t">{{ $persons->links() }}</div>
        @endif
    </x-card>
</div>
@endsection


