<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressionService
{
    /**
     * Compress and convert an uploaded avatar image to WebP with max 500x500px and max file size of 200KB.
     *
     * @param UploadedFile $file
     * @param int $userId
     * @param int $maxSizeBytes Default 200 KB (204,800 bytes)
     * @return string Relative storage path
     */
    public function compressAndStoreAvatar(UploadedFile $file, int $userId, int $maxSizeBytes = 204800): string
    {
        $sourcePath = $file->getRealPath();
        $imageInfo = @getimagesize($sourcePath);

        if (! $imageInfo) {
            throw new \InvalidArgumentException('File yang diunggah bukan gambar yang valid.');
        }

        $mime = $imageInfo[0] ? $imageInfo['mime'] : '';

        $srcImage = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            'image/gif' => @imagecreatefromgif($sourcePath),
            default => @imagecreatefromstring(file_get_contents($sourcePath)),
        };

        if (! $srcImage) {
            throw new \RuntimeException('Gagal memproses gambar yang diunggah.');
        }

        $origWidth = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        // Target square dimension max 500x500
        $maxDimension = 500;
        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            $ratio = min($maxDimension / $origWidth, $maxDimension / $origHeight);
            $newWidth = (int) round($origWidth * $ratio);
            $newHeight = (int) round($origHeight * $ratio);
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency if PNG/WebP
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
        imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($resizedImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Save temporary webp and loop quality to ensure file size <= 200KB
        $tempPath = tempnam(sys_get_temp_dir(), 'avatar_') . '.webp';
        $quality = 85;

        do {
            imagewebp($resizedImage, $tempPath, $quality);
            $fileSize = filesize($tempPath);
            $quality -= 10;
        } while ($fileSize > $maxSizeBytes && $quality >= 15);

        imagedestroy($srcImage);
        imagedestroy($resizedImage);

        $filename = "avatars/avatar_{$userId}_" . Str::random(8) . '.webp';
        Storage::disk('public')->put($filename, file_get_contents($tempPath));
        @unlink($tempPath);

        return $filename;
    }
}
