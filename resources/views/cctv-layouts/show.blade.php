@extends('layouts.app')

@section('title', 'Layout Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $layout->layout_name }}</h1>
            <p class="mt-2 text-gray-600">{{ ucfirst($layout->layout_type) }} - {{ $layout->total_positions }} positions</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('cctv-layouts.edit', $layout) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">Edit</a>
            @if(!$layout->is_default)
                <button @click="confirmDelete({{ $layout->id }})" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <x-card title="Layout Info">
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($layout->layout_type) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Positions</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $layout->total_positions }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs font-semibold rounded-full {{ $layout->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $layout->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>
                @if($layout->is_default)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Default</dt>
                        <dd class="mt-1"><span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">Yes</span></dd>
                    </div>
                @endif
            </dl>
        </x-card>

        <div class="lg:col-span-3">
            <x-card title="Position Configuration">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($layout->positions as $position)
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-900">Position {{ $position->position_number }}</h4>
                                <span class="px-2 py-1 text-xs {{ $position->is_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} rounded">
                                    {{ $position->is_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">{{ $position->position_name }}</p>
                            <div class="space-y-1 text-sm">
                                <p><span class="text-gray-500">Branch:</span> {{ $position->branch->branch_name ?? 'N/A' }}</p>
                                <p><span class="text-gray-500">Device:</span> {{ $position->device->device_name ?? 'N/A' }}</p>
                                <p><span class="text-gray-500">Quality:</span> {{ ucfirst($position->quality) }}</p>
                                @if($position->auto_switch)
                                    <p><span class="text-gray-500">Auto-switch:</span> {{ $position->switch_interval }}s</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('cctv-layouts.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Layouts</a>
    </div>
</div>

<x-confirm-modal id="confirm-delete" title="Delete Layout" message="Delete this CCTV layout?" />
<script>
    function confirmDelete(id) {
        window.dispatchEvent(new CustomEvent('open-modal-confirm-delete'));
    }
    window.addEventListener('confirm-confirm-delete', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/cctv-layouts/{{ $layout->id }}`;
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
    });
</script>
@endsection


