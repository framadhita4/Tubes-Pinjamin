<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload item image to storage
     *
     * @param UploadedFile $file
     * @return string Path to stored file
     */
    public function uploadItemImage(UploadedFile $file): string
    {
        return $this->uploadFile($file, 'items');
    }

    /**
     * Upload KTM (Student ID) photo to storage
     *
     * @param UploadedFile $file
     * @return string Path to stored file
     */
    public function uploadKTMPhoto(UploadedFile $file): string
    {
        return $this->uploadFile($file, 'ktm');
    }

    /**
     * Upload return condition photo to storage
     *
     * @param UploadedFile $file
     * @return string Path to stored file
     */
    public function uploadReturnPhoto(UploadedFile $file): string
    {
        return $this->uploadFile($file, 'returns');
    }

    /**
     * Generic file upload method
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string Path to stored file
     */
    private function uploadFile(UploadedFile $file, string $directory): string
    {
        // Generate unique filename with timestamp
        $timestamp = now()->format('YmdHis');
        $randomString = Str::random(10);
        $extension = $file->getClientOriginalExtension();
        $filename = "{$timestamp}_{$randomString}.{$extension}";

        // Store file in public disk
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    /**
     * Delete file from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Get full URL of stored file
     *
     * @param string $path
     * @return string
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Validate image file
     *
     * @param UploadedFile $file
     * @param int $maxSizeKB Maximum file size in KB (default: 2MB)
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateImage(UploadedFile $file, int $maxSizeKB = 2048): array
    {
        // Check file type
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return [
                'valid' => false,
                'message' => 'File harus berupa gambar (JPEG, JPG, PNG, atau GIF)'
            ];
        }

        // Check file size
        $fileSizeKB = $file->getSize() / 1024;
        if ($fileSizeKB > $maxSizeKB) {
            return [
                'valid' => false,
                'message' => "Ukuran file maksimal {$maxSizeKB}KB (" . round($maxSizeKB / 1024, 2) . "MB)"
            ];
        }

        return ['valid' => true, 'message' => ''];
    }
}

