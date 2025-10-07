@extends('layouts.app')

@section('title', 'Create Company Branch')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create Company Branch</h1>
        <p class="mt-2 text-gray-600">Add a new city-level company branch</p>
    </div>

    <x-card>
        <form action="{{ route('company-branches.store') }}" method="POST">
            @csrf

            <x-form-input 
                label="Company Group" 
                name="group_id" 
                type="select"
                :required="true"
            >
                <option value="">-- Select Group --</option>
                @foreach($companyGroups as $group)
                    <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                        {{ $group->group_name }} ({{ $group->province_name }})
                    </option>
                @endforeach
            </x-form-input>

            <x-form-input 
                label="Branch Code" 
                name="branch_code" 
                :required="true"
                placeholder="e.g., JKT001"
            />

            <x-form-input 
                label="Branch Name" 
                name="branch_name" 
                :required="true"
                placeholder="e.g., Jakarta Central Branch"
            />

            <x-form-input 
                label="City Name" 
                name="city_name" 
                :required="true"
                placeholder="e.g., Central Jakarta"
            />

            <x-form-input 
                label="Address" 
                name="address" 
                type="textarea"
                placeholder="Full address"
            />

            <div class="grid grid-cols-2 gap-4">
                <x-form-input 
                    label="Phone" 
                    name="phone" 
                    type="tel"
                    placeholder="+62812345678"
                />

                <x-form-input 
                    label="Email" 
                    name="email" 
                    type="email"
                    placeholder="branch@company.com"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-form-input 
                    label="Latitude" 
                    name="latitude" 
                    type="number"
                    step="0.00000001"
                    placeholder="-6.200000"
                />

                <x-form-input 
                    label="Longitude" 
                    name="longitude" 
                    type="number"
                    step="0.00000001"
                    placeholder="106.816666"
                />
            </div>

            <x-form-input 
                label="Status" 
                name="status" 
                type="select"
                :required="true"
            >
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </x-form-input>

            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('company-branches.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Create Branch
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection


