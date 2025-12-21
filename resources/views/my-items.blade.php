@extends('layouts.app')

@section('title', 'Barang Saya - PinjamIn')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold mb-2">Barang Saya</h1>
                <p class="text-base-content/70">Kelola barang yang Anda sewakan</p>
            </div>
            <a href="{{ route('upload') }}" class="btn btn-primary">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Tambah Barang
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-figure text-primary">
                    <i data-lucide="package" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Total Barang</div>
                <div class="stat-value text-primary" id="total-items">0</div>
                <div class="stat-desc">Barang yang terdaftar</div>
            </div>

            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-figure text-success">
                    <i data-lucide="check-circle" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Tersedia</div>
                <div class="stat-value text-success" id="available-items">0</div>
                <div class="stat-desc">Siap dipinjam</div>
            </div>

            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-figure text-warning">
                    <i data-lucide="clock" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Sedang Dipinjam</div>
                <div class="stat-value text-warning" id="borrowed-items">0</div>
                <div class="stat-desc">Barang aktif</div>
            </div>
        </div>

        <!-- Items List -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Daftar Barang</h2>

                <!-- Loading -->
                <div id="items-loading" class="text-center py-12">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                    <p class="mt-4">Memuat barang...</p>
                </div>

                <!-- Items Table -->
                <div id="items-table" class="overflow-x-auto" style="display: none;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama Barang</th>
                                <th>Stok</th>
                                <th>Max Hari</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <!-- Items will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="items-empty" class="text-center py-12" style="display: none;">
                    <i data-lucide="package-open" class="w-24 h-24 mx-auto text-base-content/20"></i>
                    <p class="text-lg font-semibold mt-4">Belum ada barang</p>
                    <p class="text-base-content/70 mt-2">Tambahkan barang pertama Anda untuk mulai menyewakan</p>
                    <a href="{{ route('upload') }}" class="btn btn-primary mt-4">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        Tambah Barang
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <dialog id="edit_modal" class="modal">
        <div class="modal-box max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Edit Barang</h3>
            <form id="editForm">
                @csrf
                <input type="hidden" id="edit_item_id" />

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Nama Barang</span>
                    </label>
                    <input type="text" id="edit_nama" name="nama" class="input input-bordered w-full" required />
                </div>

                <div class="form-control mt-4 flex flex-col">
                    <label class="label">
                        <span class="label-text font-medium">Deskripsi</span>
                    </label>
                    <textarea id="edit_deskripsi" name="deskripsi" class="textarea textarea-bordered h-24 w-full"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Stok</span>
                        </label>
                        <input type="number" id="edit_stok" name="stok" class="input input-bordered" required />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Max Hari</span>
                        </label>
                        <input type="number" id="edit_max_hari" name="max_hari" class="input input-bordered" required />
                    </div>
                </div>

                <div class="form-control mt-4 flex flex-col">
                    <label class="label">
                        <span class="label-text font-medium">Ganti Foto (opsional)</span>
                    </label>
                    <input type="file" name="gambar" accept="image/*" class="file-input file-input-bordered" />
                </div>

                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" onclick="edit_modal.close()">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="check" class="w-5 h-5"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Delete Confirmation Modal -->
    <dialog id="delete_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Konfirmasi Hapus</h3>
            <p class="py-4">Apakah Anda yakin ingin menghapus barang <span id="delete-item-name"
                    class="font-semibold"></span>?</p>
            <p class="text-sm text-error">Tindakan ini tidak dapat dibatalkan.</p>
            <div class="modal-action">
                <button class="btn btn-ghost" onclick="delete_modal.close()">Batal</button>
                <button class="btn btn-error" onclick="confirmDelete()">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                    Hapus
                </button>
            </div>
        </div>
    </dialog>

    <script>
        let myItems = [];
        let deleteItemId = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadMyItems();

            // Edit form submission
            document.getElementById('editForm').addEventListener('submit', handleEditSubmit);

            // Initialize Lucide icons
            setTimeout(() => {
                if (window.lucide && window.lucide.createIcons) {
                    window.lucide.createIcons();
                }
            }, 100);
        });

        async function loadMyItems() {
            try {
                const response = await window.fetchRequest('{{ route('items.my') }}', {
                    method: 'GET'
                });

                if (response.success) {
                    myItems = response.data.items;
                    displayItems(myItems);
                    updateStats(myItems);
                }
            } catch (error) {
                console.error('Error loading items:', error);
                document.getElementById('items-loading').style.display = 'none';
                document.getElementById('items-empty').style.display = 'block';
            }
        }

        function displayItems(items) {
            const loading = document.getElementById('items-loading');
            const table = document.getElementById('items-table');
            const empty = document.getElementById('items-empty');
            const tbody = document.getElementById('items-tbody');

            loading.style.display = 'none';

            if (items.length === 0) {
                table.style.display = 'none';
                empty.style.display = 'block';
                return;
            }

            empty.style.display = 'none';
            table.style.display = 'block';

            tbody.innerHTML = items.map(item => {
                const isAvailable = item.stok > 0;
                const statusBadge = isAvailable ?
                    '<span class="badge badge-success">Tersedia</span>' :
                    '<span class="badge badge-error">Habis</span>';

                return `
                    <tr>
                        <td>
                            <div class="avatar">
                                <div class="mask mask-squircle w-12 h-12">
                                    <img src="/storage/items/${item.gambar}" alt="${item.nama}" />
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="font-bold">${item.nama}</div>
                            <div class="text-sm opacity-50">${item.deskripsi ? item.deskripsi.substring(0, 50) + '...' : '-'}</div>
                        </td>
                        <td>${item.stok}</td>
                        <td>${item.maxHari} hari</td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="flex gap-2">
                                <button class="btn btn-sm btn-soft" onclick="openEditModal(${item.id})" title="Edit">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-soft btn-error" onclick="openDeleteModal(${item.id}, '${item.nama}')" title="Hapus">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            // Re-initialize Lucide icons for dynamic content
            setTimeout(() => {
                if (window.lucide && window.lucide.createIcons) {
                    window.lucide.createIcons();
                }
            }, 1000);
        }

        function updateStats(items) {
            const total = items.length;
            const available = items.filter(item => item.stok > 0).length;
            // For borrowed count, we would need to check active borrowings
            // For now, just showing total - available as a rough estimate
            const borrowed = total - available;

            document.getElementById('total-items').textContent = total;
            document.getElementById('available-items').textContent = available;
            document.getElementById('borrowed-items').textContent = borrowed;
        }

        function openEditModal(itemId) {
            const item = myItems.find(i => i.id === itemId);
            if (!item) return;

            document.getElementById('edit_item_id').value = itemId;
            document.getElementById('edit_nama').value = item.nama;
            document.getElementById('edit_deskripsi').value = item.deskripsi || '';
            document.getElementById('edit_stok').value = item.stok;
            document.getElementById('edit_max_hari').value = item.maxHari;

            edit_modal.showModal();
        }

        async function handleEditSubmit(e) {
            e.preventDefault();

            const itemId = document.getElementById('edit_item_id').value;
            const formData = new FormData(e.target);
            formData.append('_method', 'POST');

            const submitBtn = e.target.querySelector('button[type="submit"]');
            window.setButtonLoading(submitBtn, true, 'Menyimpan...');

            try {
                const response = await window.fetchRequest(`{{ route('items.update', '_id_') }}`.replace('_id_',
                    itemId), {
                    method: 'POST',
                    body: formData
                });

                if (response.success) {
                    edit_modal.close();
                    loadMyItems();
                }
            } catch (error) {
                console.error('Update error:', error);
            } finally {
                window.setButtonLoading(submitBtn, false);
            }
        }

        function openDeleteModal(itemId, itemName) {
            deleteItemId = itemId;
            document.getElementById('delete-item-name').textContent = itemName;
            delete_modal.showModal();
        }

        async function confirmDelete() {
            if (!deleteItemId) return;

            try {
                const response = await window.fetchRequest(`/items/${deleteItemId}`, {
                    method: 'DELETE'
                });

                if (response.success) {
                    delete_modal.close();
                    deleteItemId = null;
                    loadMyItems();
                }
            } catch (error) {
                console.error('Delete error:', error);
            }
        }
    </script>
@endsection
