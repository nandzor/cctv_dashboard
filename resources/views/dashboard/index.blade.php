@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
  <!-- Welcome Banner -->
  <div class="mb-8">
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 rounded-2xl shadow-2xl">
      <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
      <div class="absolute bottom-0 left-0 w-40 h-40 bg-white opacity-5 rounded-full -ml-20 -mb-20"></div>

      <div class="relative p-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Welcome back, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-blue-100">Here's what's happening with your application today.</p>
          </div>
          <div class="hidden md:block">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4">
              <p class="text-100 text-sm">{{ now()->format('l, F j, Y') }}</p>
              <p class="text text-2xl font-bold">{{ now()->format('H:i') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card title="Total Users" :value="$totalUsers" color="blue" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\'/>'" trend="+12%"
      :trend-up="true" />

    <x-stat-card title="Active Sessions" value="1" color="green" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\'/>'" trend="+5%"
      :trend-up="true" />

    <x-stat-card title="API Requests" value="1,234" color="purple" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z\'/>'" trend="+23%"
      :trend-up="true" />

    <x-stat-card title="System Status" value="Healthy" color="indigo" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z\'/>'" />
  </div>

  <!-- Quick Actions & Info -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Quick Actions -->
    <x-card>
      <h3 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h3>
      <div class="space-y-3">
        <x-button href="{{ route('users.create') }}" variant="primary" class="w-full">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create New User
        </x-button>

        <x-button href="{{ route('users.index') }}" variant="outline" class="w-full">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          View All Users
        </x-button>
      </div>
    </x-card>

    <!-- System Info -->
    <x-card>
      <h3 class="text-xl font-bold text-gray-900 mb-4">System Information</h3>
      <div class="space-y-3">
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
          <span class="text-gray-600">Laravel Version</span>
          <span class="font-semibold text-gray-900">12.x</span>
        </div>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
          <span class="text-gray-600">PHP Version</span>
          <span class="font-semibold text-gray-900">{{ PHP_VERSION }}</span>
        </div>
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
          <span class="text-gray-600">Database</span>
          <span class="font-semibold text-gray-900">PostgreSQL</span>
        </div>
      </div>
    </x-card>
  </div>
@endsection
