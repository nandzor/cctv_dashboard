@extends('layouts.app')

@section('title', 'Create Company Group')

@section('content')
  <div class="max-w-3xl mx-auto">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Create Company Group</h1>
      <p class="mt-2 text-gray-600">Add a new province-level company group</p>
    </div>

    <x-card>
      <form action="{{ route('company-groups.store') }}" method="POST">
        @csrf

        <x-form-input label="Province Code" name="province_code" :required="true" placeholder="e.g., JB (Jawa Barat)" />

        <x-form-input label="Province Name" name="province_name" :required="true" placeholder="e.g., Jawa Barat" />

        <x-form-input label="Group Name" name="group_name" :required="true" placeholder="e.g., PT. Company Name" />

        <x-form-input label="Address" name="address" type="textarea" placeholder="Full address" />

        <x-form-input label="Phone" name="phone" type="tel" placeholder="e.g., +62812345678" />

        <x-form-input label="Email" name="email" type="email" placeholder="e.g., contact@company.com" />

        <x-form-input label="Status" name="status" type="select" :required="true">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
        </x-form-input>

        <div class="flex justify-end space-x-3 mt-6">
          <a href="{{ route('company-groups.index') }}"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </a>
          <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Create Group
          </button>
        </div>
      </form>
    </x-card>
  </div>
@endsection
