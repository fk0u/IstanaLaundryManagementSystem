# Session Briefing — Istana Laundry Management System

**Last Updated:** 2026-08-05 23:35 WITA

## Current Status
- **2FA Login Challenge Flow (Web + API) — LIVE**:
  - User yang mengaktifkan 2FA akan diminta kode TOTP saat login melalui halaman `/two-factor-challenge`.
  - Opsi **"Trust This Device"** — perangkat dipercaya selama 30 hari via cookie `2fa_device_trust`.
  - Tabel `user_trusted_devices` menyimpan token hash SHA-256 dengan expiry 30 hari.
  - API Login mendukung 2FA melalui field `two_factor_code` / `recovery_code` dan header `X-Device-Trust-Token`.
- **Foto Profil di Topbar & Sidebar**:
  - Avatar foto profil (WebP <200KB) kini muncul di topbar button, topbar dropdown, dan sidebar footer.
  - Jika belum ada foto, tampil fallback initials.
- **Testing**: 7/7 test PASSED (47 assertions) di `TwoFactorAuthenticationTest`.
- **Deployed**: Git pushed & VPS migration executed.
