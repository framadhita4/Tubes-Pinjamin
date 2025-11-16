@extends('layouts.app')

@section('title', 'Dashboard - PinjamIn')

@section('header-buttons')
    <h2 id="userWelcome" style="margin-right: 1rem;">Halo, {{ Auth::user()->name }}!</h2>
    <button class="btn small" onclick="location.href='{{ route('panel-pemilik') }}'">Panel Pemilik</button>
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: inline;">
        @csrf
        <button class="btn small btn-danger" type="submit">Logout</button>
    </form>
@endsection

@section('content')
    <section id="pinjamSection">
        <h3>Barang Tersedia Untuk Dipinjam</h3>
        <div id="barangDashboard" class="grid"></div>
    </section>

    <!-- Modal Form Peminjaman -->
    <div id="modalPeminjaman" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Form Peminjaman</h3>
            <form id="formPeminjaman" class="form-grid" onsubmit="event.preventDefault(); submitPeminjaman();">
                <input id="fp_nama" placeholder="Nama Lengkap" required />
                <input id="fp_nim" placeholder="NIM" required />
                <input id="fp_jurusan" placeholder="Jurusan" required />

                <label>Upload Foto KTM (wajib)</label>
                <input id="fp_ktm" type="file" accept="image/*" required />

                <label>Lama Peminjaman</label>
                <select id="fp_lama" required></select>
                <div id="lamaInfo" class="lama-note"></div>

                <textarea id="fp_alasan" placeholder="Alasan Peminjaman" required></textarea>

                <label><input id="fp_setuju" type="checkbox" /> Saya setuju menjaga barang & mengembalikan tepat
                    waktu.</label>

                <div style="margin-top:10px;display:flex;gap:8px;justify-content:flex-end">
                    <button class="btn" type="submit">Kirim Peminjaman</button>
                    <button class="btn outline" type="button" onclick="closeModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pengembalian -->
    <div id="modalPengembalian" class="modal">
        <div class="modal-content">
            <h3 id="returnModalTitle">Pengembalian Barang</h3>
            <img id="returnItemImage" src="" alt="Gambar Barang"
                style="max-width:220px;border-radius:8px;margin-bottom:8px">
            <p id="returnItemName" style="font-weight:600"></p>
            <label>Kondisi Terbaru</label>
            <textarea id="returnCondition" placeholder="Tuliskan kondisi barang..." required></textarea>
            <label>Upload Foto Kondisi (wajib)</label>
            <input id="returnPhoto" type="file" accept="image/*" required>
            <div style="margin-top:10px;display:flex;gap:8px;justify-content:flex-end">
                <button class="btn" onclick="submitReturn()">Kirim Pengembalian</button>
                <button class="btn outline" onclick="closeReturnModal()">Batal</button>
            </div>
        </div>
    </div>

    <!-- Modal Foto Preview -->
    <div id="modalFotoPreview" class="modal">
        <div class="modal-content">
            <img id="previewFoto" src="" style="width:100%;border-radius:8px">
            <div style="text-align:right;margin-top:8px">
                <button class="btn" onclick="closeFotoPreview()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        // Store authenticated user data in localStorage for compatibility with existing JavaScript
        @auth
        const authUser = @json(Auth::user());
        const currentUser = {
            nama: authUser.name,
            email: authUser.email,
            nim: authUser.nim
        };
        localStorage.setItem('isLoggedIn', 'true');
        localStorage.setItem('loggedUser', JSON.stringify(currentUser));
        @endauth

        // API Helper Function to fetch all items
        async function fetchAllItems() {
            try {
                const response = await fetch('{{ route('items.index') }}', {
                    headers: {
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();
                if (data.success) {
                    // Also sync to localStorage for compatibility
                    localStorage.setItem('items', JSON.stringify(data.items));
                    return data.items;
                }
                return [];
            } catch (error) {
                console.error('Error fetching items:', error);
                return [];
            }
        }

        // Render items on dashboard
        async function renderBarangFromBackend() {
            const list = document.getElementById('barangDashboard');
            const items = await fetchAllItems();
            const dipinjam = JSON.parse(localStorage.getItem('barangDipinjam') || '[]');
            const forms = JSON.parse(localStorage.getItem('formData') || '[]');

            list.innerHTML = '';

            if (!items.length) {
                list.innerHTML = "<p class='muted'>Belum ada barang yang diupload.</p>";
                return;
            }

            items.forEach(item => {
                const isMine = item.ownerEmail === currentUser.email;
                const stok = item.stok || 0;
                const borrowedEntry = dipinjam.find(d => d.itemId === item.id && d.borrowerEmail === currentUser
                    .email);
                const waiting = forms.find(f => f.idBarang === item.id && f.borrowerEmail === currentUser
                    .email && f.status === 'menunggu');

                let buttonHtml = '';
                if (isMine) {
                    buttonHtml =
                        `<button class="btn small danger" onclick="hapusBarang(${item.id})">Hapus Barang</button>`;
                } else if (borrowedEntry) {
                    buttonHtml = `<button class="btn" onclick="requestReturn(${item.id})">Kembalikan</button>`;
                } else if (waiting) {
                    buttonHtml = `<button class="btn gray" disabled>Menunggu Persetujuan Pemilik</button>`;
                } else if (stok <= 0) {
                    buttonHtml = `<button class="btn gray" disabled>Stok Habis</button>`;
                } else {
                    buttonHtml = `<button class="btn" onclick="openBorrowModal(${item.id})">Pinjam</button>`;
                }

                list.innerHTML += `
                    <div class="card">
                        <img src="${item.gambar}" alt="${item.nama}">
                        <h4>${item.nama}</h4>
                        <p class="small">Pemilik: ${item.ownerNama} ${isMine ? '<strong>(Barang Saya)</strong>' : ''}</p>
                        <p>Stok: ${stok}</p>
                        ${buttonHtml}
                    </div>
                `;
            });
        }

        // Delete item handler for dashboard
        window.hapusBarang = async function(id) {
            if (!confirm('Yakin ingin menghapus barang ini?')) return;

            try {
                const response = await fetch(`/api/items/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    renderBarangFromBackend();
                } else {
                    alert('Gagal menghapus barang: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus barang');
            }
        };

        // init page-specific
        document.addEventListener('DOMContentLoaded', () => {
            // Render items from backend
            renderBarangFromBackend();

            // If initDashboard exists from app.js for other features (modals, etc), call it
            if (typeof initDashboard === 'function') initDashboard();
        });

        // listen storage updates from other tabs and refresh render functions if present
        window.addEventListener('storage', (e) => {
            if (e.key === 'lastUpdate') {
                renderBarangFromBackend();
                if (typeof renderBarang === 'function') renderBarang();
            }
        });
    </script>
@endsection
