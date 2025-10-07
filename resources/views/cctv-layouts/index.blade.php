@extends('layouts.app')

@section('title', 'CCTV Layouts')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">CCTV Layouts</h1>
            <p class="mt-2 text-gray-600">Manage CCTV grid layouts (4, 6, 8 windows)</p>
        </div>
        <a href="{{ route('cctv-layouts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Layout
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-6 mb-6">
        <x-stat-card title="Total Layouts" :value="$statistics['total_layouts']" icon="chart-bar" color="blue" />
        <x-stat-card title="Active Layouts" :value="$statistics['active_layouts']" icon="eye" color="green" />
        <x-stat-card title="Default Layout" :value="$statistics['by_type']['4-window'] ?? 0" icon="building" color="purple" />
    </div>

    <!-- Layouts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($layouts->items() as $layout)
            <x-card>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $layout->layout_name }}</h3>
                        <p class="text-sm text-gray-500">{{ ucfirst($layout->layout_type) }}</p>
                    </div>
                    @if($layout->is_default)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">Default</span>
                    @endif
                </div>

                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Positions:</span>
                        <span class="font-semibold">{{ $layout->total_positions }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Status:</span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $layout->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $layout->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Created by:</span>
                        <span class="font-semibold">{{ $layout->creator->name ?? 'System' }}</span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('cctv-layouts.show', $layout) }}" class="flex-1 px-4 py-2 text-center bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">View</a>
                    <a href="{{ route('cctv-layouts.edit', $layout) }}" class="flex-1 px-4 py-2 text-center bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm">Edit</a>
                </div>
            </x-card>
        @empty
            <div class="col-span-3">
                <x-card>
                    <div class="text-center py-8">
                        <p class="text-gray-400">No layouts found. <a href="{{ route('cctv-layouts.create') }}" class="text-blue-600 hover:text-blue-800">Create one now</a></p>
                    </div>
                </x-card>
            </div>
        @endforelse
    </div>

    @if($layouts->hasPages())
        <div class="mt-6">{{ $layouts->links() }}</div>
    @endif
</div>
@endsection


