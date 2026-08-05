# Istana Laundry Management System — Top-Tier Security, TOTP 2FA & WebP Avatar Reference

Dokumentasi lengkap RESTful API v1 untuk **Istana Laundry Management System Samarinda**. API ini dilengkapi dengan **Top-Tier Security Hardening** (HSTS, CSP, X-Frame-Options, Rate Limiting), **Two-Factor Authentication (2FA TOTP)** Google Authenticator, serta **Upload Foto Profil dengan Konversi & Kompresi WebP (Maksimal 200KB)**.

---

## 🛡️ Security Headers & Hardening

Setiap response API dilindungi oleh HTTP Security Headers berikut:
* `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
* `X-Frame-Options: SAMEORIGIN`
* `X-Content-Type-Options: nosniff`
* `X-XSS-Protection: 1; mode=block`
* `Referrer-Policy: strict-origin-when-cross-origin`
* `Permissions-Policy: camera=(), microphone=(), geolocation=(self)`

---

## 🔐 Profile & 2FA Security Endpoints (`/api/v1/profile`)

### 1. View User Profile
* **Endpoint:** `GET /v1/profile`
* **Headers:** `Authorization: Bearer {token}`
* **Response 200:**
  ```json
  {
    "status": "success",
    "data": {
      "id": 1,
      "name": "Super Admin",
      "email": "admin@istanalaundry.com",
      "branch": "Samarinda Utama",
      "role": "Super_Admin",
      "avatar_url": "https://istanasystem.alk-tech.my.id/storage/avatars/avatar_1_x89a2b.webp",
      "two_factor_enabled": true
    }
  }
  ```

### 2. Upload & Compress Avatar Photo (WebP < 200KB)
* **Endpoint:** `POST /v1/profile/avatar`
* **Content-Type:** `multipart/form-data`
* **Body:** `avatar` (file JPEG, PNG, WEBP)
* **Features:** Resizes image to 500x500px, converts to `.webp`, and compresses dynamically to guarantee file size <= 200KB.
* **Response 200:**
  ```json
  {
    "status": "success",
    "message": "Foto profil berhasil diunggah, dikonversi ke WebP, dan dikompresi di bawah 200KB!",
    "data": {
      "avatar_path": "avatars/avatar_1_x89a2b.webp",
      "avatar_url": "https://istanasystem.alk-tech.my.id/storage/avatars/avatar_1_x89a2b.webp",
      "file_size_bytes": 45120,
      "file_size_kb": "44.06 KB"
    }
  }
  ```

### 3. Enable 2FA TOTP (Google Authenticator)
* **Endpoint:** `POST /v1/profile/2fa/enable`
* **Response 200:** Generates a 16-character Base32 secret key and `otpauth://` URL for scanning QR code in Google Authenticator or Authy.

### 4. Confirm & Activate 2FA
* **Endpoint:** `POST /v1/profile/2fa/confirm`
* **Body:** `{ "code": "123456" }`
* **Response 200:** Validates initial 6-digit OTP code and returns 8 emergency recovery codes.

### 5. Disable 2FA
* **Endpoint:** `POST /v1/profile/2fa/disable`
* **Body:** `{ "current_password": "password" }`

---

*Hak Cipta © 2026 Istana Laundry Management System — Security & Profile API Reference.*
