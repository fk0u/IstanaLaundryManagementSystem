# Manual Testing & QA Execution Guide
# Istana Laundry Management System

> **Official Helpdesk / Support:** +62 811-5599-199  
> **Target Environment:** Local (`http://localhost:8000`) & Production (`https://istanasystem.alk-tech.my.id`)  

---

## 🧪 Skenario Pengujian Manual

### 1. Pengujian Two-Factor Authentication (2FA) & Trust Device
1. **Setup 2FA**:
   - Login sebagai user → Buka menu **Profil Staf** (`/profile`).
   - Klik **"Setup 2FA Google Authenticator"**.
   - Scan QR code menggunakan aplikasi Google Authenticator / Authy.
   - Masukkan 6 digit OTP → Klik **Verifikasi & Aktifkan**.
   - Pastikan 8 kode pemulihan darurat muncul dan simpan kode tersebut.
2. **Uji 2FA Challenge saat Login**:
   - Logout dari aplikasi.
   - Login kembali menggunakan email & password.
   - Sistem HARUS mengarahkan ke halaman `/two-factor-challenge`.
   - Masukkan kode OTP salah (misal `000000`) → Sistem HARUS menolak dengan pesan error.
   - Masukkan kode OTP benar dari Google Authenticator → Sistem HARUS berhasil login dan masuk ke Dashboard.
3. **Uji Fitur Trust Device (30 Hari)**:
   - Logout dari aplikasi.
   - Login kembali → Di halaman 2FA challenge, **centang "Percayai perangkat ini selama 30 hari"**.
   - Masukkan kode OTP benar.
   - Logout dari aplikasi.
   - Login kembali dengan email & password → Sistem HARUS **langsung masuk ke Dashboard tanpa meminta kode 2FA** lagi (karena cookie `2fa_device_trust` aktif).

---

### 2. Pengujian Upload Foto Profil & WebP Compression (≤ 200KB)
1. Buka halaman **Profil Staf** (`/profile`).
2. Pilih file foto resolusi tinggi (misal JPG / PNG ukuran 2MB - 5MB).
3. Klik **"Simpan Foto Profil"**.
4. Verifikasi bahwa:
   - Foto berhasil di-upload dan ditampilkan di preview profil.
   - Foto profil muncul di **Topbar (kanan atas)** dan **Sidebar (kiri bawah)**.
   - File tersimpan di `storage/app/public/avatars/` sebagai format `.webp` dengan ukuran **kurang dari 200KB**.

---

### 3. Pengujian RESTful API & Swagger Documentation
1. Buka URL `/api/documentation` pada browser.
2. Pastikan halaman **Swagger UI** terbuka dengan rapi.
3. Test endpoint `POST /api/login`:
   - Masukkan body JSON `{ "email": "admin@istanalaundry.com", "password": "password" }`.
   - Pastikan response mengembalikan Sanctum Bearer Token.
4. Gunakan token tersebut pada tombol **"Authorize"** di Swagger UI (`Bearer {token}`).
5. Test endpoint `GET /api/v1/dashboard/stats` dan `GET /api/v1/orders` → Pastikan response HTTP status `200 OK` dengan data JSON yang valid.

---

### 4. Pengujian Security Headers & Rate Limiting
1. Jalankan `curl -I http://localhost:8000/` atau periksa via Inspect Element Browser (Network Tab).
2. Verifikasi keberadaan header berikut:
   - `Strict-Transport-Security`
   - `X-Frame-Options: SAMEORIGIN`
   - `X-Content-Type-Options: nosniff`
   - `X-XSS-Protection: 1; mode=block`
3. Kirim request beruntun > 30x dalam 1 menit ke `/api/v1/branches` → Verifikasi response HTTP `429 Too Many Requests`.
