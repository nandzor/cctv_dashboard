@extends('layouts.app')

@section('title', 'Branch Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $branch->branch_name }}</h1>
            <p class="mt-2 text-gray-600">{{ $branch->city_name }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('company-branches.edit', $branch) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">Edit</a>
            <button @click="confirmDelete({{ $branch->id }})" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card title="Branch Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Branch Code</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->branch_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Company Group</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->group->group_name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->phone ?: 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->email ?: 'N/A' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $branch->address ?: 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">GPS Coordinates</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($branch->latitude && $branch->longitude)
                                {{ $branch->latitude }}, {{ $branch->longitude }}
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($branch->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </x-card>
        </div>

        <div>
            <x-card title="Statistics">
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                        <span class="text-gray-600">Total Devices</span>
                        <span class="text-2xl font-bold text-blue-600">{{ $deviceCounts['total'] }}</span>
                      </div>
                      <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                        <span class="text-gray-600">Active Devices</span>
                        <span class="text-xl font-semibold text-green-600">{{ $deviceCounts['active'] }}</span>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <div class="mt-8">
        <x-card title="Devices" :padding="false">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($branch->deviceMasters as $device)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $device->device_id }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $device->device_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $device->device_type }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $device->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($device->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('device-masters.show', $device) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">No devices found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>

    <div class="mt-6">
        <a href="{{ route('company-branches.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Branches</a>
    </div>
</div>

<x-confirm-modal id="confirm-delete" title="Delete Branch" message="Delete this branch and all devices?" />
<script>
    function confirmDelete(id) {
        window.dispatchEvent(new CustomEvent('open-modal-confirm-delete'));
    }
    window.addEventListener('confirm-confirm-delete', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/company-branches/{{ $branch->id }}`;
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
    });
</script>
@endsection

