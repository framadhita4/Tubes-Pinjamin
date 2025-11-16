@extends('layouts.app')

@section('title', 'Login - PinjamIn')

@section('header-buttons')
    <button class="btn small" onclick="location.href='{{ route('home') }}'">Beranda</button>
@endsection

@section('content')
    <div class="form-card">
        <form id="formLogin" class="form-grid">
            @csrf
            <input id="loginEmail" name="email" placeholder="Email" type="email" required />
            <input id="loginPassword" name="password" placeholder="Password" type="password" required />
            <button class="btn" type="submit">Login</button>
        </form>
        <p style="margin-top:10px;">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    </div>

    <script>
        document.getElementById('formLogin').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('{{ route('login.post') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Store user data in localStorage for compatibility with existing code
                        localStorage.setItem('isLoggedIn', 'true');
                        localStorage.setItem('loggedUser', JSON.stringify(data.user));

                        alert(data.message);
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                });
        });
    </script>
@endsection
