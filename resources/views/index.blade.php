@extends('layouts.app')

@section('title', 'PinjamIn - Beranda')

@section('header-buttons')
    <button class="btn small" onclick="location.href='{{ route('login') }}'">Login</button>
    <button class="btn small outline" onclick="location.href='{{ route('register') }}'">Daftar</button>
@endsection

@section('content')
    <section class="hero">
        <h1>Selamat datang di PinjamIn</h1>
        <p>Platform saling meminjam antar mahasiswa. Daftar dan upload barangmu untuk dipinjamkan!</p>
        <div style="margin-top:14px;">
            <button class="btn" onclick="location.href='{{ route('login') }}'">Mulai</button>
        </div>
    </section>
@endsection
