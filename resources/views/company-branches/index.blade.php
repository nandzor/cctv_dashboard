@extends('layouts.app')

@section('title', 'Company Branches')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Company Branches</h1>
            <p class="mt-2 text-gray-600">Manage city-level company branches</p>
        </div>
        <a href="{{ route('company-branches.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Branch
        </a>
    </div>

    <x-card class="mb-6">
        <form method="GET" action="{{ route('company-branches.index') }}" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by branch name or city..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
            @if(request('search'))
                <a href="{{ route('company-branches.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Clear</a>
            @endif
        </form>
    </x-card>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($companyBranches->items() as $branch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $branch->branch_code }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $branch->branch_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $branch->city_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $branch->companyGroup->group_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($branch->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                <a href="{{ route('company-branches.show', $branch) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                <a href="{{ route('company-branches.edit', $branch) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                <button @click="confirmDelete({{ $branch->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                No company branches found. <a href="{{ route('company-branches.create') }}" class="text-blue-600 hover:text-blue-800">Create one now</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($companyBranches->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $companyBranches->links() }}
            </div>
        @endif
    </x-card>
</div>

<x-confirm-modal 
    id="confirm-delete"
    title="Delete Company Branch"
    message="Are you sure you want to delete this branch? All associated devices will also be deleted."
    confirmText="Delete"
    cancelText="Cancel"
    icon="warning"
/>

<script>
    let pendingDeleteId = null;
    function confirmDelete(id) {
        pendingDeleteId = id;
        window.dispatchEvent(new CustomEvent('open-modal-confirm-delete', { detail: { id: id } }));
    }
    window.addEventListener('confirm-confirm-delete', function() {
        if (pendingDeleteId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/company-branches/${pendingDeleteId}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endsection


