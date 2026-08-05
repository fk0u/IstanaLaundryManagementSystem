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
            <span class="brand-badge">100% Complete RESTful API v1.0</span>
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
                "title": "Istana Laundry 100% Feature-Complete RESTful API",
                "version": "1.0.0",
                "description": "Dokumentasi RESTful API 100% lengkap untuk Istana Laundry Management System. Meliputi seluruh modul ERP: Public, Auth, Dashboard, Orders, Refunds, Struk Thermal, CRM, Inventory, HR, Assets & Depresiasi, Finance, Expenses, Supplier Debt, Procurement & Approvals, Cashier Shifts, Master Data, User/RBAC, dan Performance KPIs."
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
                        "description": "Masukkan Token Sanctum dari endpoint /login"
                    }
                }
            },
            "paths": {
                "/v1/branches": {
                    "get": { "tags": ["1. Public API v1"], "summary": "Daftar Outlet Cabang Aktif", "responses": { "200": { "description": "Success" } } }
                },
                "/v1/services": {
                    "get": { "tags": ["1. Public API v1"], "summary": "Daftar Layanan & Tarif", "responses": { "200": { "description": "Success" } } }
                },
                "/v1/track/{orderNumber}": {
                    "get": { "tags": ["1. Public API v1"], "summary": "Lacak Status Order", "parameters": [{ "name": "orderNumber", "in": "path", "required": true, "schema": { "type": "string" } }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/orders/online": {
                    "post": { "tags": ["1. Public API v1"], "summary": "Submit Order Online dengan Presisi GPS", "responses": { "201": { "description": "Created" } } }
                },
                "/login": {
                    "post": { "tags": ["2. Staff Authentication"], "summary": "Staff Login", "responses": { "200": { "description": "Bearer Token Sanctum" } } }
                },
                "/me": {
                    "get": { "tags": ["2. Staff Authentication"], "summary": "Profil User Staf Login", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/dashboard/stats": {
                    "get": { "tags": ["3. Dashboard & Analytics"], "summary": "Executive KPI Stats", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/dashboard/charts": {
                    "get": { "tags": ["3. Dashboard & Analytics"], "summary": "Grafik Omzet Bulanan", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/orders": {
                    "get": { "tags": ["4. Orders & Refunds"], "summary": "List Transaksi Order", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/orders/{order}": {
                    "get": { "tags": ["4. Orders & Refunds"], "summary": "Detail Transaksi Order", "security": [{ "BearerAuth": [] }], "parameters": [{ "name": "order", "in": "path", "required": true, "schema": { "type": "integer" } }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/orders/{order}/payments": {
                    "post": { "tags": ["4. Orders & Refunds"], "summary": "Pelunasan / Cicilan Pembayaran", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/orders/{order}/refund": {
                    "post": { "tags": ["4. Orders & Refunds"], "summary": "Permohonan Refund / Pembatalan Order", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/orders/{order}/receipt-data": {
                    "get": { "tags": ["4. Orders & Refunds"], "summary": "Data Terstruktur Printer Thermal ESC/POS", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/customers": {
                    "get": { "tags": ["5. CRM Customers"], "summary": "List & Cari Member Pelanggan", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["5. CRM Customers"], "summary": "Daftarkan Member Baru", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/customers/{customer}": {
                    "get": { "tags": ["5. CRM Customers"], "summary": "Detail Member & Histori Poin", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "put": { "tags": ["5. CRM Customers"], "summary": "Update Profil Member", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "delete": { "tags": ["5. CRM Customers"], "summary": "Hapus Member", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/inventory": {
                    "get": { "tags": ["6. Inventory & Stock"], "summary": "List Stok Bahan & Low Stock Alert", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["6. Inventory & Stock"], "summary": "Tambah Item Stok", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/hr/employees": {
                    "get": { "tags": ["7. HR & Payroll"], "summary": "List Karyawan Aktif", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["7. HR & Payroll"], "summary": "Tambah Karyawan Baru", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/hr/payrolls": {
                    "get": { "tags": ["7. HR & Payroll"], "summary": "Histori Penggajian", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["7. HR & Payroll"], "summary": "Generate Slip Gaji Bulanan", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/assets": {
                    "get": { "tags": ["8. Fixed Assets"], "summary": "List Aset Tetap & Nilai Buku", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["8. Fixed Assets"], "summary": "Registrasi Aset Tetap Baru", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/assets/depreciate": {
                    "post": { "tags": ["8. Fixed Assets"], "summary": "Eksekusi Jurnal Depresiasi Bulanan", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/finance/coa": {
                    "get": { "tags": ["9. Finance & Accounting"], "summary": "Chart of Accounts (COA)", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["9. Finance & Accounting"], "summary": "Tambah Akun COA Baru", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/finance/expenses": {
                    "get": { "tags": ["9. Finance & Accounting"], "summary": "List Pengeluaran Operasional", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["9. Finance & Accounting"], "summary": "Pencatatan Beban Operasional Baru", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/finance/supplier-payments": {
                    "get": { "tags": ["9. Finance & Accounting"], "summary": "List Pembayaran Utang Supplier", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["9. Finance & Accounting"], "summary": "Catat Pelunasan Utang Supplier", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/finance/reports/income-statement": {
                    "get": { "tags": ["9. Finance & Accounting"], "summary": "Laporan Laba Rugi Real-Time", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/finance/reports/balance-sheet": {
                    "get": { "tags": ["9. Finance & Accounting"], "summary": "Laporan Neraca Keuangan", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/procurement/purchase-requests/{purchaseRequest}/approve": {
                    "put": { "tags": ["10. Procurement & Approvals"], "summary": "Persetujuan / Penolakan PR", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/procurement/purchase-orders": {
                    "get": { "tags": ["10. Procurement & Approvals"], "summary": "List Purchase Order (PO)", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["10. Procurement & Approvals"], "summary": "Buat PO Baru dari PR", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/shifts/open": {
                    "post": { "tags": ["11. Cashier Shifts"], "summary": "Buka Shift Kasir Baru", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/shifts/close": {
                    "post": { "tags": ["11. Cashier Shifts"], "summary": "Tutup Shift Kasir & Audit Setoran", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/users": {
                    "get": { "tags": ["12. User & RBAC Management"], "summary": "List Akun Staf User", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } },
                    "post": { "tags": ["12. User & RBAC Management"], "summary": "Buat Akun Staf Baru", "security": [{ "BearerAuth": [] }], "responses": { "201": { "description": "Created" } } }
                },
                "/v1/performance/cashiers": {
                    "get": { "tags": ["13. Performance Metrics"], "summary": "Statistik KPI Performa Kasir", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
                },
                "/v1/audit-logs": {
                    "get": { "tags": ["13. Performance Metrics"], "summary": "Audit Trail Aktivitas Sistem", "security": [{ "BearerAuth": [] }], "responses": { "200": { "description": "Success" } } }
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
