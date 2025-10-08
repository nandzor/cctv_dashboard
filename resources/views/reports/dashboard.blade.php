@extends('layouts.app')

@section('title', 'Reports Dashboard')
@section('page-title', 'Reports Dashboard')

@section('content')
  <!-- Statistics -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-stat-card title="Total Detections" :value="$totalDetections" icon="eye" color="blue" />
    <x-stat-card title="Unique Persons" :value="$uniquePersons" icon="users" color="green" />
    <x-stat-card title="Active Branches" :value="$uniqueBranches" icon="building" color="purple" />
    <x-stat-card title="Active Devices" :value="$uniqueDevices" icon="camera" color="orange" />
  </div>

  <!-- Filters -->
  <x-card class="mb-6">
    <div class="p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Report Filters</h3>
      <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-input type="date" name="date_from" :value="$dateFrom" label="From Date" />
        <x-input type="date" name="date_to" :value="$dateTo" label="To Date" />
        <x-company-branch-select name="branch_id" :value="$branchId" label="Select Branch" placeholder="All Branches" />
        <x-button type="submit" variant="primary" size="sm" class="self-end">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          Generate Report
        </x-button>
      </form>
    </div>
  </x-card>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Daily Trend Chart -->
    <x-detection-trend-chart :data="$dailyTrend" title="Detection Trend" />

    <!-- Top Branches -->
    <x-card>
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Branches by Detections</h3>
        <div class="space-y-4">
          @foreach ($topBranches as $item)
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
              <div>
                <p class="font-semibold text-gray-900">{{ $item->branch->branch_name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500">{{ $item->branch->city_name ?? '' }}</p>
              </div>
              <x-badge variant="primary" size="lg">
                {{ number_format($item->detection_count) }}
              </x-badge>
            </div>
          @endforeach
        </div>
      </div>
    </x-card>
  </div>
@endsection
