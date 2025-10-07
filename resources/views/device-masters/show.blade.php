@extends('layouts.app')

@section('title', 'Device Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $deviceMaster->device_name }}</h1>
            <p class="mt-2 text-gray-600">{{ $deviceMaster->device_id }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('device-masters.edit', $deviceMaster) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">Edit</a>
            <button @click="confirmDelete({{ $deviceMaster->id }})" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card title="Device Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Device ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $deviceMaster->device_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Device Type</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded 
                                @if($deviceMaster->device_type === 'camera') bg-blue-100 text-blue-800
                                @elseif($deviceMaster->device_type === 'node_ai') bg-purple-100 text-purple-800
                                @elseif($deviceMaster->device_type === 'mikrotik') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $deviceMaster->device_type)) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Branch</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('company-branches.show', $deviceMaster->companyBranch) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $deviceMaster->companyBranch->branch_name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $deviceMaster->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($deviceMaster->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">URL / IP Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-100 p-2 rounded">{{ $deviceMaster->url ?: 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Username</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $deviceMaster->username ?: 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Password</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($deviceMaster->password)
                                <span class="text-gray-400">●●●●●●●●</span>
                                <span class="text-xs text-gray-500">(Encrypted)</span>
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $deviceMaster->notes ?: 'N/A' }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>

        <div>
            <x-card title="Device Stats">
                <div class="space-y-4">
                    <div class="text-center py-4">
                        <p class="text-sm text-gray-500">Stats will be available after first detection</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('device-masters.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Devices</a>
    </div>
</div>

<x-confirm-modal id="confirm-delete" title="Delete Device" message="Delete this device?" />
<script>
    function confirmDelete(id) {
        window.dispatchEvent(new CustomEvent('open-modal-confirm-delete'));
    }
    window.addEventListener('confirm-confirm-delete', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/device-masters/{{ $deviceMaster->id }}`;
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
    });
</script>
@endsection


