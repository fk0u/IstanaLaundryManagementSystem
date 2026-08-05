<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation — Istana Laundry Management System</title>
    <link rel="icon" href="{{ asset('images/logo.webp') }}" type="image/webp">
    
    <!-- Swagger UI CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet" />

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #f8fafc;
        }

        .swagger-header {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            border-b: 1px solid rgba(255, 255, 255, 0.1);
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .brand-box {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo {
            height: 38px;
            width: auto;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 1.25rem;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .brand-badge {
            background: #FF6600;
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-action {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            background: #FF6600;
            color: #ffffff;
            border-color: #FF6600;
        }

        /* Swagger Custom Dark Theme Tweaks */
        .swagger-ui {
            max-w: 1200px;
            margin: 0 auto;
            padding: 24px 16px 64px 16px;
            filter: invert(88%) hue-rotate(180deg) brightness(95%) contrast(90%);
        }
        .swagger-ui img {
            filter: invert(100%) hue-rotate(180deg);
        }
    </style>
</head>
<body>

    <header class="swagger-header">
        <a href="{{ url('/') }}" class="brand-box">
            <img src="{{ asset('images/logo.webp') }}" alt="Istana Laundry" class="brand-logo" />
            <span class="brand-title">ISTANA LAUNDRY</span>
            <span class="brand-badge">Interactive API Spec v1.0</span>
        </a>
        <div class="header-actions">
            <a href="{{ asset('docs/api/IstanaLaundry_Postman_Collection.json') }}" download class="btn-action">Download Postman JSON</a>
            <a href="{{ url('/') }}" class="btn-action">Kembali ke Web Utama</a>
        </div>
    </header>

    <div id="swagger-ui"></div>

    <!-- Swagger UI JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>

    <script>
        const spec = {
            "openapi": "3.0.3",
            "info": {
                "title": "Istana Laundry Management System API",
                "version": "1.0.0",
                "description": "Dokumentasi API interaktif resmi untuk Istana Laundry Management System Samarinda. Mendukung integrasi Web Company Profile, Mobile Apps, POS Tablet Kasir, dan Layanan Pemesanan Online berbasis titik GPS presisi."
            },
            "servers": [
                {
                    "url": "https://istanasystem.alk-tech.my.id/api",
                    "description": "Production Server (Samarinda VPS)"
                },
                {
                    "url": "http://localhost:8000/api",
                    "description": "Local Development Server"
                }
            ],
            "components": {
                "securitySchemes": {
                    "BearerAuth": {
                        "type": "http",
                        "scheme": "bearer",
                        "bearerFormat": "Sanctum",
                        "description": "Masukkan Token Sanctum yang didapatkan dari endpoint /login"
                    }
                }
            },
            "paths": {
                "/v1/branches": {
                    "get": {
                        "tags": ["1. Public API v1"],
                        "summary": "Daftar Outlet Cabang",
                        "description": "Mengambil daftar seluruh outlet cabang aktif Istana Laundry beserta alamat dan kontak HP.",
                        "responses": {
                            "200": {
                                "description": "Berhasil mengambil data cabang",
                                "content": {
                                    "application/json": {
                                        "example": {
                                            "status": "success",
                                            "data": [
                                                { "id": 1, "name": "Istana Laundry Samarinda Pusat", "code": "WJK", "phone": "08115550001", "address": "Jl. Wijaya Kusuma Blok V-C Gg. Rina, Air Hitam" }
                                            ]
                                        }
                                    }
                                }
                            }
                        }
                    }
                },
                "/v1/services": {
                    "get": {
                        "tags": ["1. Public API v1"],
                        "summary": "Daftar Layanan & Tarif",
                        "description": "Mengambil daftar jenis layanan laundry, satuan, dan harga.",
                        "parameters": [
                            {
                                "name": "branch_id",
                                "in": "query",
                                "required": false,
                                "schema": { "type": "integer" },
                                "description": "ID Cabang khusus untuk penyesuaian tarif lokal"
                            }
                        ],
                        "responses": {
                            "200": {
                                "description": "Berhasil mengambil data layanan",
                                "content": {
                                    "application/json": {
                                        "example": {
                                            "status": "success",
                                            "data": [
                                                { "id": 1, "name": "Cuci Kiloan Express", "type": "kilogram", "unit": "kg", "price": 18000, "description": "Selesai 24 jam" }
                                            ]
                                        }
                                    }
                                }
                            }
                        }
                    }
                },
                "/v1/track/{orderNumber}": {
                    "get": {
                        "tags": ["1. Public API v1"],
                        "summary": "Lacak Status Order (GET)",
                        "description": "Lacak status pengerjaan cucian dan timeline berdasarkan nomor nota.",
                        "parameters": [
                            {
                                "name": "orderNumber",
                                "in": "path",
                                "required": true,
                                "schema": { "type": "string" },
                                "example": "WJK-202608-0001"
                            }
                        ],
                        "responses": {
                            "200": { "description": "Detail status order ditemukan" },
                            "404": { "description": "Nomor nota tidak ditemukan" }
                        }
                    }
                },
                "/v1/orders/online": {
                    "post": {
                        "tags": ["1. Public API v1"],
                        "summary": "Submit Online Order Presisi GPS",
                        "description": "Pemesanan penjemputan online baru dari website dengan koordinat Latitude dan Longitude presisi.",
                        "requestBody": {
                            "required": true,
                            "content": {
                                "application/json": {
                                    "schema": {
                                        "type": "object",
                                        "required": ["customer_name", "customer_phone", "delivery_address"],
                                        "properties": {
                                            "branch_code": { "type": "string", "example": "WJK" },
                                            "customer_name": { "type": "string", "example": "Bpk. Ghani" },
                                            "customer_phone": { "type": "string", "example": "081234567890" },
                                            "delivery_address": { "type": "string", "example": "Jl. Air Hitam No. 88, Samarinda" },
                                            "latitude": { "type": "number", "example": -0.4851234 },
                                            "longitude": { "type": "number", "example": 117.1423456 },
                                            "service_id": { "type": "integer", "example": 1 },
                                            "quantity": { "type": "number", "example": 5 },
                                            "notes": { "type": "string", "example": "Jemput di pagar hitam" }
                                        }
                                    }
                                }
                            }
                        },
                        "responses": {
                            "201": { "description": "Order online berhasil dibuat" }
                        }
                    }
                },
                "/login": {
                    "post": {
                        "tags": ["2. Staff Authentication"],
                        "summary": "Login Staf (Kasir / Admin)",
                        "description": "Mendapatkan token Bearer Sanctum.",
                        "requestBody": {
                            "required": true,
                            "content": {
                                "application/json": {
                                    "schema": {
                                        "type": "object",
                                        "required": ["email", "password"],
                                        "properties": {
                                            "email": { "type": "string", "example": "kasir@istanalaundry.com" },
                                            "password": { "type": "string", "example": "password" }
                                        }
                                    }
                                }
                            }
                        },
                        "responses": {
                            "200": { "description": "Login berhasil, mengembalikan token Bearer" }
                        }
                    }
                },
                "/me": {
                    "get": {
                        "tags": ["2. Staff Authentication"],
                        "summary": "Profil Staf Terautentikasi",
                        "security": [{ "BearerAuth": [] }],
                        "responses": {
                            "200": { "description": "Informasi staf login" }
                        }
                    }
                },
                "/pos/orders": {
                    "post": {
                        "tags": ["3. POS Cashier API"],
                        "summary": "Buat Order Transaksi POS Kasir",
                        "security": [{ "BearerAuth": [] }],
                        "requestBody": {
                            "required": true,
                            "content": {
                                "application/json": {
                                    "schema": {
                                        "type": "object",
                                        "required": ["order_type", "payment_method", "items"],
                                        "properties": {
                                            "customer_id": { "type": "integer", "example": 1 },
                                            "order_type": { "type": "string", "example": "outlet" },
                                            "payment_method": { "type": "string", "example": "cash" },
                                            "items": {
                                                "type": "array",
                                                "items": {
                                                    "type": "object",
                                                    "properties": {
                                                        "service_id": { "type": "integer", "example": 1 },
                                                        "quantity": { "type": "number", "example": 5 },
                                                        "unit_price": { "type": "number", "example": 18000 }
                                                    }
                                                }
                                            },
                                            "paid_amount": { "type": "number", "example": 90000 }
                                        }
                                    }
                                }
                            }
                        },
                        "responses": {
                            "201": { "description": "Order POS berhasil dibuat" }
                        }
                    }
                },
                "/production/{id}/status": {
                    "patch": {
                        "tags": ["4. Production Workshop API"],
                        "summary": "Update Status Tahapan Produksi",
                        "security": [{ "BearerAuth": [] }],
                        "parameters": [
                            {
                                "name": "id",
                                "in": "path",
                                "required": true,
                                "schema": { "type": "integer" }
                            }
                        ],
                        "requestBody": {
                            "required": true,
                            "content": {
                                "application/json": {
                                    "schema": {
                                        "type": "object",
                                        "required": ["status"],
                                        "properties": {
                                            "status": { "type": "string", "enum": ["TERIMA", "PILAH", "CUCI", "KERING", "LIPAT", "CEK", "SIAP", "DIAMBIL"], "example": "CUCI" },
                                            "notes": { "type": "string", "example": "Mesin cuci #1 selesai pengerjaan" }
                                        }
                                    }
                                }
                            }
                        },
                        "responses": {
                            "200": { "description": "Status produksi berhasil diperbarui" }
                        }
                    }
                }
            }
        };

        window.onload = function() {
            window.ui = SwaggerUIBundle({
                spec: spec,
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ],
                layout: "BaseLayout"
            });
        };
    </script>
</body>
</html>
