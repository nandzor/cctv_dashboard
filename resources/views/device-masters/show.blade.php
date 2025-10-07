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
            <x-button variant="warning" :href="route('device-masters.edit', $deviceMaster)">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Edit
            </x-button>
            <x-button variant="danger" @click="confirmDelete({{ $deviceMaster->id }})">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                Delete
            </x-button>
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
                            <x-badge :variant="$deviceMaster->device_type === 'camera' ? 'primary' : ($deviceMaster->device_type === 'node_ai' ? 'purple' : ($deviceMaster->device_type === 'mikrotik' ? 'success' : 'gray'))">
                                {{ ucfirst(str_replace('_', ' ', $deviceMaster->device_type)) }}
                            </x-badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Branch</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('company-branches.show', $deviceMaster->branch) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $deviceMaster->branch->branch_name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <x-badge :variant="$deviceMaster->status === 'active' ? 'success' : 'danger'">
                                {{ ucfirst($deviceMaster->status) }}
                            </x-badge>
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
        <x-button variant="secondary" :href="route('device-masters.index')">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Back to Devices
        </x-button>
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


