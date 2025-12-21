@extends('layouts.app')

@section('title', 'Peminjaman Saya - PinjamIn')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold mb-2">Peminjaman Saya</h1>
            <p class="text-base-content/70">Kelola peminjaman dan pengembalian barang Anda</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-figure text-warning">
                    <i data-lucide="clock" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Menunggu Persetujuan</div>
                <div class="stat-value text-warning" id="pending-count">0</div>
                <div class="stat-desc">Permintaan baru</div>
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
                    <i data-lucide="rotate-ccw" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Menunggu Verifikasi</div>
                <div class="stat-value text-info" id="return-pending-count">0</div>
                <div class="stat-desc">Pengembalian</div>
            </div>

            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-figure text-base-content">
                    <i data-lucide="archive" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Selesai</div>
                <div class="stat-value" id="completed-count">0</div>
                <div class="stat-desc">Total riwayat</div>
            </div>
        </div>

        <!-- Tabs -->
        <div role="tablist" class="tabs tabs-boxed bg-base-100 shadow">
            <a role="tab" class="tab tab-active" onclick="switchTab('pending')" id="tab-pending">
                <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
                Menunggu Persetujuan
                <span class="badge badge-sm ml-2" id="tab-badge-pending">0</span>
            </a>
            <a role="tab" class="tab" onclick="switchTab('active')" id="tab-active">
                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                Sedang Dipinjam
                <span class="badge badge-sm ml-2" id="tab-badge-active">0</span>
            </a>
            <a role="tab" class="tab" onclick="switchTab('return-pending')" id="tab-return-pending">
                <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i>
                Menunggu Verifikasi
                <span class="badge badge-sm ml-2" id="tab-badge-return-pending">0</span>
            </a>
            <a role="tab" class="tab" onclick="switchTab('history')" id="tab-history">
                <i data-lucide="archive" class="w-4 h-4 mr-2"></i>
                Riwayat
                <span class="badge badge-sm ml-2" id="tab-badge-history">0</span>
            </a>
        </div>

        <!-- Content Cards -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <!-- Loading -->
                <div id="borrowings-loading" class="text-center py-12">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                    <p class="mt-4">Memuat data...</p>
                </div>

                <!-- Content Container -->
                <div id="borrowings-content" style="display: none;">
                    <!-- Pending Requests -->
                    <div id="content-pending">
                        <h2 class="text-xl font-bold mb-4">Permintaan Menunggu Persetujuan</h2>
                        <div id="list-pending" class="space-y-4">
                            <!-- Will be populated by JS -->
                        </div>
                    </div>

                    <!-- Active Borrowings -->
                    <div id="content-active" style="display: none;">
                        <h2 class="text-xl font-bold mb-4">Barang Sedang Dipinjam</h2>
                        <div id="list-active" class="space-y-4">
                            <!-- Will be populated by JS -->
                        </div>
                    </div>

                    <!-- Return Pending -->
                    <div id="content-return-pending" style="display: none;">
                        <h2 class="text-xl font-bold mb-4">Menunggu Verifikasi Pengembalian</h2>
                        <div id="list-return-pending" class="space-y-4">
                            <!-- Will be populated by JS -->
                        </div>
                    </div>

                    <!-- History -->
                    <div id="content-history" style="display: none;">
                        <h2 class="text-xl font-bold mb-4">Riwayat Peminjaman</h2>
                        <div id="list-history" class="space-y-4">
                            <!-- Will be populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="borrowings-empty" class="text-center py-12" style="display: none;">
                    <i data-lucide="inbox" class="w-24 h-24 mx-auto text-base-content/20"></i>
                    <p class="text-lg font-semibold mt-4">Tidak ada data</p>
                    <p class="text-base-content/70 mt-2">Belum ada peminjaman untuk kategori ini</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Request Modal -->
    <dialog id="return_modal" class="modal">
        <div class="modal-box max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Form Pengembalian Barang</h3>
            <form id="returnForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="return_borrowing_id">

                <div class="space-y-4">
                    <!-- Item Info -->
                    <div class="alert alert-info">
                        <i data-lucide="info" class="w-6 h-6"></i>
                        <div>
                            <h4 class="font-bold" id="return-item-name"></h4>
                            <p class="text-sm" id="return-item-owner"></p>
                        </div>
                    </div>

                    <!-- Condition Description -->
                    <div class="form-control flex flex-col">
                        <label class="label">
                            <span class="label-text font-medium">Kondisi Barang</span>
                            <span class="label-text-alt text-error">*Wajib</span>
                        </label>
                        <textarea name="kondisi" id="kondisi" class="textarea textarea-bordered h-24 w-full"
                            placeholder="Jelaskan kondisi barang saat dikembalikan..." required></textarea>
                        <label class="label">
                            <span class="label-text-alt">Contoh: Barang dalam kondisi baik, tidak ada kerusakan</span>
                        </label>
                    </div>

                    <!-- Condition Photo Upload -->
                    <div class="form-control flex flex-col">
                        <label class="label">
                            <span class="label-text font-medium">Upload Foto Kondisi Barang</span>
                            <span class="label-text-alt text-error">*Wajib</span>
                        </label>
                        <input type="file" name="foto_kondisi" id="foto_kondisi" accept="image/*"
                            class="file-input file-input-bordered w-full" required />
                        <label class="label">
                            <span class="label-text-alt">Maksimal 2MB (JPG, PNG). Foto untuk verifikasi kondisi</span>
                        </label>
                    </div>

                    <!-- Preview -->
                    <div id="photo-preview" class="hidden">
                        <label class="label">
                            <span class="label-text font-medium">Preview Foto</span>
                        </label>
                        <img id="preview-img" src="" alt="Preview" class="max-h-48 rounded-lg" />
                    </div>

                    <!-- Warning -->
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        <span class="text-sm">Pastikan foto dan deskripsi kondisi sesuai dengan kondisi barang yang
                            sebenarnya. Pemilik akan memverifikasi sebelum pengembalian disetujui.</span>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" onclick="return_modal.close()">Batal</button>
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

    <!-- Cancel Confirmation Modal -->
    <dialog id="cancel_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Batalkan Peminjaman</h3>
            <p class="py-4">Apakah Anda yakin ingin membatalkan permintaan peminjaman ini?</p>
            <div class="alert alert-warning">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                <span class="text-sm">Tindakan ini tidak dapat dibatalkan</span>
            </div>
            <div class="modal-action">
                <button class="btn btn-ghost" onclick="cancel_modal.close()">Tidak</button>
                <button class="btn btn-error" onclick="confirmCancel()">
                    <i data-lucide="x" class="w-5 h-5"></i>
                    Ya, Batalkan
                </button>
            </div>
        </div>
    </dialog>

    <!-- Detail Modal -->
    <dialog id="detail_modal" class="modal">
        <div class="modal-box max-w-3xl">
            <h3 class="font-bold text-lg mb-4">Detail Peminjaman</h3>
            <div id="detail-content">
                <!-- Will be populated by JS -->
            </div>
            <div class="modal-action">
                <button class="btn" onclick="detail_modal.close()">Tutup</button>
            </div>
        </div>
    </dialog>

    <!-- Image Preview Modal -->
    <dialog id="image_modal" class="modal">
        <div class="modal-box max-w-4xl">
            <h3 class="font-bold text-lg mb-4" id="image-modal-title">Preview Gambar</h3>
            <div class="flex justify-center">
                <img id="preview-image" src="" alt="Preview" class="max-h-96 rounded-lg" />
            </div>
            <div class="modal-action">
                <button class="btn" onclick="image_modal.close()">Tutup</button>
            </div>
        </div>
    </dialog>

    <script>
        let allBorrowings = [];
        let currentTab = 'pending';
        let actionBorrowingId = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadBorrowings();

            // Handle return form submission
            document.getElementById('returnForm').addEventListener('submit', handleReturnSubmit);

            // Photo preview
            document.getElementById('foto_kondisi').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('preview-img').src = e.target.result;
                        document.getElementById('photo-preview').classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Reinitialize icons
            setTimeout(() => {
                if (window.lucide && window.lucide.createIcons) {
                    window.lucide.createIcons();
                }
            }, 100);
        });

        async function loadBorrowings() {
            try {
                const response = await window.fetchRequest('{{ route('borrowings.index') }}', {
                    method: 'GET'
                });

                if (response.success) {
                    allBorrowings = response.data.data || response.data || [];
                    console.log('Loaded borrowings:', allBorrowings);
                    updateStats();
                    displayBorrowings();

                    document.getElementById('borrowings-loading').style.display = 'none';
                    document.getElementById('borrowings-content').style.display = 'block';
                }
            } catch (error) {
                console.error('Error loading borrowings:', error);
                document.getElementById('borrowings-loading').style.display = 'none';
                document.getElementById('borrowings-empty').style.display = 'block';
            }
        }

        function updateStats() {
            const pending = allBorrowings.filter(b => b.status === 'pending').length;
            const active = allBorrowings.filter(b => b.status === 'approved').length;
            const returnPending = allBorrowings.filter(b => b.status === 'return_pending').length;
            const completed = allBorrowings.filter(b => ['returned', 'rejected', 'cancelled'].includes(b.status))
                .length;

            // Update stat cards
            document.getElementById('pending-count').textContent = pending;
            document.getElementById('active-count').textContent = active;
            document.getElementById('return-pending-count').textContent = returnPending;
            document.getElementById('completed-count').textContent = completed;

            // Update tab badges
            document.getElementById('tab-badge-pending').textContent = pending;
            document.getElementById('tab-badge-active').textContent = active;
            document.getElementById('tab-badge-return-pending').textContent = returnPending;
            document.getElementById('tab-badge-history').textContent = completed;
        }

        function switchTab(tab) {
            currentTab = tab;

            // Update tab active state
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('tab-active'));
            document.getElementById(`tab-${tab}`).classList.add('tab-active');

            // Hide all content
            ['pending', 'active', 'return-pending', 'history'].forEach(t => {
                document.getElementById(`content-${t}`).style.display = 'none';
            });

            // Show selected content
            document.getElementById(`content-${tab}`).style.display = 'block';
            displayBorrowings();
        }

        function displayBorrowings() {
            let filtered = [];

            switch (currentTab) {
                case 'pending':
                    filtered = allBorrowings.filter(b => b.status === 'pending');
                    break;
                case 'active':
                    filtered = allBorrowings.filter(b => b.status === 'approved');
                    break;
                case 'return-pending':
                    filtered = allBorrowings.filter(b => b.status === 'return_pending');
                    break;
                case 'history':
                    filtered = allBorrowings.filter(b => ['returned', 'rejected', 'cancelled'].includes(b.status));
                    break;
            }

            const listContainer = document.getElementById(`list-${currentTab}`);
            const emptyState = document.getElementById('borrowings-empty');

            if (filtered.length === 0) {
                listContainer.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            listContainer.innerHTML = filtered.map(borrowing => {
                return createBorrowingCard(borrowing);
            }).join('');

            // Reinitialize icons
            setTimeout(() => {
                if (window.lucide && window.lucide.createIcons) {
                    window.lucide.createIcons();
                }
            }, 100);
        }

        function createBorrowingCard(b) {
            const statusBadge = {
                'pending': '<span class="badge badge-warning">Menunggu Persetujuan</span>',
                'approved': '<span class="badge badge-success">Disetujui</span>',
                'return_pending': '<span class="badge badge-info">Menunggu Verifikasi</span>',
                'returned': '<span class="badge badge-success">Selesai</span>',
                'rejected': '<span class="badge badge-error">Ditolak</span>',
                'cancelled': '<span class="badge badge-ghost">Dibatalkan</span>'
            };

            // Check if overdue
            const returnDate = new Date(b.tanggal_kembali);
            const today = new Date();
            const isOverdue = b.status === 'approved' && returnDate < today;
            const daysLate = isOverdue ? Math.floor((today - returnDate) / (1000 * 60 * 60 * 24)) : 0;

            let actions = '';

            if (b.status === 'pending') {
                actions = `
                    <button class="btn btn-sm btn-error" onclick="openCancelModal(${b.id})">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Batalkan
                    </button>
                `;
            } else if (b.status === 'approved') {
                actions = `
                    <button class="btn btn-sm btn-primary" onclick="openReturnModal(${b.id})">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Ajukan Pengembalian
                    </button>
                `;
            }

            return `
                <div class="card bg-base-100 border ${isOverdue ? 'border-error' : 'border-base-300'}">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">${b.item?.nama || 'Item'}</h3>
                                <p class="text-sm text-base-content/70">Pemilik: ${b.item?.user?.name || b.item?.ownerNama || 'N/A'}</p>
                                <p class="text-sm text-base-content/70">Durasi: ${b.lama_hari || 0} hari</p>
                                <p class="text-sm text-base-content/70">Tanggal Pinjam: ${formatDate(b.tanggal_pinjam)}</p>
                                <p class="text-sm text-base-content/70">Tanggal Kembali: ${formatDate(b.tanggal_kembali)}</p>
                                ${isOverdue ? `<p class="text-sm text-error font-semibold mt-2">⚠️ Terlambat ${daysLate} hari!</p>` : ''}
                                ${b.catatan ? `<p class="text-sm mt-2"><strong>Catatan:</strong> ${b.catatan}</p>` : ''}
                                ${b.kondisi ? `<p class="text-sm mt-2"><strong>Kondisi:</strong> ${b.kondisi}</p>` : ''}
                                ${b.alasan_penolakan ? `<p class="text-sm mt-2 text-error"><strong>Alasan Penolakan:</strong> ${b.alasan_penolakan}</p>` : ''}
                            </div>
                            <div class="text-right">
                                ${statusBadge[b.status] || ''}
                            </div>
                        </div>

                        <div class="flex gap-2 mt-4 flex-wrap">
                            ${b.foto_ktm ? `<button class="btn btn-sm btn-ghost" onclick="showImage('${b.foto_ktm}', 'Foto KTM')">
                                                <i data-lucide="image" class="w-4 h-4"></i>
                                                Lihat KTM
                                            </button>` : ''}
                            ${b.foto_kondisi ? `<button class="btn btn-sm btn-ghost" onclick="showImage('${b.foto_kondisi}', 'Foto Kondisi')">
                                                <i data-lucide="image" class="w-4 h-4"></i>
                                                Lihat Foto Kondisi
                                            </button>` : ''}
                            <button class="btn btn-sm btn-ghost" onclick="showDetail(${b.id})">
                                <i data-lucide="info" class="w-4 h-4"></i>
                                Detail
                            </button>
                        </div>

                        ${actions ? `<div class="card-actions justify-end mt-4">${actions}</div>` : ''}
                    </div>
                </div>
            `;
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }

        function showImage(imagePath, title) {
            document.getElementById('image-modal-title').textContent = title;
            document.getElementById('preview-image').src = `/storage/${imagePath}`;
            image_modal.showModal();
        }

        function showDetail(id) {
            const borrowing = allBorrowings.find(b => b.id === id);
            if (!borrowing) return;

            const detailContent = document.getElementById('detail-content');
            detailContent.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold">Informasi Barang</h4>
                        <p>Nama: ${borrowing.item?.nama || '-'}</p>
                        <p>Deskripsi: ${borrowing.item?.deskripsi || '-'}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold">Informasi Pemilik</h4>
                        <p>Nama: ${borrowing.item?.user?.name || borrowing.item?.ownerNama || '-'}</p>
                        <p>Email: ${borrowing.item?.user?.email || '-'}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold">Detail Peminjaman</h4>
                        <p>Durasi: ${borrowing.lama_hari} hari</p>
                        <p>Tanggal Pinjam: ${formatDate(borrowing.tanggal_pinjam)}</p>
                        <p>Tanggal Kembali: ${formatDate(borrowing.tanggal_kembali)}</p>
                        ${borrowing.tanggal_pengembalian_aktual ? `<p>Dikembalikan: ${formatDate(borrowing.tanggal_pengembalian_aktual)}</p>` : ''}
                        <p>Status: ${borrowing.status}</p>
                        ${borrowing.catatan ? `<p>Catatan: ${borrowing.catatan}</p>` : ''}
                        ${borrowing.kondisi ? `<p>Kondisi: ${borrowing.kondisi}</p>` : ''}
                        ${borrowing.alasan_penolakan ? `<p class="text-error">Alasan Penolakan: ${borrowing.alasan_penolakan}</p>` : ''}
                        ${borrowing.rating ? `<p>Rating: ${borrowing.rating}/5</p>` : ''}
                    </div>
                </div>
            `;
            detail_modal.showModal();
        }

        function openReturnModal(id) {
            const borrowing = allBorrowings.find(b => b.id === id);
            if (!borrowing) return;

            actionBorrowingId = id;
            document.getElementById('return_borrowing_id').value = id;
            document.getElementById('return-item-name').textContent = borrowing.item?.nama || 'Item';
            document.getElementById('return-item-owner').textContent =
                `Pemilik: ${borrowing.item?.user?.name || borrowing.item?.ownerNama || 'N/A'}`;

            // Reset form
            document.getElementById('returnForm').reset();
            document.getElementById('photo-preview').classList.add('hidden');

            return_modal.showModal();
        }

        function openCancelModal(id) {
            actionBorrowingId = id;
            cancel_modal.showModal();
        }

        async function handleReturnSubmit(e) {
            e.preventDefault();

            const borrowingId = document.getElementById('return_borrowing_id').value;
            if (!borrowingId) return;

            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');

            window.setButtonLoading(submitBtn, true, 'Mengirim...');

            try {
                const response = await window.fetchRequest(
                    `{{ route('borrowings.return', '_id_') }}`.replace('_id_', borrowingId), {
                        method: 'POST',
                        body: formData
                    }
                );

                if (response.success) {
                    window.showToast(response.message || 'Permintaan pengembalian berhasil dikirim', 'success');
                    return_modal.close();
                    actionBorrowingId = null;
                    loadBorrowings();
                } else {
                    window.showToast(response.message || 'Gagal mengirim permintaan', 'error');
                }
            } catch (error) {
                console.error('Error submitting return:', error);
                window.showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
            } finally {
                window.setButtonLoading(submitBtn, false);
            }
        }

        async function confirmCancel() {
            if (!actionBorrowingId) return;

            try {
                const response = await window.fetchRequest(
                    `{{ route('borrowings.cancel', '_id_') }}`.replace('_id_', actionBorrowingId), {
                        method: 'POST'
                    }
                );

                if (response.success) {
                    window.showToast(response.message || 'Permintaan berhasil dibatalkan', 'success');
                    cancel_modal.close();
                    actionBorrowingId = null;
                    loadBorrowings();
                } else {
                    window.showToast(response.message || 'Gagal membatalkan permintaan', 'error');
                }
            } catch (error) {
                console.error('Error canceling:', error);
                window.showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
            }
        }
    </script>
@endsection
