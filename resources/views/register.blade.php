@extends('layouts.auth')

@section('title', 'Register - PinjamIn')

@section('content')
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="card bg-base-100 shadow-2xl">
            <div class="card-body">
                <!-- Logo/Header -->
                <div class="text-center mb-6">
                    <h1 class="text-4xl font-bold text-primary mb-2">PinjamIn</h1>
                    <p class="text-base-content/70">Buat akun baru untuk memulai</p>
                </div>

                <!-- Register Form -->
                <form id="formRegister" class="space-y-4">
                    @csrf

                    <!-- Name Input -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Nama Lengkap</span>
                        </label>
                        <input type="text" id="regNama" name="name" placeholder="Masukkan nama lengkap"
                            class="input input-bordered w-full focus:input-primary" required />
                    </div>

                    <!-- Email Input -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Email</span>
                        </label>
                        <input type="email" id="regEmail" name="email" placeholder="nama@email.com"
                            class="input input-bordered w-full focus:input-primary" required />
                    </div>

                    <!-- NIM Input -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">NIM</span>
                            <span class="label-text-alt text-base-content/60">(Opsional)</span>
                        </label>
                        <input type="text" id="regNIM" name="nim" placeholder="Masukkan NIM"
                            class="input input-bordered w-full focus:input-primary" />
                    </div>

                    <!-- Role Selection -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Daftar Sebagai</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="peminjam"
                                    class="radio radio-primary hidden peer" checked />
                                <div
                                    class="card bg-base-200 hover:bg-primary/10 peer-checked:bg-primary peer-checked:text-primary-content transition-all border-2 border-transparent peer-checked:border-primary">
                                    <div class="card-body p-4 text-center">
                                        <div class="text-2xl mb-1">🎯</div>
                                        <p class="text-sm font-semibold">Peminjam</p>
                                    </div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="costumer"
                                    class="radio radio-secondary hidden peer" />
                                <div
                                    class="card bg-base-200 hover:bg-secondary/10 peer-checked:bg-secondary peer-checked:text-secondary-content transition-all border-2 border-transparent peer-checked:border-secondary">
                                    <div class="card-body p-4 text-center">
                                        <div class="text-2xl mb-1">📦</div>
                                        <p class="text-sm font-semibold">Pemilik</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Password</span>
                        </label>
                        <input type="password" id="regPassword" name="password" placeholder="Minimal 8 karakter"
                            class="input input-bordered w-full focus:input-primary" required minlength="8" />
                        <label class="label">
                            <span class="label-text-alt text-base-content/60">Minimal 8 karakter</span>
                        </label>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="checkbox checkbox-primary checkbox-sm" required />
                            <span class="label-text text-xs">
                                Saya setuju dengan <a href="#" class="link link-primary">Syarat & Ketentuan</a>
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary w-full text-white">
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                            Daftar Sekarang
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="divider">ATAU</div>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-base-content/70">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="link link-primary font-semibold">Login</a>
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

        <!-- Info Banner -->
        <div class="alert alert-info shadow-lg mt-6">
            <i data-lucide="info" class="w-6 h-6"></i>
            <div class="text-sm">
                <p class="font-semibold">Pilih peran yang sesuai</p>
                <p class="text-xs opacity-80">Peminjam: meminjam barang | Pemilik: menyewakan barang</p>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast toast-top toast-end z-50"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('formRegister').addEventListener('submit', async function(e) {
                e.preventDefault();

                await window.submitForm(this, '{{ route('register.post') }}', {
                    loadingText: 'Mendaftar...',
                    onSuccess: (data) => {
                        // Redirect after short delay
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                });
            });

            // Add visual feedback for role selection
            document.querySelectorAll('input[name="role"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    window.showToast(
                        `Anda memilih sebagai ${this.value === 'peminjam' ? 'Peminjam' : 'Pemilik'}`,
                        'info'
                    );
                });
            });
        });
    </script>
@endsection
