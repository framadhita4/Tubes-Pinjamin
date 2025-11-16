@extends('layouts.app')

@section('title', 'Panel Pemilik')

@section('header-buttons')
    <h2 style="margin-right: 1rem;">📋 Panel Pemilik</h2>
    <button class="btn small" onclick="window.location='{{ route('dashboard') }}'">⬅ Dashboard</button>
    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
        @csrf
        <button class="btn small btn-danger" type="submit">Logout</button>
    </form>
@endsection

@section('content')
    <section style="margin-bottom:12px">
        <h3>🔔 Notifikasi</h3>
        <div id="notifArea" class="card muted">Memuat...</div>
    </section>

    <section class="table-section" style="margin-bottom:12px">
        <h3>📦 Barang Milik Saya</h3>
        <div class="table-wrap">
            <table id="tableMyItems" class="styled-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Stok</th>
                        <th>Max Hari</th>
                        <th>Deskripsi</th>
                        <th>Foto</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <section class="table-section" style="margin-bottom:12px">
        <h3>📋 Permintaan Peminjaman</h3>
        <div class="table-wrap">
            <table id="tableActive" class="styled-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Barang</th>
                        <th>Hari</th>
                        <th>Tanggal</th>
                        <th>KTM</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <section class="table-section" style="margin-bottom:12px">
        <h3>🔁 Permintaan Pengembalian</h3>
        <div class="table-wrap">
            <table id="tablePendingReturns" class="styled-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Barang</th>
                        <th>Kondisi</th>
                        <th>Foto</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <section class="table-section">
        <h3>📚 Riwayat</h3>
        <div class="table-wrap">
            <table id="tableHistory" class="styled-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Barang</th>
                        <th>Lama</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Kondisi</th>
                        <th>Foto</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <div style="display:flex;justify-content:center;margin-top:12px">
        <button id="btnOpenUpload" class="btn">+ Upload Barang</button>
    </div>
    </main>

    <!-- Modal Upload Barang -->
    <div id="modalUpload" class="modal">
        <div class="modal-content">
            <h3>Upload Barang Baru</h3>
            <form id="formUploadModal" class="form-grid" onsubmit="event.preventDefault(); uploadItem();">
                <input id="itemName" placeholder="Nama Barang" required />
                <input id="itemStok" type="number" min="1" value="1" required />
                <textarea id="itemDeskripsi" placeholder="Deskripsi" required></textarea>
                <label>Foto Barang (wajib)</label>
                <input id="itemFoto" type="file" accept="image/*" required />
                <label>Max Lama Peminjaman</label>
                <select id="itemMaxHari" required>
                    <option value="1">1 Hari</option>
                    <option value="3">3 Hari</option>
                    <option value="7">7 Hari</option>
                    <option value="14">14 Hari</option>
                    <option value="30">30 Hari</option>
                </select>
                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px">
                    <button class="btn" type="submit">Upload & Simpan</button>
                    <button class="btn outline" type="button" onclick="closeUploadModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Foto Preview -->
    <div id="modalFotoPreview" class="modal">
        <div class="modal-content">
            <img id="previewFoto" src="" style="width:100%;border-radius:8px">
            <div style="text-align:right;margin-top:8px"><button class="btn" onclick="closeFotoPreview()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        // Store authenticated user data in localStorage for compatibility with existing JavaScript
        @auth
        const authUser = @json(Auth::user());
        localStorage.setItem('isLoggedIn', 'true');
        localStorage.setItem('loggedUser', JSON.stringify({
            nama: authUser.name,
            email: authUser.email,
            nim: authUser.nim
        }));
        @endauth

        // API Helper Functions
        async function fetchMyItems() {
            try {
                const response = await fetch('{{ route('items.my') }}', {
                    headers: {
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();
                return data.success ? data.items : [];
            } catch (error) {
                console.error('Error fetching items:', error);
                return [];
            }
        }

        async function deleteItem(id) {
            try {
                const response = await fetch(`/api/items/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error deleting item:', error);
                return {
                    success: false,
                    message: 'Error deleting item'
                };
            }
        }

        async function createItem(itemData) {
            try {
                const response = await fetch('{{ route('items.store') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(itemData)
                });
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error creating item:', error);
                return {
                    success: false,
                    message: 'Error creating item'
                };
            }
        }

        // Render my items table
        async function renderMyItems() {
            const tbody = document.querySelector('#tableMyItems tbody');
            const items = await fetchMyItems();

            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">Belum ada barang diupload</td></tr>';
                return;
            }

            tbody.innerHTML = items.map(item => `
                <tr>
                    <td>${item.nama}</td>
                    <td>${item.stok}</td>
                    <td>${item.maxHari}</td>
                    <td>${item.deskripsi || '-'}</td>
                    <td><img src="${item.gambar}" style="max-width:70px;border-radius:6px;cursor:pointer" onclick="openFotoPreview('${item.gambar}')"></td>
                    <td><button class="btn small danger" onclick="hapusBarangBackend(${item.id})">Hapus</button></td>
                </tr>
            `).join('');
        }

        // Delete item handler
        window.hapusBarangBackend = async function(id) {
            if (!confirm('Yakin ingin menghapus barang ini?')) return;

            const result = await deleteItem(id);
            if (result.success) {
                alert(result.message);
                renderMyItems();
                // Also update localStorage for compatibility
                let localItems = JSON.parse(localStorage.getItem('items') || '[]');
                localItems = localItems.filter(item => item.id !== id);
                localStorage.setItem('items', JSON.stringify(localItems));
            } else {
                alert('Gagal menghapus barang: ' + result.message);
            }
        };

        // Upload item handler
        window.uploadItem = function() {
            const name = document.getElementById('itemName').value.trim();
            const stok = Number(document.getElementById('itemStok').value) || 0;
            const deskripsi = document.getElementById('itemDeskripsi').value.trim() || '';
            const file = document.getElementById('itemFoto').files[0];
            const maxHari = Number(document.getElementById('itemMaxHari').value) || 7;

            if (!name || stok <= 0 || !file) {
                alert('Lengkapi nama, stok, dan foto!');
                return;
            }

            const reader = new FileReader();
            reader.onload = async (e) => {
                const foto = e.target.result;

                const result = await createItem({
                    nama: name,
                    stok: stok,
                    deskripsi: deskripsi,
                    max_hari: maxHari,
                    gambar: foto
                });

                if (result.success) {
                    alert(result.message);
                    closeUploadModal();
                    renderMyItems();

                    // Also update localStorage for compatibility
                    let localItems = JSON.parse(localStorage.getItem('items') || '[]');
                    localItems.push({
                        id: result.item.id,
                        nama: result.item.nama,
                        stok: result.item.stok,
                        deskripsi: result.item.deskripsi,
                        maxHari: result.item.maxHari,
                        gambar: result.item.gambar,
                        ownerNama: result.item.ownerNama,
                        ownerEmail: result.item.ownerEmail
                    });
                    localStorage.setItem('items', JSON.stringify(localItems));
                } else {
                    alert('Gagal mengupload barang: ' + result.message);
                }
            };
            reader.readAsDataURL(file);
        };

        window.closeUploadModal = function() {
            document.getElementById('modalUpload').style.display = 'none';
            document.getElementById('formUploadModal').reset();
        };

        window.openFotoPreview = function(src) {
            document.getElementById('previewFoto').src = src;
            document.getElementById('modalFotoPreview').style.display = 'flex';
        };

        window.closeFotoPreview = function() {
            document.getElementById('modalFotoPreview').style.display = 'none';
            document.getElementById('previewFoto').src = '';
        };

        document.addEventListener('DOMContentLoaded', () => {
            // open upload modal
            const btnOpen = document.getElementById('btnOpenUpload');
            btnOpen.addEventListener('click', () => document.getElementById('modalUpload').style.display = 'flex');

            // close on outside click
            window.addEventListener('click', (e) => {
                if (e.target === document.getElementById('modalUpload')) closeUploadModal();
                if (e.target === document.getElementById('modalFotoPreview')) closeFotoPreview();
            });

            // Initial render
            renderMyItems();

            // If initPanel exists from app.js, call it for other functionality
            if (typeof initPanel === 'function') initPanel();
        });

        // listen storage updates from other tabs
        window.addEventListener('storage', (e) => {
            if (e.key === 'lastUpdate') {
                renderMyItems();
                if (typeof renderPanel === 'function') renderPanel();
            }
        });
    </script>
@endsection
