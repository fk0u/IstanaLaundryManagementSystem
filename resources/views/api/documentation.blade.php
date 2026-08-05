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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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

        .swagger-ui {
            max-width: 1200px;
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
            <span class="brand-badge">Full RESTful API Engine v1.0</span>
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
                "title": "Istana Laundry Management System Full REST API",
                "version": "1.0.0",
                "description": "Dokumentasi RESTful API menyeluruh untuk Istana Laundry Management System Samarinda. Meliputi 10 modul backend ERP: Public, Auth, Dashboard, Orders, CRM, Inventory, HR, Assets, Finance, Procurement, Shifts, dan Master Data."
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
                        "responses": { "200": { "description": "Daftar cabang aktif" } }
                    }
                },
                "/v1/services": {
                    "get": {
                        "tags": ["1. Public API v1"],
                        "summary": "Daftar Layanan & Tarif",
                        "responses": { "200": { "description": "Daftar layanan & harga" } }
                    }
                },
                "/v1/track/{orderNumber}": {
                    "get": {
                        "tags": ["1. Public API v1"],
                        "summary": "Lacak Status Order (GET)",
                        "parameters": [{ "name": "orderNumber", "in": "path", "required": true, "schema": { "type": "string" } }],
                        "responses": { "200": { "description": "Detail status order & timeline WITA" } }
                    }
                },
                "/v1/orders/online": {
                    "post": {
                        "tags": ["1. Public API v1"],
                        "summary": "Submit Online Order dengan Koordinat GPS",
                        "responses": { "201": { "description": "Order online berhasil dibuat" } }
                    }
                },
                "/login": {
                    "post": {
                        "tags": ["2. Staff Authentication"],
                        "summary": "Staff Login",
                        "responses": { "200": { "description": "Mengembalikan token Bearer Sanctum" } }
                    }
                },
                "/me": {
                    "get": {
                        "tags": ["2. Staff Authentication"],
                        "summary": "Profil User Staf Login",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "Data user staf" } }
                    }
                },
                "/v1/dashboard/stats": {
                    "get": {
                        "tags": ["3. Dashboard & Analytics"],
                        "summary": "Executive KPI Stats",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "Omzet hari ini, order aktif, dan siap ambil" } }
                    }
                },
                "/v1/dashboard/charts": {
                    "get": {
                        "tags": ["3. Dashboard & Analytics"],
                        "summary": "Grafik Omzet Bulanan",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "Data omzet per bulan" } }
                    }
                },
                "/v1/orders": {
                    "get": {
                        "tags": ["4. Orders & Transactions"],
                        "summary": "Daftar Transaksi Order (Filter & Pagination)",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "List transaksi order" } }
                    }
                },
                "/v1/orders/{order}": {
                    "get": {
                        "tags": ["4. Orders & Transactions"],
                        "summary": "Detail Transaksi Order",
                        "security": [{ "BearerAuth": [] }],
                        "parameters": [{ "name": "order", "in": "path", "required": true, "schema": { "type": "integer" } }],
                        "responses": { "200": { "description": "Detail transaksi" } }
                    }
                },
                "/v1/orders/{order}/payments": {
                    "post": {
                        "tags": ["4. Orders & Transactions"],
                        "summary": "Pelunasan / Cicilan Pembayaran Order",
                        "security": [{ "BearerAuth": [] }],
                        "parameters": [{ "name": "order", "in": "path", "required": true, "schema": { "type": "integer" } }],
                        "responses": { "200": { "description": "Pembayaran berhasil dicatat" } }
                    }
                },
                "/v1/customers": {
                    "get": {
                        "tags": ["5. CRM Customers & Loyalty"],
                        "summary": "Daftar & Cari Member Pelanggan",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "List pelanggan" } }
                    },
                    "post": {
                        "tags": ["5. CRM Customers & Loyalty"],
                        "summary": "Daftarkan Member Baru",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "201": { "description": "Member baru dibuat" } }
                    }
                },
                "/v1/inventory": {
                    "get": {
                        "tags": ["6. Inventory & Stock"],
                        "summary": "Daftar Stok Bahan & Low Stock Warning",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "Stok barang" } }
                    },
                    "post": {
                        "tags": ["6. Inventory & Stock"],
                        "summary": "Tambah Item Stok Baru",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "201": { "description": "Item stok dibuat" } }
                    }
                },
                "/v1/hr/employees": {
                    "get": {
                        "tags": ["7. HR & Payroll"],
                        "summary": "Daftar Karyawan",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "List karyawan" } }
                    },
                    "post": {
                        "tags": ["7. HR & Payroll"],
                        "summary": "Tambah Karyawan Baru",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "201": { "description": "Karyawan baru dibuat" } }
                    }
                },
                "/v1/assets": {
                    "get": {
                        "tags": ["8. Fixed Assets"],
                        "summary": "Daftar Aset Tetap & Nilai Buku",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "List aset tetap" } }
                    }
                },
                "/v1/finance/coa": {
                    "get": {
                        "tags": ["9. Finance & Accounting"],
                        "summary": "Chart of Accounts (COA)",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "Daftar akun perkiraan" } }
                    }
                },
                "/v1/finance/reports/income-statement": {
                    "get": {
                        "tags": ["9. Finance & Accounting"],
                        "summary": "Laporan Laba Rugi Real-Time",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "Financial income statement" } }
                    }
                },
                "/v1/procurement/suppliers": {
                    "get": {
                        "tags": ["10. Procurement"],
                        "summary": "Daftar Supplier Pemasok",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "List supplier" } }
                    }
                },
                "/v1/shifts/open": {
                    "post": {
                        "tags": ["11. Cashier Shifts"],
                        "summary": "Buka Shift Kasir Baru",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "201": { "description": "Shift kasir dibuka" } }
                    }
                },
                "/v1/shifts/close": {
                    "post": {
                        "tags": ["11. Cashier Shifts"],
                        "summary": "Tutup Shift Kasir & Audit Kas",
                        "security": [{ "BearerAuth": [] }],
                        "responses": { "200": { "description": "Shift kasir ditutup" } }
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
