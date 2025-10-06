@extends('layouts.guest')

@section('title', 'Login')

@section('content')
  <div class="bg-white rounded-2xl shadow-2xl p-8">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Welcome Back</h1>
      <p class="text-gray-600 mt-2">Sign in to your account</p>
    </div>

    <!-- Demo Credentials -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
      <p class="text-sm font-medium text-blue-900 mb-2">Demo Credentials:</p>
      <div class="flex items-center justify-between text-sm text-blue-800">
        <div>
          <p class="font-mono">admin@example.com / admin123</p>
        </div>
        <button type="button" onclick="fillDemoCredentials()"
          class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
          Use Demo
        </button>
      </div>
    </div>

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="mb-6">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
        @error('email')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
        <input id="password" type="password" name="password" required
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror">
        @error('password')
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div class="mb-6">
        <label class="flex items-center">
          <input type="checkbox" name="remember"
            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
          <span class="ml-2 text-sm text-gray-700">Remember me</span>
        </label>
      </div>

      <button type="submit"
        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors font-medium">
        Sign In
      </button>
    </form>

    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-medium">Sign up</a>
      </p>
    </div>
  </div>

  <script>
    function fillDemoCredentials() {
      document.getElementById('email').value = 'admin@example.com';
      document.getElementById('password').value = 'admin123';

      // Show notification
      const notification = document.createElement('div');
      notification.className =
        'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300';
      notification.textContent = 'Demo credentials filled!';
      document.body.appendChild(notification);

      setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
      }, 2000);
    }
  </script>
@endsection
