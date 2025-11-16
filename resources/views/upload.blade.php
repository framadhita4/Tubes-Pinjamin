@extends('layouts.app')

@section('title', 'Upload Barang - PinjamIn')

@section('header-buttons')
    <button class="btn small" onclick="location.href='{{ route('dashboard') }}'">⬅ Dashboard</button>
    <button class="btn small danger" onclick="logout()">Logout</button>
@endsection

@section('content')
    <form id="formUpload" class="form-grid">
        <input id="itemName" placeholder="Nama Barang" required />
        <input id="itemStok" type="number" min="1" value="1" required />
        <input id="itemKondisi" placeholder="Kondisi (contoh: bagus, hampir baru)" />
        <textarea id="itemDeskripsi" placeholder="Deskripsi (opsional)"></textarea>
        <label>Foto Barang (jpg/png):</label>
        <input id="itemFoto" type="file" accept="image/*" required />
        <button class="btn" type="submit">Upload Barang</button>
    </form>

    <script>
        document.getElementById('formUpload').addEventListener('submit', function(e) {
            e.preventDefault();
            uploadItem();
        });
    </script>
@endsection
