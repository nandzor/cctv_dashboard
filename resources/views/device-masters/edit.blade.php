@extends('layouts.app')

@section('title', 'Edit Device')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Device</h1>
        <p class="mt-2 text-gray-600">Update device information</p>
    </div>

    <x-card>
        <form action="{{ route('device-masters.update', $deviceMaster) }}" method="POST">
            @csrf
            @method('PUT')

            <x-form-input label="Device ID" name="device_id" :value="$deviceMaster->device_id" :required="true" />
            <x-form-input label="Device Name" name="device_name" :value="$deviceMaster->device_name" :required="true" />

            <x-form-input label="Device Type" name="device_type" type="select" :required="true">
                <option value="camera" {{ $deviceMaster->device_type === 'camera' ? 'selected' : '' }}>Camera</option>
                <option value="node_ai" {{ $deviceMaster->device_type === 'node_ai' ? 'selected' : '' }}>Node AI</option>
                <option value="mikrotik" {{ $deviceMaster->device_type === 'mikrotik' ? 'selected' : '' }}>Mikrotik</option>
                <option value="cctv" {{ $deviceMaster->device_type === 'cctv' ? 'selected' : '' }}>CCTV</option>
            </x-form-input>

            <x-form-input label="Branch" name="branch_id" type="select" :required="true">
                @foreach($companyBranches as $branch)
                    <option value="{{ $branch->id }}" {{ $deviceMaster->branch_id == $branch->id ? 'selected' : '' }}>
                        {{ $branch->branch_name }} ({{ $branch->city_name }})
                    </option>
                @endforeach
            </x-form-input>

            <x-form-input label="URL / IP Address" name="url" :value="$deviceMaster->url" />
            
            <div class="grid grid-cols-2 gap-4">
                <x-form-input label="Username" name="username" :value="$deviceMaster->username" />
                <x-form-input label="Password" name="password" type="password" placeholder="Leave blank to keep current" />
            </div>

            <x-form-input label="Notes" name="notes" type="textarea" :value="$deviceMaster->notes" />

            <x-form-input label="Status" name="status" type="select" :required="true">
                <option value="active" {{ $deviceMaster->status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $deviceMaster->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </x-form-input>

            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('device-masters.show', $deviceMaster) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Device</button>
            </div>
        </form>
    </x-card>
</div>
@endsection


