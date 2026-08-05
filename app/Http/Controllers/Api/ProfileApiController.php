<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImageCompressionService;
use App\Services\Security\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileApiController extends Controller
{
    protected TwoFactorService $twoFactorService;
    protected ImageCompressionService $imageService;

    public function __construct(TwoFactorService $twoFactorService, ImageCompressionService $imageService)
    {
        $this->twoFactorService = $twoFactorService;
        $this->imageService = $imageService;
    }

    public function show(Request $request)
    {
        $user = $request->user()->load(['branch', 'roles']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'branch' => $user->branch ? $user->branch->name : null,
                'role' => $user->getRoleNames()->first() ?? 'User',
                'avatar_url' => $user->avatar_path ? asset('storage/' . $user->avatar_path) : null,
                'two_factor_enabled' => ! is_null($user->two_factor_confirmed_at),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|string|min:8',
        ]);

        if (! empty($validated['new_password'])) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Password lama yang dimasukkan tidak sesuai.',
                ], 422);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui!',
            'data' => $user,
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240', // Max 10MB input
        ]);

        $user = $request->user();

        // Compress and convert to WebP under 200KB
        $filename = $this->imageService->compressAndStoreAvatar($request->file('avatar'), $user->id);

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => $filename]);
        $fullPath = Storage::disk('public')->path($filename);
        $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;

        return response()->json([
            'status' => 'success',
            'message' => 'Foto profil berhasil diunggah, dikonversi ke WebP, dan dikompresi di bawah 200KB!',
            'data' => [
                'avatar_path' => $filename,
                'avatar_url' => asset('storage/' . $filename),
                'file_size_bytes' => $fileSize,
                'file_size_kb' => round($fileSize / 1024, 2) . ' KB',
            ],
        ]);
    }

    public function enable2FA(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fitur 2FA sudah aktif pada akun Anda.',
            ], 422);
        }

        $secret = $this->twoFactorService->generateSecretKey();
        $qrCodeUrl = $this->twoFactorService->getQrCodeUrl($user, $secret);

        $user->update(['two_factor_secret' => encrypt($secret)]);

        return response()->json([
            'status' => 'success',
            'message' => 'Silakan pindai QR Code di Google Authenticator atau Authy lalu masukkan 6 digit OTP.',
            'data' => [
                'secret' => $secret,
                'otpauth_url' => $qrCodeUrl,
            ],
        ]);
    }

    public function confirm2FA(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user()->fresh();

        if (! $user->two_factor_secret) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan inisialisasi 2FA terlebih dahulu.',
            ], 422);
        }

        $secret = decrypt($user->two_factor_secret);
        if (! $this->twoFactorService->verifyKey($secret, $validated['code'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP 2FA yang Anda masukkan tidak valid.',
            ], 422);
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();

        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Autentikasi Dua Faktor (2FA) berhasil diaktifkan!',
            'data' => [
                'recovery_codes' => $recoveryCodes,
            ],
        ]);
    }

    public function disable2FA(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = $request->user()->fresh();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password yang dimasukkan salah.',
            ], 422);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Fitur 2FA berhasil dinonaktifkan.',
        ]);
    }
}
