@extends('layouts.app')

@section('title', 'Form Peminjaman - PinjamIn')

@section('header-buttons')
    <button class="btn small" onclick="location.href='{{ route('dashboard') }}'">⬅ Dashboard</button>
@endsection

@section('content')
    <div class="form-container">
        <h2>Form Peminjaman Barang</h2>
        <form id="formPeminjaman">
            <input type="text" id="nama" placeholder="Nama Lengkap" required>
            <input type="text" id="nim" placeholder="NIM" required>
            <input type="text" id="jurusan" placeholder="Jurusan" required>
            <input type="text" id="kelas" placeholder="Kelas" required>
            <textarea id="alasan" placeholder="Alasan Peminjaman" required></textarea>
            <input type="number" id="lama" min="1" max="30" placeholder="Lama Peminjaman (hari)"
                required>
            <p><strong>Saya berjanji akan menjaga barang dan mengembalikan tepat waktu. Jika rusak, saya bertanggung
                    jawab.</strong></p>
            <button type="submit">Kirim</button>
        </form>
    </div>
@endsection
