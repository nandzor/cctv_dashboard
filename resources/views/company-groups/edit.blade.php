@extends('layouts.app')

@section('title', 'Edit Company Group')

@section('content')
  <div class="max-w-3xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Edit Company Group</h1>
      <p class="mt-2 text-gray-600">Update company group information</p>
    </div>

    <x-card>
      <form action="{{ route('company-groups.update', $companyGroup) }}" method="POST">
        @csrf
        @method('PUT')

        <x-form-input label="Province Code" name="province_code" :value="$companyGroup->province_code" :required="true" />

        <x-form-input label="Province Name" name="province_name" :value="$companyGroup->province_name" :required="true" />

        <x-form-input label="Group Name" name="group_name" :value="$companyGroup->group_name" :required="true" />

        <x-form-input label="Address" name="address" type="textarea" :value="$companyGroup->address" />

        <x-form-input label="Phone" name="phone" type="tel" :value="$companyGroup->phone" />

        <x-form-input label="Email" name="email" type="email" :value="$companyGroup->email" />

        <x-form-input label="Status" name="status" type="select" :required="true">
          <option value="active" {{ $companyGroup->status === 'active' ? 'selected' : '' }}>Active</option>
          <option value="inactive" {{ $companyGroup->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </x-form-input>

        <div class="flex justify-end space-x-3 mt-6">
          <a href="{{ route('company-groups.show', $companyGroup) }}"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </a>
          <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Update Group
          </button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
