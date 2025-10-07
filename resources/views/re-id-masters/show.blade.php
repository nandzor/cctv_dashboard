@extends('layouts.app')

@section('title', 'Person Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $person->person_name ?: 'Unknown Person' }}</h1>
        <p class="mt-2 text-gray-600 font-mono">Re-ID: {{ $person->re_id }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card title="Person Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Re-ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $person->re_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Person Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $person->person_name ?: 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Detection Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($person->detection_date)->format('l, F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Detection Time</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($person->detection_time)->format('H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">First Detected</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $person->first_detected_at ? \Carbon\Carbon::parse($person->first_detected_at)->format('H:i:s') : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Detected</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $person->last_detected_at ? \Carbon\Carbon::parse($person->last_detected_at)->format('H:i:s') : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $person->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($person->status) }}
                            </span>
                        </dd>
                    </div>
                    @if($person->appearance_features)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Appearance Features</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <pre class="bg-gray-100 p-3 rounded text-xs">{{ json_encode($person->appearance_features, JSON_PRETTY_PRINT) }}</pre>
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-card>
        </div>

        <div>
            <x-card title="Detection Statistics">
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-4 border-b">
                        <span class="text-gray-600">Branches Detected</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $person->total_detection_branch_count }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-4 border-b">
                        <span class="text-gray-600">Total Actual Count</span>
                        <span class="text-xl font-semibold text-purple-600">{{ $person->total_actual_count }}</span>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Detection History -->
    <div class="mt-8">
        <x-card title="Detection History ({{ $date }})" :padding="false">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($person->reIdBranchDetections as $detection)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::parse($detection->detection_timestamp)->format('H:i:s') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $detection->branch->branch_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $detection->device->device_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-center font-semibold">{{ $detection->detected_count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($detection->detection_data)
                                    <button @click="showJson{{ $detection->id }} = !showJson{{ $detection->id }}" class="text-blue-600 hover:text-blue-800">View JSON</button>
                                    <div x-data="{ showJson{{ $detection->id }}: false }" x-show="showJson{{ $detection->id }}" x-cloak class="mt-2">
                                        <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto">{{ json_encode($detection->detection_data, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">No detections found for this date</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>

    <!-- All Detection Dates -->
    @if($hasMultipleDetections)
        <div class="mt-8">
            <x-card title="All Detection Dates">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($allDetections as $det)
                        <a href="{{ route('re-id-masters.show', ['reId' => $det->re_id, 'date' => $det->detection_date]) }}" 
                           class="p-4 border-2 rounded-lg text-center {{ $det->detection_date === $date ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">
                            <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($det->detection_date)->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $det->total_actual_count }} detections</p>
                        </a>
                    @endforeach
                </div>
            </x-card>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('re-id-masters.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Person Tracking</a>
    </div>
</div>
@endsection


