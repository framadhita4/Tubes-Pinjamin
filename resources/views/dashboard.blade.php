@extends('layouts.app')

@section('title', 'Dashboard - PinjamIn')

@section('content')
    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="card bg-linear-to-r from-primary to-primary/50 text-primary-content">
            <div class="card-body">
                <h2 class="card-title text-2xl">Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
                <p>Temukan barang yang ingin Anda pinjam atau kelola barang Anda sendiri.</p>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex gap-4">
                    <input type="text" id="searchInput" placeholder="Cari barang..." class="input input-bordered w-full" />
                    <button class="btn btn-primary" onclick="searchItems()">
                        <i data-lucide="search" class="w-5 h-5"></i>
                        Cari
                    </button>
                </div>
            </div>
        </div>

        @if (Auth::user()->role === 'peminjam')
            <!-- My Borrowings Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat bg-base-100 shadow rounded-lg">
                    <div class="stat-figure text-warning">
                        <i data-lucide="clock" class="w-8 h-8"></i>
                    </div>
                    <div class="stat-title">Menunggu Persetujuan</div>
                    <div class="stat-value text-warning" id="pending-count">0</div>
                    <div class="stat-desc">Permintaan peminjaman</div>
                </div>

                <div class="stat bg-base-100 shadow rounded-lg">
                    <div class="stat-figure text-success">
                        <i data-lucide="check-circle" class="w-8 h-8"></i>
                    </div>
                    <div class="stat-title">Sedang Dipinjam</div>
                    <div class="stat-value text-success" id="active-count">0</div>
                    <div class="stat-desc">Barang aktif</div>
                </div>

                <div class="stat bg-base-100 shadow rounded-lg">
                    <div class="stat-figure text-info">
                        <i data-lucide="file-text" class="w-8 h-8"></i>
                    </div>
                    <div class="stat-title">Riwayat</div>
                    <div class="stat-value text-info" id="history-count">0</div>
                    <div class="stat-desc">Total peminjaman</div>
                </div>
            </div>
        @endif

        <!-- Items Grid -->
        <div>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold">Barang Tersedia</h3>
                <div class="badge badge-lg badge-primary" id="items-count">0 Barang</div>
            </div>

            <div id="items-loading" class="text-center py-12">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="mt-4">Memuat barang...</p>
            </div>

            <div id="items-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                style="display: none;">
                <!-- Items will be loaded here -->
            </div>

            <div id="items-empty" class="text-center py-12" style="display: none;">
                <i data-lucide="package" class="w-24 h-24 mx-auto text-base-content/20"></i>
                <p class="mt-4 text-lg font-semibold">Tidak ada barang tersedia</p>
                <p class="text-base-content/70">Coba lagi nanti</p>
            </div>
        </div>
    </div>

    <!-- Borrowing Request Modal -->
    <dialog id="borrowing_modal" class="modal">
        <div class="modal-box max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Form Peminjaman</h3>
            <form id="borrowingForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="item_id" name="item_id">

                <div class="space-y-4">
                    <!-- Item Info -->
                    <div class="alert alert-info">
                        <i data-lucide="info" class="w-6 h-6"></i>
                        <div>
                            <h4 class="font-bold" id="modal-item-name"></h4>
                            <p class="text-sm" id="modal-item-owner"></p>
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="form-control flex flex-col">
                        <label class="label">
                            <span class="label-text font-medium">Lama Peminjaman</span>
                        </label>
                        <select name="lama_hari" id="lama_hari" class="select select-bordered w-full" required>
                            <option value="">Pilih durasi...</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="form-control flex flex-col">
                        <label class="label">
                            <span class="label-text font-medium">Catatan / Alasan Peminjaman</span>
                        </label>
                        <textarea name="catatan" class="textarea textarea-bordered h-24 w-full"
                            placeholder="Jelaskan untuk apa Anda meminjam barang ini..." required></textarea>
                    </div>

                    <!-- KTM Photo Upload -->
                    <div class="form-control flex flex-col">
                        <label class="label">
                            <span class="label-text font-medium">Upload Foto KTM/Identitas</span>
                            <span class="label-text-alt text-error">*Wajib</span>
                        </label>
                        <input type="file" name="foto_ktm" accept="image/*" class="file-input file-input-bordered w-full"
                            required />
                        <label class="label">
                            <span class="label-text-alt">Maksimal 2MB (JPG, PNG)</span>
                        </label>
                    </div>

                    <!-- Agreement -->
                    <div class="form-control flex flex-col">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="checkbox checkbox-primary" required />
                            <span class="label-text">Saya bertanggung jawab atas barang yang dipinjam</span>
                        </label>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" onclick="borrowing_modal.close()">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="send" class="w-5 h-5"></i>
                        Kirim Permintaan
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        let allItems = [];
        const userRole = '{{ Auth::user()->role }}';

        document.addEventListener('DOMContentLoaded', function() {
            loadItems();
            if (userRole === 'peminjam') {
                loadBorrowingStats();
            }

            // Handle borrowing form submission
            document.getElementById('borrowingForm').addEventListener('submit', handleBorrowingSubmit);

            // Search on Enter key
            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchItems();
                }
            });
        });

        async function loadItems() {
            try {
                const response = await fetch('{{ route('items.index') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();

                if (data.success) {
                    allItems = data.items;
                    displayItems(allItems);
                }
            } catch (error) {
                console.error('Error loading items:', error);
                document.getElementById('items-loading').style.display = 'none';
                document.getElementById('items-empty').style.display = 'block';
            }
        }

        function displayItems(items) {
            const container = document.getElementById('items-grid');
            const loading = document.getElementById('items-loading');
            const empty = document.getElementById('items-empty');
            const countBadge = document.getElementById('items-count');

            loading.style.display = 'none';

            if (items.length === 0) {
                empty.style.display = 'block';
                container.style.display = 'none';
                countBadge.textContent = '0 Barang';
                return;
            }

            empty.style.display = 'none';
            container.style.display = 'grid';
            countBadge.textContent = `${items.length} Barang`;

            container.innerHTML = items.map(item => {
                const isOwn = item.user_id === {{ Auth::id() }};
                const isAvailable = item.isAvailable && item.stok > 0;

                return `
                    <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow">
                        <a href="${item.gambar}" target="_blank">
                            <figure class="h-48 overflow-hidden">
                                <img src="${item.gambar}" alt="${item.nama}" class="w-full h-full object-cover" />
                            </figure>
                        </a>
                        <div class="card-body">
                            <h2 class="card-title">
                                ${item.nama}
                                ${isOwn ? '<div class="badge badge-secondary">Milik Saya</div>' : ''}
                            </h2>
                            <p class="text-sm text-base-content/70">${item.deskripsi || 'Tidak ada deskripsi'}</p>
                            <div class="divider my-2"></div>
                            <div class="flex justify-between text-sm">
                                <span><strong>Pemilik:</strong> ${item.ownerNama}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span><strong>Stok:</strong> ${item.stok}</span>
                                <span><strong>Max:</strong> ${item.maxHari} hari</span>
                            </div>
                            <div class="card-actions justify-end mt-4">
                                ${isOwn ? 
                                    '<button class="btn btn-sm btn-ghost" disabled>Barang Anda</button>' :
                                    isAvailable ?
                                    `<button class="btn btn-sm btn-primary" onclick="openBorrowingModal(${item.id})">
                                                                                                                <i data-lucide="plus" class="w-4 h-4"></i>
                                                                                                                Pinjam
                                                                                                            </button>` :
                                    '<button class="btn btn-sm btn-disabled">Stok Habis</button>'
                                }
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function searchItems() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const filtered = allItems.filter(item =>
                item.nama.toLowerCase().includes(query) ||
                (item.deskripsi && item.deskripsi.toLowerCase().includes(query))
            );
            displayItems(filtered);
        }

        function openBorrowingModal(itemId) {
            const item = allItems.find(i => i.id === itemId);
            if (!item) return;

            document.getElementById('item_id').value = itemId;
            document.getElementById('modal-item-name').textContent = item.nama;
            document.getElementById('modal-item-owner').textContent = `Pemilik: ${item.ownerNama}`;

            // Populate duration options
            const select = document.getElementById('lama_hari');
            select.innerHTML = '<option value="">Pilih durasi...</option>';
            for (let i = 1; i <= item.maxHari; i++) {
                select.innerHTML += `<option value="${i}">${i} hari</option>`;
            }

            borrowing_modal.showModal();
        }

        async function handleBorrowingSubmit(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');

            window.setButtonLoading(submitBtn, true, 'Mengirim...');

            try {
                const response = await fetch('{{ route('borrowings.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    window.showToast(data.message, 'success');
                    borrowing_modal.close();
                    e.target.reset();
                    loadItems();
                    if (userRole === 'peminjam') {
                        loadBorrowingStats();
                    }
                } else {
                    window.showToast(data.message, 'error');
                }
            } catch (error) {
                console.error('Error submitting borrowing request:', error);
                window.showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
            } finally {
                window.setButtonLoading(submitBtn, false);
            }
        }

        async function loadBorrowingStats() {
            try {
                const response = await fetch('{{ route('borrowings.index') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();

                if (data.success) {
                    const borrowings = data.data;
                    const pending = borrowings.filter(b => b.status === 'pending').length;
                    const active = borrowings.filter(b => b.status === 'approved').length;
                    const history = borrowings.filter(b => ['returned', 'rejected', 'cancelled'].includes(b.status))
                        .length;

                    document.getElementById('pending-count').textContent = pending;
                    document.getElementById('active-count').textContent = active;
                    document.getElementById('history-count').textContent = history;
                }
            } catch (error) {
                console.error('Error loading borrowing stats:', error);
            }
        }
    </script>
@endpush
