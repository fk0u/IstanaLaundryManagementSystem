# Session Briefing — Istana Laundry Management System

## Status Utama
- **Base Domain:** `https://istanasystem.alk-tech.my.id`
- **Database VPS:** MySQL `db_istanasystem` @ `localhost` (User: `istanadev`)
- **Zona Waktu Sistem & Database:** WITA / GMT+8 (`Asia/Singapore`)
- **Auto Sync Git:** Crontab berjalan per 5 menit + instant push trigger.

## Fitur & Perbaikan Terakhir
1. **Error 500 VPS Resolved:** Fix permission `storage/` & `bootstrap/cache/` to `777` for `www-data` PHP-FPM.
2. **Public API v1 Exposed:**
   - `GET /api/v1/branches`: List cabang & info outlet.
   - `GET /api/v1/services`: List tarif & jenis cuci.
   - `GET/POST /api/v1/track`: Lacak status order & timeline WITA.
   - `POST /api/v1/orders/online`: Order online presisi titik lokasi GPS (`latitude`, `longitude`, `google_maps_url`).
3. **Modal Pemesanan Online UI:** Disesuaikan untuk mendukung penuh **Light Mode** & Dark Mode.
4. **Peta Leaflet 5 Outlet:** Perbaikan kesalahan sintaks JS script tag sehingga peta Google Maps & 5 Pinpoint cabang Samarinda tampil sempurna.
5. **API Documentation & Collections:**
   - `docs/api/API_DOCUMENTATION.md`
   - `docs/api/IstanaLaundry_Postman_Collection.json` (Format Postman v2.1)
   - `docs/api/bruno/` (Koleksi native Bruno API Client dengan Environment `Production` & `Local`)
