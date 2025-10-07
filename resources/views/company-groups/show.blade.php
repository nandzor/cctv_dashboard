@extends('layouts.app')

@section('title', 'Company Group Details')

@section('content')
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $companyGroup->group_name }}</h1>
        <p class="mt-2 text-gray-600">{{ $companyGroup->province_name }}</p>
      </div>
      @if (auth()->user()->isAdmin())
        <div class="flex space-x-3">
          <a href="{{ route('company-groups.edit', $companyGroup) }}"
            class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
            Edit
          </a>
          <button @click="confirmDelete({{ $companyGroup->id }})"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            Delete
          </button>
        </div>
      @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Group Information -->
      <div class="lg:col-span-2">
        <x-card title="Group Information">
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
            <div>
              <dt class="text-sm font-medium text-gray-500">Province Code</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $companyGroup->province_code }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Status</dt>
              <dd class="mt-1">
                <span
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $companyGroup->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                  {{ ucfirst($companyGroup->status) }}
                </span>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Phone</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $companyGroup->phone ?: 'N/A' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Email</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $companyGroup->email ?: 'N/A' }}</dd>
            </div>
            <div class="md:col-span-2">
              <dt class="text-sm font-medium text-gray-500">Address</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $companyGroup->address ?: 'N/A' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Created</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $companyGroup->created_at->format('M d, Y H:i') }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ $companyGroup->updated_at->format('M d, Y H:i') }}</dd>
            </div>
          </dl>
        </x-card>
      </div>

      <!-- Statistics -->
      <div>
        <x-card title="Statistics">
          <div class="space-y-4">
            <div class="flex justify-between items-center pb-4 border-b border-gray-200">
              <span class="text-gray-600">Total Branches</span>
              <span class="text-2xl font-bold text-blue-600">{{ $branchCounts['total'] }}</span>
            </div>
            <div class="flex justify-between items-center pb-4 border-b border-gray-200">
              <span class="text-gray-600">Active Branches</span>
              <span class="text-xl font-semibold text-green-600">{{ $branchCounts['active'] }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-600">Inactive Branches</span>
              <span class="text-xl font-semibold text-red-600">{{ $branchCounts['inactive'] }}</span>
            </div>
          </div>
        </x-card>
      </div>
    </div>

    <!-- Company Branches -->
    <div class="mt-8">
      <x-card title="Company Branches" :padding="false">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($companyGroup->branches as $branch)
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm text-gray-900">{{ $branch->branch_code }}</td>
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $branch->branch_name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ $branch->city_name }}</td>
                  <td class="px-6 py-4">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                      {{ ucfirst($branch->status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right text-sm font-medium">
                    <a href="{{ route('company-branches.show', $branch) }}"
                      class="text-blue-600 hover:text-blue-900">View</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                    No branches found. <a
                      href="{{ route('company-branches.create', ['group_id' => $companyGroup->id]) }}"
                      class="text-blue-600 hover:text-blue-800">Create one now</a>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </x-card>
    </div>

    <div class="mt-6">
      <a href="{{ route('company-groups.index') }}" class="text-blue-600 hover:text-blue-800">
        ← Back to Company Groups
      </a>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <x-confirm-modal id="confirm-delete" title="Delete Company Group"
    message="Are you sure you want to delete this company group? All associated branches and devices will also be deleted."
    confirmText="Delete" cancelText="Cancel" icon="warning" />

  <script>
    function confirmDelete(id) {
      window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', {
        detail: {
          id: id
        }
      }));
    }

    window.addEventListener('confirm-confirm-delete', function() {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/ company-groups/{{ $companyGroup->id }}`;
      form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
      document.body.appendChild(form);
      form.submit();
    });
  </script>
@endsection
