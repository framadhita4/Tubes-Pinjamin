@extends('layouts.auth')

@section('title', 'Login - PinjamIn')

@section('content')
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="card bg-base-100 shadow-2xl">
            <div class="card-body">
                <!-- Logo/Header -->
                <div class="text-center mb-6">
                    <h1 class="text-4xl font-bold text-primary mb-2">PinjamIn</h1>
                    <p class="text-base-content/70">Selamat datang kembali!</p>
                </div>

                <!-- Login Form -->
                <form id="formLogin" class="space-y-4">
                    @csrf

                    <!-- Email Input -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Email</span>
                        </label>
                        <input type="email" id="loginEmail" name="email" placeholder="nama@email.com"
                            class="input input-bordered w-full focus:input-primary" required />
                    </div>

                    <!-- Password Input -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Password</span>
                        </label>
                        <input type="password" id="loginPassword" name="password" placeholder="••••••••"
                            class="input input-bordered w-full focus:input-primary" required />
                        {{-- <label class="label">
                            <a href="#" class="label-text-alt link link-hover link-primary">Lupa password?</a>
                        </label> --}}
                    </div>

                    <!-- Submit Button -->
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full text-white">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                            Masuk
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="divider">ATAU</div>

                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-base-content/70">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="link link-primary font-semibold">Daftar Sekarang</a>
                    </p>
                </div>

                <!-- Back to Home -->
                {{-- <div class="text-center mt-4">
                    <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Kembali ke Beranda
                    </a>
                </div> --}}
            </div>
        </div>

        <!-- Features Cards -->
        <div class="mt-6 grid grid-cols-2 gap-3">
            <div class="card bg-base-100/90 shadow-lg">
                <div class="card-body p-4 text-center">
                    <i data-lucide="package" class="w-8 h-8 mx-auto text-primary mb-2"></i>
                    <p class="text-xs font-medium">Pinjam Mudah</p>
                </div>
            </div>
            <div class="card bg-base-100/90 shadow-lg">
                <div class="card-body p-4 text-center">
                    <i data-lucide="shield-check" class="w-8 h-8 mx-auto text-secondary mb-2"></i>
                    <p class="text-xs font-medium">Aman Terpercaya</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast toast-top toast-end z-50"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('formLogin').addEventListener('submit', async function(e) {
                e.preventDefault();

                await window.submitForm(this, '{{ route('login.post') }}', {
                    loadingText: 'Memproses...',
                    onSuccess: (data) => {
                        // Store user data in localStorage for compatibility with existing code
                        localStorage.setItem('isLoggedIn', 'true');
                        localStorage.setItem('loggedUser', JSON.stringify(data.user));

                        // Redirect after short delay
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    }
                });
            });
        });
    </script>
@endsection
