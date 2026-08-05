# Istana Laundry Management System — Complete API Documentation v1.0

Dokumentasi resmi Application Programming Interface (API) untuk **Istana Laundry Management System Samarinda**. API ini dirancang untuk mendukung integrasi Web Company Profile, Mobile Apps, POS Tablet Kasir, dan Layanan Pemesanan Online berbasis titik GPS.

---

## 📌 Informasi Umum

* **Base URL Production:** `https://istanasystem.alk-tech.my.id/api`
* **Base URL Local:** `http://localhost:8000/api`
* **Format Request/Response:** `application/json`
* **Standar Zona Waktu:** **WITA (UTC+8 / Asia/Singapore / Asia/Makassar)**
* **Header Default:**
  ```http
  Accept: application/json
  Content-Type: application/json
  ```

---

## 🔐 Autentikasi (Laravel Sanctum)

Endpoint yang membutuhkan autentikasi (POS Kasir & Workshop Produksi) menggunakan **Bearer Token** pada HTTP Request Header:

```http
Authorization: Bearer {your_sanctum_token_here}
```

---

## 🌐 1. Public API v1 (Tanpa Autentikasi)

Endpoint ini bersifat publik dan dapat dipanggil langsung dari Web Company Profile (`istanalaundry.alk-tech.my.id`) atau aplikasi pihak ketiga tanpa token.

### 1.1 Get Active Branches (Daftar Cabang)
Mengambil daftar outlet/cabang Istana Laundry yang sedang aktif.

* **HTTP Method:** `GET`
* **Path:** `/v1/branches`
* **Headers:** `Accept: application/json`

#### Response `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Istana Laundry Samarinda Pusat",
      "code": "WJK",
      "phone": "08115550001",
      "address": "Jl. Wijaya Kusuma Blok V-C Gg. Rina, Air Hitam"
    },
    {
      "id": 2,
      "name": "Cabang Dr. Sutomo",
      "code": "SUT",
      "phone": "08115550002",
      "address": "Jl. Dr. Sutomo, Sidodadi, Kec. Samarinda Ulu"
    }
  ]
}
```

---

### 1.2 Get Active Services (Daftar Layanan & Tarif)
Mengambil daftar jenis layanan laundry, satuan, dan tarif (opsional disaring berdasarkan cabang).

* **HTTP Method:** `GET`
* **Path:** `/v1/services`
* **Query Parameters:**
  * `branch_id` *(Integer, Optional)* — ID cabang untuk mendapatkan penyesuaian harga khusus cabang.

#### Response `200 OK`:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Cuci Kiloan Express",
      "type": "kilogram",
      "unit": "kg",
      "price": 18000.00,
      "base_price": 18000.00,
      "description": "Layanan pencucian higienis selesai 24 jam"
    },
    {
      "id": 2,
      "name": "Dry Clean Jas / Kebaya",
      "type": "satuan",
      "unit": "pcs",
      "price": 35000.00,
      "base_price": 35000.00,
      "description": "Pembersihan kering pakaian bahan halus"
    }
  ]
}
```

---

### 1.3 Order Status Tracking (Lacak Status Nota)
Melihat posisi proses pengerjaan cucian, rincian biaya, dan *timeline* status berdasarkan nomor nota order.

* **HTTP Method:** `GET` atau `POST`
* **Path GET:** `/v1/track/{orderNumber}`
* **Path POST:** `/v1/track` (Body: `{"order_number": "WJK-202608-0001"}`)

