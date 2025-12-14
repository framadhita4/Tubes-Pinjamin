@extends('layouts.app')

@section('title', 'Upload Barang - PinjamIn')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2">Upload Barang Baru</h1>
            <p class="text-base-content/70">Tambahkan barang yang ingin Anda sewakan kepada peminjam</p>
        </div>

        <!-- Upload Form -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf

                    <!-- Item Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Nama Barang <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="nama" placeholder="Contoh: Kamera DSLR Canon EOS 80D"
                            class="input input-bordered w-full" required />
                    </div>

                    <!-- Description -->
                    <div class="form-control mt-4 flex flex-col">
                        <label class="label">
                            <span class="label-text font-medium">Deskripsi</span>
                        </label>
                        <textarea name="deskripsi" placeholder="Jelaskan kondisi dan spesifikasi barang..."
                            class="textarea textarea-bordered h-24 w-full" rows="4"></textarea>
                    </div>

                    <!-- Stock and Max Days -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Stok <span class="text-error">*</span></span>
                            </label>
                            <input type="number" name="stok" placeholder="1" min="1"
                                class="input input-bordered w-full" required />
                            <label class="label">
                                <span class="label-text-alt">Jumlah barang yang tersedia</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Maksimal Peminjaman <span
                                        class="text-error">*</span></span>
                            </label>
                            <input type="number" name="max_hari" placeholder="7" min="1"
                                class="input input-bordered w-full" required />
                            <label class="label">
                                <span class="label-text-alt">Maksimal hari peminjaman</span>
                            </label>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-control mt-4">
                        <label class="label">
                            <span class="label-text font-medium">Foto Barang <span class="text-error">*</span></span>
                        </label>
                        <input type="file" name="gambar" id="imageInput" accept="image/*"
                            class="file-input file-input-bordered w-full" required />
                        <label class="label">
                            <span class="label-text-alt">Format: JPG, PNG, maksimal 2MB</span>
                        </label>

                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-4" style="display: none;">
                            <label class="label">
                                <span class="label-text font-medium">Preview:</span>
                            </label>
                            <div class="flex justify-center">
                                <img id="previewImg" src="" alt="Preview"
                                    class="rounded-lg max-h-64 object-contain border-2 border-base-300" />
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="card-actions justify-end mt-6">
                        <button type="button" class="btn btn-ghost" onclick="resetForm()">Reset</button>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="upload" class="w-5 h-5"></i>
                            Upload Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="alert alert-info mt-6">
            <i data-lucide="info" class="w-6 h-6"></i>
            <div>
                <h3 class="font-bold">Tips Upload Barang:</h3>
                <ul class="text-sm mt-2 list-disc list-inside">
                    <li>Gunakan foto yang jelas dan berkualitas baik</li>
                    <li>Berikan deskripsi yang lengkap tentang kondisi barang</li>
                    <li>Pastikan stok sesuai dengan jumlah barang yang Anda miliki</li>
                    <li>Tentukan durasi maksimal peminjaman yang wajar</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast toast-top toast-end z-50"></div>

    <script>
        // Image preview
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    window.showToast('Ukuran file maksimal 2MB', 'error');
                    e.target.value = '';
                    return;
                }

                // Validate file type
                if (!file.type.startsWith('image/')) {
                    window.showToast('File harus berupa gambar', 'error');
                    e.target.value = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Form submission
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');

            window.setButtonLoading(submitBtn, true, 'Mengupload...');

            try {
                const response = await window.fetchRequest('{{ route('items.store') }}', {
                    method: 'POST',
                    body: formData
                });

                if (response.success) {
                    setTimeout(() => {
                        resetForm();
                        window.location.href = '{{ route('my-items') }}';
                    }, 1500);
                }
            } catch (error) {
                console.error('Upload error:', error);
            } finally {
                window.setButtonLoading(submitBtn, false);
            }
        });

        function resetForm() {
            document.getElementById('uploadForm').reset();
            document.getElementById('imagePreview').style.display = 'none';
        }

        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
