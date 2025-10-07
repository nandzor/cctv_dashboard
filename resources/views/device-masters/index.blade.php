@extends('layouts.app')

@section('title', 'Device Masters')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Device Masters</h1>
            <p class="mt-2 text-gray-600">Manage all CCTV devices and sensors</p>
        </div>
        <a href="{{ route('device-masters.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Device
        </a>
    </div>

    <x-card class="mb-6">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search devices..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
        </form>
    </x-card>

    <x-card :padding="false">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($deviceMasters->items() as $device)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $device->device_id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $device->device_name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded 
                                @if($device->device_type === 'camera') bg-blue-100 text-blue-800
                                @elseif($device->device_type === 'node_ai') bg-purple-100 text-purple-800
                                @elseif($device->device_type === 'mikrotik') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $device->device_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $device->companyBranch->branch_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $device->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($device->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                            <a href="{{ route('device-masters.show', $device) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            <a href="{{ route('device-masters.edit', $device) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                            <button @click="confirmDelete({{ $device->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">No devices found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($deviceMasters->hasPages())
            <div class="px-6 py-4 border-t">{{ $deviceMasters->links() }}</div>
        @endif
    </x-card>
</div>

<x-confirm-modal id="confirm-delete" title="Delete Device" message="Delete this device?" />
<script>
    let pendingDeleteId = null;
    function confirmDelete(id) {
        pendingDeleteId = id;
        window.dispatchEvent(new CustomEvent('open-modal-confirm-delete'));
    }
    window.addEventListener('confirm-confirm-delete', function() {
        if (pendingDeleteId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/device-masters/${pendingDeleteId}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endsection


