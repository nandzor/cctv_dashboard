@extends('layouts.app')

@section('title', 'Create Device')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create Device</h1>
        <p class="mt-2 text-gray-600">Add a new device to the system</p>
    </div>

    <x-card>
        <form action="{{ route('device-masters.store') }}" method="POST">
            @csrf

            <x-form-input label="Device ID" name="device_id" :required="true" placeholder="e.g., CAMERA_001" />
            <x-form-input label="Device Name" name="device_name" :required="true" placeholder="Main Entrance Camera" />

            <x-form-input label="Device Type" name="device_type" type="select" :required="true">
                <option value="camera">Camera</option>
                <option value="node_ai">Node AI</option>
                <option value="mikrotik">Mikrotik</option>
                <option value="cctv">CCTV</option>
            </x-form-input>

            <x-form-input label="Branch" name="branch_id" type="select" :required="true">
                <option value="">-- Select Branch --</option>
                @foreach($companyBranches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->branch_name }} ({{ $branch->city_name }})</option>
                @endforeach
            </x-form-input>

            <x-form-input label="URL / IP Address" name="url" placeholder="rtsp://192.168.1.100:554/stream1" />
            
            <div class="grid grid-cols-2 gap-4">
                <x-form-input label="Username" name="username" />
                <x-form-input label="Password" name="password" type="password" />
            </div>

            <x-form-input label="Notes" name="notes" type="textarea" placeholder="Additional information..." />

            <x-form-input label="Status" name="status" type="select" :required="true">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </x-form-input>

            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('device-masters.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create Device</button>
            </div>
        </form>
    </x-card>
</div>
@endsection


