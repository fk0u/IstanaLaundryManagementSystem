<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ImageCompressionService;
use App\Services\Security\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected TwoFactorService $twoFactorService;
    protected ImageCompressionService $imageService;

    public function __construct(TwoFactorService $twoFactorService, ImageCompressionService $imageService)
    {
        $this->twoFactorService = $twoFactorService;
        $this->imageService = $imageService;
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $qrCodeUrl = null;
        $secret = null;

        if ($user->two_factor_secret && ! $user->two_factor_confirmed_at) {
            $secret = decrypt($user->two_factor_secret);
            $qrCodeUrl = $this->twoFactorService->getQrCodeUrl($user, $secret);
        }

        return view('profile.edit', [
            'user' => $user,
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update profile avatar (compress to WebP < 200KB).
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        $user = $request->user();

        $filename = $this->imageService->compressAndStoreAvatar($request->file('avatar'), $user->id);

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => $filename]);

        return Redirect::route('profile.edit')->with('success', 'Foto profil berhasil diunggah, dikonversi ke WebP, dan dikompresi di bawah 200KB!');
    }

    /**
     * Enable 2FA TOTP setup.
     */
    public function enable2FA(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->two_factor_secret) {
            $secret = $this->twoFactorService->generateSecretKey();
            $user->update(['two_factor_secret' => encrypt($secret)]);
        }

        return Redirect::route('profile.edit')->with('show_2fa_modal', true);
    }

    /**
     * Confirm 2FA OTP code.
     */
    public function confirm2FA(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user()->fresh();

        if (! $user->two_factor_secret) {
            return Redirect::route('profile.edit')->with('error', 'Silakan inisialisasi 2FA terlebih dahulu.');
        }

        $secret = decrypt($user->two_factor_secret);
        if (! $this->twoFactorService->verifyKey($secret, $request->code)) {
            return Redirect::route('profile.edit')->with('error', 'Kode OTP 2FA yang Anda masukkan salah. Coba lagi.');
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();

        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ]);

        return Redirect::route('profile.edit')->with('recovery_codes', $recoveryCodes)->with('success', '2FA Google Authenticator berhasil diaktifkan!');
    }

    /**
     * Disable 2FA.
     */
    public function disable2FA(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        $user = $request->user()->fresh();

        if (! Hash::check($request->current_password, $user->password)) {
            return Redirect::route('profile.edit')->with('error', 'Password lama yang dimasukkan tidak sesuai.');
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return Redirect::route('profile.edit')->with('success', '2FA berhasil dinonaktifkan.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
