<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PinjamIn - Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-base-200">
    <!-- Navbar -->
    <div class="navbar bg-base-100 shadow-lg sticky top-0 z-50">
        <div class="navbar-start">
            <div class="dropdown">
                <label tabindex="0" class="btn btn-ghost lg:hidden">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </label>
                <ul tabindex="0"
                    class="menu menu-sm dropdown-content mt-3 z-1 p-2 shadow bg-base-100 rounded-box w-52">
                    @auth
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        @if (Auth::user()->role === 'peminjam')
                            <li><a href="{{ route('my-borrowings') }}">Peminjaman Saya</a></li>
                        @endif
                        @if (Auth::user()->role === 'costumer')
                            <li><a href="{{ route('upload') }}">Upload Barang</a></li>
                            <li><a href="{{ route('my-items') }}">Barang Saya</a></li>
                            <li><a href="{{ route('panel-pemilik') }}">Panel Pemilik</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('home') }}">Beranda</a></li>
                        <li><a href="{{ route('login') }}">Login</a></li>
                        <li><a href="{{ route('register') }}">Daftar</a></li>
                    @endauth
                </ul>
            </div>
            <a href="{{ Auth::check() ? route('dashboard') : route('home') }}"
                class="btn btn-ghost normal-case text-xl">
                <span class="text-primary font-bold">PinjamIn</span>
            </a>
        </div>

        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1">
                @auth
                    <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard
                        </a></li>
                    @if (Auth::user()->role === 'peminjam')
                        <li><a href="{{ route('my-borrowings') }}"
                                class="{{ request()->routeIs('my-borrowings') ? 'active' : '' }}">
                                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                                Peminjaman Saya
                            </a></li>
                    @endif
                    @if (Auth::user()->role === 'costumer')
                        <li><a href="{{ route('upload') }}" class="{{ request()->routeIs('upload') ? 'active' : '' }}">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Upload Barang
                            </a></li>
                        <li><a href="{{ route('my-items') }}"
                                class="{{ request()->routeIs('my-items') ? 'active' : '' }}">
                                <i data-lucide="package" class="w-4 h-4"></i>
                                Barang Saya
                            </a></li>
                        <li><a href="{{ route('panel-pemilik') }}"
                                class="{{ request()->routeIs('panel-pemilik') ? 'active' : '' }}">
                                <i data-lucide="settings" class="w-4 h-4"></i>
                                Panel Pemilik
                            </a></li>
                    @endif
                @else
                    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                            <i data-lucide="home" class="w-4 h-4"></i>
                            Beranda
                        </a></li>
                @endauth
            </ul>
        </div>

        <div class="navbar-end gap-2">
            @auth
                <!-- Notifications -->
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle">
                        <div class="indicator">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            <span class="badge badge-sm badge-primary indicator-item" id="notif-badge">0</span>
                        </div>
                    </label>
                    <div tabindex="0" class="mt-3 z-1 card card-compact dropdown-content w-80 bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h3 class="font-bold text-lg">Notifikasi</h3>
                            <div id="notifications-list" class="space-y-2 max-h-96 overflow-y-auto">
                                <p class="text-sm text-base-content/70">Memuat notifikasi...</p>
                            </div>
                            <div class="card-actions">
                                <button class="btn btn-sm btn-ghost btn-block" onclick="markAllNotificationsRead()">
                                    Tandai Semua Dibaca
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-10 flex items-center justify-center">
                            <span class="text-xl">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                    </label>
                    <ul tabindex="0"
                        class="menu menu-sm dropdown-content mt-3 z-1 p-2 shadow bg-base-100 rounded-box w-52">
                        <li class="menu-title">
                            <span>{{ Auth::user()->name }}</span>
                            <span class="badge badge-sm">{{ ucfirst(Auth::user()->role) }}</span>
                        </li>
                        <li><a href="{{ route('dashboard') }}">
                                <i data-lucide="user" class="w-4 h-4"></i>
                                Dashboard
                            </a></li>
                        <li>
                            <a href="#" onclick="handleLogout(event)">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            @else
                <!-- Guest Buttons -->
                <a href="{{ route('login') }}" class="btn btn-ghost">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    Login
                </a>
                <a href="{{ route('register') }}" class="btn btn-primary">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Daftar
                </a>
            @endauth
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Flash Messages -->
        @if (session('message'))
            <div class="alert alert-success shadow-lg mb-6">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error shadow-lg mb-6">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast toast-top toast-end z-50"></div>

    <!-- Global Scripts -->
    <script>
        @auth
        // Handle logout
        async function handleLogout(event) {
            event.preventDefault();

            // for fetch use a helper function
            const response = await window.fetchRequest('{{ route('logout') }}', {
                method: 'POST',
            });

            if (response.success) {
                // Clear localStorage
                localStorage.removeItem('isLoggedIn');
                localStorage.removeItem('loggedUser');

                window.location.href = response.data.redirect;
            }
        }

        // Load notifications on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        });

        async function loadNotifications() {
            try {
                const response = await window.fetchRequest('{{ route('notifications.unread-count') }}', {
                    method: 'GET'
                });

                if (response.success) {
                    const badge = document.getElementById('notif-badge');
                    badge.textContent = response.data.count;
                    badge.style.display = response.data.count > 0 ? 'flex' : 'none';
                }

                // Load notification list
                const listResponse = await window.fetchRequest('{{ route('notifications.index') }}', {
                    method: 'GET'
                });

                if (listResponse.success) {
                    displayNotifications(listResponse.data.data);
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }

        function displayNotifications(notifications) {
            const container = document.getElementById('notifications-list');

            if (notifications.data && notifications.data.length === 0) {
                container.innerHTML = '<p class="text-sm text-base-content/70">Tidak ada notifikasi</p>';
                return;
            }

            container.innerHTML = notifications.data.map(notif => `
                <div class="alert ${notif.is_read ? 'alert-info' : 'alert-warning'} py-2 cursor-pointer" 
                     onclick="markNotificationRead(${notif.id})">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm">${notif.title}</h4>
                        <p class="text-xs">${notif.message}</p>
                        <p class="text-xs opacity-70">${new Date(notif.created_at).toLocaleString('id-ID')}</p>
                    </div>
                </div>
            `).join('');
        }

        async function markNotificationRead(id) {
            try {
                await window.fetchRequest(`{{ route('notifications.mark-read', '_id_') }}`.replace('_id_', id), {
                    method: 'POST'
                });
                loadNotifications();
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        async function markAllNotificationsRead() {
            try {
                const response = await window.fetchRequest('{{ route('notifications.mark-all-read') }}', {
                    method: 'POST'
                });

                if (response.success) {
                    window.showToast(response.data.message, 'success');
                    loadNotifications();
                }
            } catch (error) {
                console.error('Error marking all notifications as read:', error);
            }
        }
        @endauth
    </script>

    @stack('scripts')
</body>

</html>
