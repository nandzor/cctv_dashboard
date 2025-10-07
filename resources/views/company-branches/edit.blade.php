@extends('layouts.app')

@section('title', 'Edit Company Branch')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Company Branch</h1>
        <p class="mt-2 text-gray-600">Update branch information</p>
    </div>

    <x-card>
        <form action="{{ route('company-branches.update', $companyBranch) }}" method="POST">
            @csrf
            @method('PUT')

            <x-form-input 
                label="Company Group" 
                name="group_id" 
                type="select"
                :required="true"
            >
                @foreach($companyGroups as $group)
                    <option value="{{ $group->id }}" {{ $companyBranch->group_id == $group->id ? 'selected' : '' }}>
                        {{ $group->group_name }} ({{ $group->province_name }})
                    </option>
                @endforeach
            </x-form-input>

            <x-form-input label="Branch Code" name="branch_code" :value="$companyBranch->branch_code" :required="true" />
            <x-form-input label="Branch Name" name="branch_name" :value="$companyBranch->branch_name" :required="true" />
            <x-form-input label="City Name" name="city_name" :value="$companyBranch->city_name" :required="true" />
            <x-form-input label="Address" name="address" type="textarea" :value="$companyBranch->address" />

            <div class="grid grid-cols-2 gap-4">
                <x-form-input label="Phone" name="phone" type="tel" :value="$companyBranch->phone" />
                <x-form-input label="Email" name="email" type="email" :value="$companyBranch->email" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-form-input label="Latitude" name="latitude" type="number" step="0.00000001" :value="$companyBranch->latitude" />
                <x-form-input label="Longitude" name="longitude" type="number" step="0.00000001" :value="$companyBranch->longitude" />
            </div>

            <x-form-input label="Status" name="status" type="select" :required="true">
                <option value="active" {{ $companyBranch->status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $companyBranch->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </x-form-input>

            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('company-branches.show', $companyBranch) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Branch</button>
            </div>
        </form>
    </x-card>
</div>
@endsection


