<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressionService
{
    /**
     * Compress and store an uploaded avatar image under 200KB.
     * Uses WebP format if GD with WebP support is available, otherwise JPEG/PNG fallback.
     *
     * @param UploadedFile $file
     * @param int $userId
     * @param int $maxSizeBytes Default 200 KB (204,800 bytes)
     * @return string Relative storage path
     */
    public function compressAndStoreAvatar(UploadedFile $file, int $userId, int $maxSizeBytes = 204800): string
    {
        $sourcePath = $file->getRealPath();
        $ext = strtolower($file->getClientOriginalExtension());
        if (empty($ext)) {
            $ext = 'jpg';
        }

        // If GD extension is not available at all, store original file safely
        if (! extension_loaded('gd') || ! function_exists('imagecreatefromstring')) {
            $filename = "avatars/avatar_{$userId}_" . Str::random(8) . '.' . $ext;
            Storage::disk('public')->put($filename, file_get_contents($sourcePath));
            return $filename;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (! $imageInfo) {
            $filename = "avatars/avatar_{$userId}_" . Str::random(8) . '.' . $ext;
            Storage::disk('public')->put($filename, file_get_contents($sourcePath));
            return $filename;
        }

        $mime = $imageInfo['mime'] ?? '';

        $srcImage = match ($mime) {
            'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($sourcePath) : false,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($sourcePath) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($sourcePath) : false,
            default => @imagecreatefromstring(file_get_contents($sourcePath)),
        };

        if (! $srcImage) {
            $filename = "avatars/avatar_{$userId}_" . Str::random(8) . '.' . $ext;
            Storage::disk('public')->put($filename, file_get_contents($sourcePath));
            return $filename;
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

        $supportsWebp = function_exists('imagewebp');
        $outputExt = $supportsWebp ? 'webp' : 'jpg';
        $tempPath = tempnam(sys_get_temp_dir(), 'avatar_') . '.' . $outputExt;
        $quality = 85;

        do {
            if ($supportsWebp) {
                imagewebp($resizedImage, $tempPath, $quality);
            } else {
                imagejpeg($resizedImage, $tempPath, $quality);
            }
            $fileSize = file_exists($tempPath) ? filesize($tempPath) : 0;
            $quality -= 10;
        } while ($fileSize > $maxSizeBytes && $quality >= 15);

        imagedestroy($srcImage);
        imagedestroy($resizedImage);

        $filename = "avatars/avatar_{$userId}_" . Str::random(8) . '.' . $outputExt;
        Storage::disk('public')->put($filename, file_get_contents($tempPath));
        @unlink($tempPath);

        return $filename;
    }
}
