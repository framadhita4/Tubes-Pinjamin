@extends('layouts.app')

@section('title', 'Register - PinjamIn')

@section('header-buttons')
    <button class="btn small" onclick="location.href='{{ route('home') }}'">Beranda</button>
@endsection

@section('content')
    <div class="form-card">
        <form id="formRegister" class="form-grid">
            @csrf
            <input id="regNama" name="name" placeholder="Nama Lengkap" required />
            <input id="regEmail" name="email" placeholder="Email (contoh: a@b.com)" type="email" required />
            <input id="regPassword" name="password" placeholder="Password" type="password" required />
            <input id="regNIM" name="nim" placeholder="NIM (opsional)" />
            <button class="btn" type="submit">Daftar</button>
        </form>
        <p style="margin-top:10px;">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
    </div>

    <script>
        document.getElementById('formRegister').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('{{ route('register.post') }}', {
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
                        alert(data.message);
                        window.location.href = data.redirect;
                    } else {
                        let errorMsg = data.message;
                        if (data.errors) {
                            errorMsg += '\n' + Object.values(data.errors).flat().join('\n');
                        }
                        alert(errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                });
        });
    </script>
@endsection