#### Response `200 OK`:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "order_number": "WJK-202608-0001",
    "order_type": "pickup_delivery",
    "branch": {
      "id": 1,
      "name": "Istana Laundry Samarinda Pusat",
      "code": "WJK",
      "phone": "08115550001",
      "address": "Jl. Wijaya Kusuma Blok V-C Gg. Rina, Air Hitam"
    },
    "customer": {
      "name": "Bpk. Ghani",
      "phone_masked": "0812****90"
    },
    "delivery": {
      "address": "Jl. Air Hitam No. 88, Samarinda",
      "phone": "0812****90",
      "latitude": -0.4851234,
      "longitude": 117.1423456,
      "google_maps_url": "https://www.google.com/maps?q=-0.4851234,117.1423456",
      "pickup_scheduled_at": "2026-08-05 20:00:00"
    },
    "production_status": "CUCI",
    "payment_status": "paid",
    "total": 90000.00,
    "estimated_done_at": "07 Aug 2026 18:18 (UTC+8)",
    "created_at": "05/08/2026 18:18 (UTC+8)",
    "items": [
      {
        "service_name": "Cuci Kiloan Express",
        "quantity": 5.0,
        "unit": "kg",
        "unit_price": 18000.00,
        "subtotal": 90000.00
      }
    ],
    "timeline": [
      {
        "status": "TERIMA",
        "notes": "Pemesanan online diterima",
        "timestamp": "05/08/2026 18:18 (UTC+8)"
      },
      {
        "status": "CUCI",
        "notes": "Diproses di mesin cuci washer #2",
        "timestamp": "05/08/2026 19:00 (UTC+8)"
      }
    ],
    "tracking_url": "https://istanasystem.alk-tech.my.id/track?order_number=WJK-202608-0001",
    "invoice_url": "https://istanasystem.alk-tech.my.id/invoices/1"
  }
}
```

#### Response `404 Not Found`:
```json
{
  "status": "error",
  "message": "Order dengan nomor nota 'WJK-99999' tidak ditemukan."
}
```

---

### 1.4 Submit Online Order with GPS Pinpoint (Pemesanan Online)
Membuat pesanan penjemputan baru dari website dengan titik lokasi koordinat GPS presisi.

* **HTTP Method:** `POST`
* **Path:** `/v1/orders/online`
* **Request Body Parameters:**
  * `branch_code` *(String, Optional)* — Kode cabang (misal: `"WJK"`, `"SUT"`).
  * `customer_name` *(String, Required)* — Nama pemesan.
  * `customer_phone` *(String, Required)* — Nomor WhatsApp aktif (misal: `"081234567890"`).
  * `delivery_address` *(String, Required)* — Alamat lengkap penjemputan.
  * `latitude` *(Numeric, Optional)* — Koordinat Latitude (misal: `-0.4851234`).
  * `longitude` *(Numeric, Optional)* — Koordinat Longitude (misal: `117.1423456`).
  * `google_maps_url` *(String, Optional)* — Link khusus Google Maps (jika ada).
  * `service_id` *(Integer, Optional)* — ID layanan terpilih.
  * `quantity` *(Numeric, Optional, Default: 1)* — Estimasi berat/jumlah.
  * `notes` *(String, Optional)* — Catatan tambahan penjemputan.

#### Example Request Body:
```json
{
  "branch_code": "WJK",
  "customer_name": "Bpk. Ghani",
  "customer_phone": "081234567890",
  "delivery_address": "Jl. Air Hitam No. 88, Samarinda",
  "latitude": -0.4851234,
  "longitude": 117.1423456,
  "service_id": 1,
  "quantity": 5,
  "notes": "Jemput di pagar hitam depan pos satpam"
}
```

#### Response `201 Created`:
```json
{
  "status": "success",
  "message": "Pemesanan online berhasil dibuat!",
  "data": {
    "order_id": 158,
    "order_number": "ONLINE-WJK-20260805-4819",
    "branch_name": "Istana Laundry Samarinda Pusat",
    "customer_name": "Bpk. Ghani",
    "delivery_address": "Jl. Air Hitam No. 88, Samarinda",
    "latitude": -0.4851234,
    "longitude": 117.1423456,
    "google_maps_url": "https://www.google.com/maps?q=-0.4851234,117.1423456",
    "estimated_total": 90000,
    "tracking_url": "https://istanasystem.alk-tech.my.id/track?order_number=ONLINE-WJK-20260805-4819",
    "whatsapp_url": "https://wa.me/628115550001?text=*%5BPEMESANAN+ONLINE+BARU%5D*..."
  }
}
```

---

## 🔑 2. Autentikasi Staf (API Login & User Profile)

### 2.1 Staff Login
Login kasir/staf untuk mendapatkan Bearer Token.

* **HTTP Method:** `POST`
* **Path:** `/login`
* **Request Body:**
  ```json
  {
    "email": "kasir@istanalaundry.com",
    "password": "password"
  }
  ```

#### Response `200 OK`:
```json
{
  "token": "1|sanctum_token_string_here",
  "user": {
    "id": 2,
    "name": "H. Bambang Setiawan, S.E.",
    "email": "kasir@istanalaundry.com",
    "branch_id": 1,
    "roles": ["Kasir"]
  }
}
```

---

### 2.2 Get Authenticated User Profile
Mengambil informasi pengguna yang sedang login.

* **HTTP Method:** `GET`
* **Path:** `/me`
* **Headers:** `Authorization: Bearer {token}`

#### Response `200 OK`:
```json
{
  "id": 2,
  "name": "H. Bambang Setiawan, S.E.",
  "email": "kasir@istanalaundry.com",
  "branch_id": 1,
  "branch": {
    "id": 1,
    "name": "Istana Laundry Samarinda Pusat",
    "code": "WJK"
  }
}
```

---

## 💻 3. POS Tablet & Cashier API

Endpoint untuk aplikasi tablet kasir di lokasi outlet.

### 3.1 Get POS Services Catalog
* **HTTP Method:** `GET`
* **Path:** `/pos/services`
* **Headers:** `Authorization: Bearer {token}`

### 3.2 Get / Search Customers
* **HTTP Method:** `GET`
* **Path:** `/pos/customers?search=Ghani`
* **Headers:** `Authorization: Bearer {token}`

### 3.3 Create POS Order
* **HTTP Method:** `POST`
* **Path:** `/pos/orders`
* **Headers:** `Authorization: Bearer {token}`
* **Request Body:**
  ```json
  {
    "customer_id": 5,
    "order_type": "outlet",
    "payment_method": "cash",
    "items": [
      {
        "service_id": 1,
        "quantity": 3.5,
        "unit_price": 18000
      }
    ],
    "paid_amount": 70000
  }
  ```

---

## 🧺 4. Production & Workshop Status API

Endpoint untuk operator workshop memantau dan mengubah status tahapan cuci.

Status Tahapan Valid: `TERIMA` ➔ `PILAH` ➔ `CUCI` ➔ `KERING` ➔ `LIPAT` ➔ `CEK` ➔ `SIAP` ➔ `DIAMBIL`

### 4.1 Get Production Queue
* **HTTP Method:** `GET`
* **Path:** `/production`
* **Headers:** `Authorization: Bearer {token}`

### 4.2 Update Production Status
* **HTTP Method:** `PATCH`
* **Path:** `/production/{orderId}/status`
* **Headers:** `Authorization: Bearer {token}`
* **Request Body:**
  ```json
  {
    "status": "CUCI",
    "notes": "Mesin cuci #1 selesai pengerjaan"
  }
  ```

---

## 📄 Ringkasan Kode Status HTTP

| HTTP Code | Keterangan |
| :--- | :--- |
| **`200 OK`** | Request berhasil diproses. |
| **`201 Created`** | Pemesanan online / data baru berhasil dibuat. |
| **`400 Bad Request`** | Format parameter atau request body tidak valid. |
| **`401 Unauthorized`** | Token autentikasi hilang atau tidak valid. |
| **`404 Not Found`** | Nomor nota atau resource tidak ditemukan. |
| **`422 Unprocessable Entity`** | Gagal validasi data input. |
| **`500 Internal Error`** | Terjadi kesalahan pada server. |

---

*Hak Cipta © 2026 Istana Laundry Management System — Technical Documentation.*
