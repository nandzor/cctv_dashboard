@extends('layouts.app')

@section('title', 'Reports Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Reports Dashboard</h1>
        <p class="mt-2 text-gray-600">Analytics and reporting overview</p>
    </div>

    <!-- Filters -->
    <x-card class="mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-4 py-2 border border-gray-300 rounded-lg">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="px-4 py-2 border border-gray-300 rounded-lg">
            <select name="branch_id" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Generate</button>
        </form>
    </x-card>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-stat-card title="Total Detections" :value="$totalDetections" icon="eye" color="blue" />
        <x-stat-card title="Unique Persons" :value="$uniquePersons" icon="users" color="green" />
        <x-stat-card title="Active Branches" :value="$uniqueBranches" icon="building" color="purple" />
        <x-stat-card title="Active Devices" :value="$uniqueDevices" icon="camera" color="orange" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Daily Trend Chart -->
        <x-card title="Detection Trend">
            <div class="h-64 flex items-end justify-between space-x-2">
                @php $maxCount = $dailyTrend->max('count') ?: 1; @endphp
                @foreach($dailyTrend as $trend)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-blue-500 rounded-t hover:bg-blue-600 transition-colors cursor-pointer" 
                             style="height: {{ ($trend->count / $maxCount) * 100 }}%"
                             title="{{ $trend->count }} detections">
                        </div>
                        <span class="text-xs text-gray-600 mt-2">{{ \Carbon\Carbon::parse($trend->date)->format('M d') }}</span>
                        <span class="text-sm font-semibold">{{ $trend->count }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Top Branches -->
        <x-card title="Top Branches by Detections">
            <div class="space-y-4">
                @foreach($topBranches as $item)
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $item->branch->branch_name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $item->branch->city_name ?? '' }}</p>
                        </div>
                        <span class="text-xl font-bold text-blue-600">{{ number_format($item->detection_count) }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>
@endsection


