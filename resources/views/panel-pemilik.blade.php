@extends('layouts.app')

@section('title', 'Panel Pemilik - PinjamIn')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold mb-2">Panel Pemilik</h1>
            <p class="text-base-content/70">Kelola permintaan peminjaman barang Anda</p>
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
                <div class="stat-value text-success" id="approved-count">0</div>
                <div class="stat-desc">Barang aktif</div>
            </div>

            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-figure text-info">
                    <i data-lucide="rotate-ccw" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Permintaan Kembali</div>
                <div class="stat-value text-info" id="return-count">0</div>
                <div class="stat-desc">Menunggu verifikasi</div>
            </div>

            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-figure text-base-content">
                    <i data-lucide="check-check" class="w-8 h-8"></i>
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
            </a>
            <a role="tab" class="tab" onclick="switchTab('approved')" id="tab-approved">
                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                Sedang Dipinjam
            </a>
            <a role="tab" class="tab" onclick="switchTab('return')" id="tab-return">
                <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i>
                Permintaan Kembali
            </a>
            <a role="tab" class="tab" onclick="switchTab('completed')" id="tab-completed">
                <i data-lucide="archive" class="w-4 h-4 mr-2"></i>
                Riwayat
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

                    <!-- Approved/Active Borrowings -->
                    <div id="content-approved" style="display: none;">
                        <h2 class="text-xl font-bold mb-4">Barang Sedang Dipinjam</h2>
                        <div id="list-approved" class="space-y-4">
                            <!-- Will be populated by JS -->
                        </div>
                    </div>

                    <!-- Return Requests -->
                    <div id="content-return" style="display: none;">
                        <h2 class="text-xl font-bold mb-4">Permintaan Pengembalian</h2>
                        <div id="list-return" class="space-y-4">
                            <!-- Will be populated by JS -->
                        </div>
                    </div>

                    <!-- Completed/History -->
                    <div id="content-completed" style="display: none;">
                        <h2 class="text-xl font-bold mb-4">Riwayat Peminjaman</h2>
                        <div id="list-completed" class="space-y-4">
                            <!-- Will be populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="borrowings-empty" class="text-center py-12" style="display: none;">
                    <i data-lucide="inbox" class="w-24 h-24 mx-auto text-base-content/20"></i>
                    <p class="text-lg font-semibold mt-4">Tidak ada data</p>
                    <p class="text-base-content/70 mt-2">Belum ada permintaan peminjaman untuk kategori ini</p>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Approve Confirmation Modal -->
    <dialog id="approve_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Setujui Peminjaman</h3>
            <p class="py-4">Apakah Anda yakin ingin menyetujui permintaan peminjaman ini?</p>
            <div class="alert alert-info">
                <i data-lucide="info" class="w-5 h-5"></i>
                <span class="text-sm">Stok barang akan berkurang setelah disetujui</span>
            </div>
            <div class="modal-action">
                <button class="btn btn-ghost" onclick="approve_modal.close()">Batal</button>
                <button class="btn btn-success" onclick="confirmApprove()">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    Setujui
                </button>
            </div>
        </div>
    </dialog>

    <!-- Reject Modal -->
    <dialog id="reject_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Tolak Peminjaman</h3>
            <p class="py-4">Berikan alasan penolakan:</p>
            <textarea id="reject_reason" class="textarea textarea-bordered w-full h-24"
                placeholder="Contoh: Barang sedang dalam perbaikan"></textarea>
            <div class="modal-action">
                <button class="btn btn-ghost" onclick="reject_modal.close()">Batal</button>
                <button class="btn btn-error" onclick="confirmReject()">
                    <i data-lucide="x" class="w-5 h-5"></i>
                    Tolak
                </button>
            </div>
        </div>
    </dialog>

    <!-- Approve Return Modal -->
    <dialog id="approve_return_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Setujui Pengembalian</h3>
            <p class="py-4">Apakah kondisi barang sesuai dengan foto yang dikirim?</p>
            <div class="alert alert-success">
                <i data-lucide="info" class="w-5 h-5"></i>
                <span class="text-sm">Stok barang akan bertambah setelah pengembalian disetujui</span>
            </div>
            <div class="modal-action">
                <button class="btn btn-ghost" onclick="approve_return_modal.close()">Batal</button>
                <button class="btn btn-success" onclick="confirmApproveReturn()">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    Setujui Pengembalian
                </button>
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
                    allBorrowings = response.data.data || [];
                    console.log(allBorrowings);
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
            const approved = allBorrowings.filter(b => b.status === 'approved').length;
            const returnRequests = allBorrowings.filter(b => b.status === 'return_requested').length;
            const completed = allBorrowings.filter(b => b.status === 'returned' || b.status === 'completed').length;

            document.getElementById('pending-count').textContent = pending;
            document.getElementById('approved-count').textContent = approved;
            document.getElementById('return-count').textContent = returnRequests;
            document.getElementById('completed-count').textContent = completed;
        }

        function switchTab(tab) {
            currentTab = tab;

            // Update tab active state
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('tab-active'));
            document.getElementById(`tab-${tab}`).classList.add('tab-active');

            // Hide all content
            ['pending', 'approved', 'return', 'completed'].forEach(t => {
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
                case 'approved':
                    filtered = allBorrowings.filter(b => b.status === 'approved');
                    break;
                case 'return':
                    filtered = allBorrowings.filter(b => b.status === 'return_requested');
                    break;
                case 'completed':
                    filtered = allBorrowings.filter(b => b.status === 'returned' || b.status === 'completed');
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
                'pending': '<span class="badge badge-warning">Menunggu</span>',
                'approved': '<span class="badge badge-success">Disetujui</span>',
                'return_requested': '<span class="badge badge-info">Pengembalian</span>',
                'returned': '<span class="badge badge-success">Selesai</span>',
                'rejected': '<span class="badge badge-error">Ditolak</span>',
                'cancelled': '<span class="badge badge-ghost">Dibatalkan</span>'
            };

            let actions = '';

            if (b.status === 'pending') {
                actions = `
                    <button class="btn btn-sm btn-success" onclick="openApproveModal(${b.id})">
                        Setujui
                    </button>
                    <button class="btn btn-sm btn-error" onclick="openRejectModal(${b.id})">
                        Tolak
                    </button>
                `;
            } else if (b.status === 'return_requested') {
                actions = `
                    <button class="btn btn-sm btn-success" onclick="openApproveReturnModal(${b.id})">
                        Setujui Pengembalian
                    </button>
                `;
            }

            return `
                <div class="card bg-base-100 border border-base-300">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">${b.item?.nama || 'Item'}</h3>
                                <p class="text-sm text-base-content/70">Peminjam: ${b.peminjam?.name || 'N/A'} (${b.peminjam?.nim || '-'})</p>
                                <p class="text-sm text-base-content/70">Durasi: ${b.lama_hari || 0} hari</p>
                                <p class="text-sm text-base-content/70">Tanggal Pinjam: ${formatDate(b.tanggal_pinjam)}</p>
                                ${b.catatan ? `<p class="text-sm mt-2"><strong>Alasan:</strong> ${b.catatan}</p>` : ''}
                                ${b.kondisi ? `<p class="text-sm mt-2"><strong>Kondisi saat dikembalikan:</strong> ${b.kondisi}</p>` : ''}
                            </div>
                            <div class="text-right">
                                ${statusBadge[b.status] || ''}
                            </div>
                        </div>

                        <div class="flex gap-2 mt-4 flex-wrap">
                            ${b.foto_ktm ? `<button class="btn btn-sm btn-ghost" onclick="showImage('${b.foto_ktm}', 'Foto KTM')">
                                                                        Lihat KTM
                                                                    </button>` : ''}
                            ${b.foto_kondisi ? `<button class="btn btn-sm btn-ghost" onclick="showImage('${b.foto_kondisi}', 'Foto Kondisi')">
                                                                        Lihat Foto Kondisi
                                                                    </button>` : ''}
                            <button class="btn btn-sm btn-ghost" onclick="showDetail(${b.id})">
                                Detail Lengkap
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
                        <h4 class="font-semibold">Informasi Peminjam</h4>
                        <p>Nama: ${borrowing.peminjam?.name || '-'}</p>
                        <p>Email: ${borrowing.peminjam?.email || '-'}</p>
                        <p>NIM: ${borrowing.peminjam?.nim || '-'}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold">Detail Peminjaman</h4>
                        <p>Durasi: ${borrowing.lama_hari} hari</p>
                        <p>Tanggal Pinjam: ${formatDate(borrowing.tanggal_pinjam)}</p>
                        <p>Tanggal Kembali: ${formatDate(borrowing.tanggal_kembali)}</p>
                        ${borrowing.tanggal_pengembalian_aktual ? `<p>Dikembalikan: ${formatDate(borrowing.tanggal_pengembalian_aktual)}</p>` : ''}
                        <p>Status: ${borrowing.status}</p>
                        ${borrowing.catatan ? `<p>Alasan: ${borrowing.catatan}</p>` : ''}
                        ${borrowing.kondisi ? `<p>Kondisi: ${borrowing.kondisi}</p>` : ''}
                        ${borrowing.alasan_penolakan ? `<p class="text-error">Alasan Penolakan: ${borrowing.alasan_penolakan}</p>` : ''}
                    </div>
                </div>
            `;
            detail_modal.showModal();
        }

        function openApproveModal(id) {
            actionBorrowingId = id;
            approve_modal.showModal();
        }

        function openRejectModal(id) {
            actionBorrowingId = id;
            document.getElementById('reject_reason').value = '';
            reject_modal.showModal();
        }

        function openApproveReturnModal(id) {
            actionBorrowingId = id;
            approve_return_modal.showModal();
        }

        async function confirmApprove() {
            if (!actionBorrowingId) return;

            try {
                const response = await window.fetchRequest(
                    `{{ route('borrowings.approve', '_id_') }}`.replace('_id_', actionBorrowingId), {
                        method: 'POST'
                    }
                );

                if (response.success) {
                    approve_modal.close();
                    actionBorrowingId = null;
                    loadBorrowings();
                }
            } catch (error) {
                console.error('Error approving:', error);
            }
        }

        async function confirmReject() {
            if (!actionBorrowingId) return;

            const reason = document.getElementById('reject_reason').value;
            if (!reason.trim()) {
                window.showToast('Alasan penolakan harus diisi', 'error');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('alasan_penolakan', reason);

                const response = await window.fetchRequest(
                    `{{ route('borrowings.reject', '_id_') }}`.replace('_id_', actionBorrowingId), {
                        method: 'POST',
                        body: formData
                    }
                );

                if (response.success) {
                    reject_modal.close();
                    actionBorrowingId = null;
                    loadBorrowings();
                }
            } catch (error) {
                console.error('Error rejecting:', error);
            }
        }

        async function confirmApproveReturn() {
            if (!actionBorrowingId) return;

            try {
                const response = await window.fetchRequest(
                    `{{ route('borrowings.approve-return', '_id_') }}`.replace('_id_', actionBorrowingId), {
                        method: 'POST'
                    }
                );

                if (response.success) {
                    approve_return_modal.close();
                    actionBorrowingId = null;
                    loadBorrowings();
                }
            } catch (error) {
                console.error('Error approving return:', error);
            }
        }
    </script>
@endsection
