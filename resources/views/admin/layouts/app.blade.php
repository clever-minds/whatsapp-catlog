<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-900 text-white flex flex-col shadow-xl">
        <div class="p-6 border-b border-blue-800">
            <a href="{{ route('admin.dashboard') }}" class="font-bold text-2xl tracking-wide block">WhatsApp Portal</a>
        </div>
        <nav class="flex-1 overflow-y-auto mt-4">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="block px-6 py-3 hover:bg-blue-800 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800 border-l-4 border-white' : '' }}">Dashboard</a>
                <a href="{{ route('categories.index') }}" class="block px-6 py-3 hover:bg-blue-800 transition-colors {{ request()->routeIs('categories.*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">Categories</a>
                <a href="{{ route('units.index') }}" class="block px-6 py-3 hover:bg-blue-800 transition-colors {{ request()->routeIs('units.*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">Units</a>
                <a href="{{ route('products.index') }}" class="block px-6 py-3 hover:bg-blue-800 transition-colors {{ request()->routeIs('products.*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">Products</a>
                <a href="{{ route('orders.index') }}" class="block px-6 py-3 hover:bg-blue-800 transition-colors {{ request()->routeIs('orders.*') ? 'bg-blue-800 border-l-4 border-white' : '' }}">Orders</a>
            @endauth
        </nav>
        @auth
        <div class="p-6 border-t border-blue-800">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex justify-center items-center bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </form>
        </div>
        @endauth
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
        <div class="container mx-auto p-8 max-w-6xl">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</body>
</html>
