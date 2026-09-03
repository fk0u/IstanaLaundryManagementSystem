<?php

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$html = <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Spesifikasi Fitur, RBAC & Akun Pengguna - Istana Laundry ERP</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
                font-size: 8pt;
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                color: #64748b;
            }
            @bottom-left {
                content: "Istana Laundry ERP — Spesifikasi Fitur, RBAC & Kredensial Akun";
                font-size: 8pt;
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                color: #64748b;
            }
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.45;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }

        /* Cover Page Styling */
        .cover-container {
            text-align: center;
            padding-top: 2.5cm;
            padding-bottom: 1.5cm;
        }
        .cover-badge {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 8.5pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 18px;
            border: 1px solid #bae6fd;
        }
        .cover-title {
            font-size: 23pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        .cover-subtitle {
            font-size: 11.5pt;
            color: #475569;
            font-weight: 400;
            margin-bottom: 28px;
            line-height: 1.4;
        }
        .cover-divider {
            width: 80px;
            height: 4px;
            background-color: #0284c7;
            margin: 0 auto 28px auto;
            border-radius: 2px;
        }
        .meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            width: 90%;
            margin: 0 auto;
            padding: 16px 20px;
            text-align: left;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 6px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .meta-label {
            width: 32%;
            font-weight: bold;
            color: #475569;
        }
        .meta-value {
            color: #0f172a;
        }

        /* Typography & Headings */
        h1 {
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        h2 {
            font-size: 11pt;
            font-weight: 700;
            color: #0369a1;
            margin-top: 14px;
            margin-bottom: 8px;
            border-left: 3.5px solid #0284c7;
            padding-left: 7px;
        }
        h3 {
            font-size: 9.5pt;
            font-weight: 700;
            color: #334155;
            margin-top: 12px;
            margin-bottom: 5px;
        }
        p {
            margin-top: 0;
            margin-bottom: 8px;
            text-align: justify;
        }

        /* Callout / Alert Box */
        .callout {
            background-color: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 8px 12px;
            border-radius: 0 5px 5px 0;
            margin-bottom: 12px;
            font-size: 8.5pt;
            color: #0c4a6e;
        }
        .callout-title {
            font-weight: bold;
            margin-bottom: 3px;
            display: block;
        }

        .callout-warning {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            color: #78350f;
        }

        .callout-success {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            color: #14532d;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
            font-size: 8pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            text-align: left;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 700;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.data-table tr.highlight-row {
            background-color: #e0f2fe;
            font-weight: bold;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-check {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .badge-none {
            background-color: #f1f5f9;
            color: #94a3b8;
        }
        .badge-role {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            font-family: monospace;
        }
        .badge-global {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-scoped {
            background-color: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }
        .badge-pwd {
            background-color: #f1f5f9;
            color: #b91c1c;
            border: 1px solid #fecaca;
            font-family: monospace;
            font-weight: bold;
        }

        /* Lists */
        ul, ol {
            margin-top: 3px;
            margin-bottom: 8px;
            padding-left: 18px;
        }
        li {
            margin-bottom: 3px;
        }

        /* Footer Info */
        .footer-note {
            font-size: 7.5pt;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 20px;
        }

        .feature-card {
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            border-radius: 5px;
            padding: 8px 12px;
            margin-bottom: 10px;
        }
        .feature-card-header {
            font-weight: bold;
            font-size: 9pt;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .feature-endpoint {
            font-family: monospace;
            font-size: 7.5pt;
            background-color: #f1f5f9;
            color: #0369a1;
            padding: 1px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>

    <!-- ==================== COVER PAGE ==================== -->
    <div class="cover-container">
        <div class="cover-badge">Enterprise Documentation · Sistem ERP</div>
        <div class="cover-title">SPESIFIKASI FITUR, MATRIKS RBAC<br>&amp; KREDENSIAL AKUN SISTEM</div>
        <div class="cover-subtitle">
            Katalog Fitur Lengkap, Matriks Role-Based Access Control, dan Daftar Akun Pengguna Resmi<br>
            <strong>Istana Laundry Management System</strong>
        </div>
        <div class="cover-divider"></div>

        <div class="meta-card">
            <table class="meta-table">
                <tr>
                    <td class="meta-label">Nama Sistem:</td>
                    <td class="meta-value"><strong>Istana Laundry Management System (Semi-ERP)</strong></td>
                </tr>
                <tr>
                    <td class="meta-label">Klien / Bisnis:</td>
                    <td class="meta-value">Istana Premium Laundry Service (Samarinda, Kalimantan Timur)</td>
                </tr>
                <tr>
                    <td class="meta-label">Versi Dokumen:</td>
                    <td class="meta-value"><strong>Versi 3.3 (Full Enterprise Credentials Edition)</strong></td>
                </tr>
                <tr>
                    <td class="meta-label">Tanggal Pembaruan:</td>
                    <td class="meta-value">27 Agustus 2026</td>
                </tr>
                <tr>
                    <td class="meta-label">Default Password:</td>
                    <td class="meta-value"><span class="badge badge-pwd">password</span> (Default untuk seluruh akun seeded)</td>
                </tr>
                <tr>
                    <td class="meta-label">Status Sistem:</td>
                    <td class="meta-value"><span class="badge badge-check">100% LIVE PRODUCTION &amp; RESTFUL API READY</span></td>
                </tr>
                <tr>
                    <td class="meta-label">Official Hotline:</td>
                    <td class="meta-value">+62 811-5599-199 (Customer Care / Admin Pusat)</td>
                </tr>
                <tr>
                    <td class="meta-label">URL Production:</td>
                    <td class="meta-value">https://istanasystem.alk-tech.my.id</td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 35px; font-size: 8pt; color: #64748b;">
            Dokumen Rahasia Internal · Digunakan untuk Operasional, Onboarding Staf, Audit Sistem, dan Tim Teknis
        </div>
    </div>

    <div class="page-break"></div>

    <!-- ==================== BAB 1: RINGKASAN EKSEKUTIF ==================== -->
    <h1>1. Ringkasan Eksekutif &amp; Arsitektur Sistem</h1>

    <h2>1.1 Gambaran Umum Produk</h2>
    <p>
        <strong>Istana Laundry Management System</strong> adalah sistem Enterprise Resource Planning (Semi-ERP) berbasis web multi-cabang terintegrasi yang dirancang untuk mengotomasi seluruh siklus bisnis operasional laundry komersial modern kelas premium di Kota Samarinda, Kalimantan Timur.
    </p>
    <p>
        Sistem ini mengintegrasikan seluruh rantai bisnis secara real-time: transaksi kasir (POS), alur workshop produksi cucian 8-stasiun, sistem loyalitas &amp; keanggotaan pelanggan (CRM), rantai pasok &amp; inventori (Procurement &amp; FIFO Stock), pencatatan akuntansi double-entry otomatis, pengelolaan penggajian SDM (HR &amp; Payroll), depresiasi aset tetap, hingga dashboard eksekutif multi-peran.
    </p>

    <div class="callout callout-success">
        <span class="callout-title">Prinsip Desain &amp; Ketahanan Sistem:</span>
        Sistem menerapkan prinsip <strong>Zero-Trust Security</strong>, <strong>Multi-Tenant Branch Data Isolation</strong>, <strong>Idempotent Financial Journaling</strong>, dan <strong>High-Performance RESTful API Architecture</strong> untuk menjamin akurasi data serta keandalan operasional tingkat tinggi.
    </div>

    <h2>1.2 Arsitektur Multi-Cabang (Multi-Tenancy Scoping)</h2>
    <p>
        Sistem menggunakan pola arsitektur <em>Single-Database Multi-Tenant Lightweight Isolation</em>. Setiap data operasional (transaksi order, antrean produksi, stok barang, mutasi jurnal, absensi karyawan, hingga aset tetap) diikat dengan kolom <span class="badge badge-role">branch_id</span>.
    </p>
    <ul>
        <li><strong>Automated Eloquent Global Scope:</strong> Model Eloquent yang mengimplementasikan Trait <code>BranchScoped</code> secara otomatis menyaring kueri database berdasarkan cabang aktif pengguna yang terautentikasi.</li>
        <li><strong>Middleware Branch Enforcement:</strong> <code>BranchScopeMiddleware</code> memastikan seluruh rute HTTP web dan API v1 memvalidasi parameter cabang dan mencegah kebocoran data antar outlet.</li>
        <li><strong>Dynamic Scope Switching:</strong> Peran tingkat eksekutif (Developer, Owner, Super_Admin, Finance) diberikan wewenang untuk beralih konteks cabang (Switch Branch) atau melihat agregat seluruh outlet (Global Scope).</li>
    </ul>

    <h2>1.3 Daftar 5 Outlet Fisik Resmi di Samarinda</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Kode</th>
                <th style="width: 25%;">Nama Outlet</th>
                <th style="width: 35%;">Alamat Lengkap</th>
                <th style="width: 15%;">Telepon</th>
                <th style="width: 15%;">Email Cabang</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>WJK</strong></td>
                <td>Istana Laundry - Wijaya Kusuma (Pusat)</td>
                <td>Jl. Wijaya Kusuma Blok V-C Gg. Rina, Samarinda Ulu</td>
                <td>08115550001</td>
                <td>wjk@istanalaundry.com</td>
            </tr>
            <tr>
                <td><strong>SUT</strong></td>
                <td>Istana Laundry - Dr. Sutomo</td>
                <td>Jl. Dr. Sutomo, Sidodadi, Samarinda Ulu</td>
                <td>08115550002</td>
                <td>sutomo@istanalaundry.com</td>
            </tr>
            <tr>
                <td><strong>HID</strong></td>
                <td>Istana Laundry - P. Hidayatullah</td>
                <td>Jl. Pangeran Hidayatullah, Karang Mumus, Samarinda Kota</td>
                <td>08115550003</td>
                <td>hidayatullah@istanalaundry.com</td>
            </tr>
            <tr>
                <td><strong>LMG</strong></td>
                <td>Istana Laundry - Lambung Mangkurat</td>
                <td>Jl. Lambung Mangkurat, Sungai Pinang Dalam, Sungai Pinang</td>
                <td>08115550004</td>
                <td>lambung@istanalaundry.com</td>
            </tr>
            <tr>
                <td><strong>GTS</strong></td>
                <td>Istana Laundry - Grand Taman Sari</td>
                <td>Kawasan Perumahan Grand Taman Sari, Harapan Baru, Loa Janan Ilir</td>
                <td>08115550005</td>
                <td>gts@istanalaundry.com</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- ==================== BAB 2: DAFTAR AKUN PENGGUNA RESMI ==================== -->
    <h1>2. Daftar Lengkap Akun Pengguna, Password &amp; Kredensial RBAC</h1>

    <p>
        Berikut adalah daftar lengkap <strong>18 Akun Pengguna Resmi</strong> yang telah terdaftar pada database sistem (<code>UserSeeder</code> &amp; <code>ERPDataSeeder</code>), lengkap dengan informasi peran (Role), email login, password default, NIK karyawan, jabatan resmi, dan gaji pokok terdaftar.
    </p>

    <div class="callout callout-warning">
        <span class="callout-title">Catatan Keamanan Kredensial:</span>
        Seluruh akun awal di-set menggunakan password standar: <span class="badge badge-pwd">password</span>. Setiap staf disarankan mengaktifkan <strong>Two-Factor Authentication (2FA)</strong> melalui menu profil setelah login pertama kali.
    </div>

    <h2>2.1 Akun Tingkat Eksekutif &amp; Global (Super Level Users)</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Role</th>
                <th style="width: 25%;">Nama Lengkap &amp; NIK</th>
                <th style="width: 28%;">Email Login</th>
                <th style="width: 12%;">Password</th>
                <th style="width: 20%;">Jabatan &amp; Scope</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge badge-role">Developer</span></td>
                <td><strong>Rian Ardiansyah</strong><br><small>NIK-DEV-0001</small></td>
                <td><code>developer@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Developer Utama<br><span class="badge badge-global">Global (All Scope)</span></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Owner</span></td>
                <td><strong>H. Bambang Setiawan, S.E.</strong><br><small>NIK-OWN-0001</small></td>
                <td><code>owner@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Pemilik Usaha / Direksi<br><span class="badge badge-global">Global (Switchable)</span></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Super_Admin</span></td>
                <td><strong>Siti Nurhaliza, M.M.</strong><br><small>NIK-ADM-0001</small></td>
                <td><code>superadmin@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Super Administrator Pusat<br><span class="badge badge-global">Global (Switchable)</span></td>
            </tr>
        </tbody>
    </table>

    <h2>2.2 Akun Cabang Wijaya Kusuma (Pusat - WJK)</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Role</th>
                <th style="width: 25%;">Nama Lengkap &amp; NIK</th>
                <th style="width: 28%;">Email Login</th>
                <th style="width: 12%;">Password</th>
                <th style="width: 20%;">Jabatan &amp; Bank</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge badge-role">Branch_Admin</span></td>
                <td><strong>Rahmat Hidayat</strong><br><small>NIK-WJK-0001</small></td>
                <td><code>admin.wjk@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Manager Cabang WJK<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Cashier</span></td>
                <td><strong>Dewi Anggraini</strong><br><small>NIK-WJK-0002</small></td>
                <td><code>cashier.wjk@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Kasir Senior WJK<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Workshop_Admin</span></td>
                <td><strong>Agus Prasetyo</strong><br><small>NIK-WJK-0003</small></td>
                <td><code>workshop.admin.wjk@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Supervisor Workshop WJK<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Workshop_Staff</span></td>
                <td><strong>Budi Santoso</strong><br><small>NIK-WJK-0004</small></td>
                <td><code>staff.wjk@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Operator Cuci &amp; Setrika<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">CS_Marketing</span></td>
                <td><strong>Indah Permatasari</strong><br><small>NIK-WJK-0005</small></td>
                <td><code>marketing.wjk@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Staf CS &amp; Marketing<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Finance</span></td>
                <td><strong>Sri Wahyuni, A.Md.</strong><br><small>NIK-WJK-0006</small></td>
                <td><code>finance.wjk@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Kepala Akuntan &amp; Finance<br><span class="badge badge-global">Global (Switchable)</span></td>
            </tr>
        </tbody>
    </table>

    <h2>2.3 Akun Cabang Dr. Sutomo (SUT)</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Role</th>
                <th style="width: 25%;">Nama Lengkap &amp; NIK</th>
                <th style="width: 28%;">Email Login</th>
                <th style="width: 12%;">Password</th>
                <th style="width: 20%;">Jabatan &amp; Bank</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge badge-role">Branch_Admin</span></td>
                <td><strong>Eko Kurniawan</strong><br><small>NIK-SUT-0001</small></td>
                <td><code>admin.sut@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Manager Cabang Sutomo<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Cashier</span></td>
                <td><strong>Nia Ramadhani</strong><br><small>NIK-SUT-0002</small></td>
                <td><code>cashier.sut@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Kasir Utama Sutomo<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Workshop_Staff</span></td>
                <td><strong>Dedi Kurnia</strong><br><small>NIK-SUT-0003</small></td>
                <td><code>staff.sut@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Operator Workshop Sutomo<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
        </tbody>
    </table>

    <h2>2.4 Akun Cabang Pangeran Hidayatullah (HID)</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Role</th>
                <th style="width: 25%;">Nama Lengkap &amp; NIK</th>
                <th style="width: 28%;">Email Login</th>
                <th style="width: 12%;">Password</th>
                <th style="width: 20%;">Jabatan &amp; Bank</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge badge-role">Branch_Admin</span></td>
                <td><strong>Fajar Nugraha</strong><br><small>NIK-HID-0001</small></td>
                <td><code>admin.hid@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Manager Cabang Hidayatullah<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Cashier</span></td>
                <td><strong>Rina Astuti</strong><br><small>NIK-HID-0002</small></td>
                <td><code>cashier.hid@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Kasir Utama Hidayatullah<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Workshop_Staff</span></td>
                <td><strong>Ahmad Fauzi</strong><br><small>NIK-HID-0003</small></td>
                <td><code>staff.hid@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Operator Workshop Hidayatullah<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
        </tbody>
    </table>

    <h2>2.5 Akun Cabang Lambung Mangkurat (LMG)</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Role</th>
                <th style="width: 25%;">Nama Lengkap &amp; NIK</th>
                <th style="width: 28%;">Email Login</th>
                <th style="width: 12%;">Password</th>
                <th style="width: 20%;">Jabatan &amp; Bank</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge badge-role">Branch_Admin</span></td>
                <td><strong>Hendra Kusuma</strong><br><small>NIK-LMG-0001</small></td>
                <td><code>admin.lmg@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Manager Cabang Lambung<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Cashier</span></td>
                <td><strong>Maya Safitri</strong><br><small>NIK-LMG-0002</small></td>
                <td><code>cashier.lmg@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Kasir Utama Lambung<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
            <tr>
                <td><span class="badge badge-role">Workshop_Staff</span></td>
                <td><strong>Rizky Febrian</strong><br><small>NIK-LMG-0003</small></td>
                <td><code>staff.lmg@istanalaundry.com</code></td>
                <td><span class="badge badge-pwd">password</span></td>
                <td>Operator Workshop Lambung<br><small>BCA 8830-xxxxxx</small></td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- ==================== BAB 3: MATRIKS RBAC ==================== -->
    <h1>3. Matriks Hak Akses Granular (RBAC Matrix)</h1>

    <p>
        Tabel berikut merinci pemetaan antara <strong>32 Granular Permissions</strong> dengan <strong>9 System Roles</strong> yang dikonfigurasi melalui seeder <code>RolePermissionSeeder</code> dan dienkapsulasi menggunakan middleware Spatie.
    </p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 32%;">Permission Name / Category</th>
                <th style="width: 7%; text-align:center;">DEV</th>
                <th style="width: 7%; text-align:center;">OWN</th>
                <th style="width: 7%; text-align:center;">SUP</th>
                <th style="width: 7%; text-align:center;">ADM</th>
                <th style="width: 8%; text-align:center;">W-ADM</th>
                <th style="width: 7%; text-align:center;">CSH</th>
                <th style="width: 8%; text-align:center;">W-STF</th>
                <th style="width: 7%; text-align:center;">CSM</th>
                <th style="width: 7%; text-align:center;">FIN</th>
            </tr>
        </thead>
        <tbody>
            <!-- POS & Orders -->
            <tr class="highlight-row">
                <td colspan="10"><strong>A. POS &amp; ORDER MANAGEMENT</strong></td>
            </tr>
            <tr>
                <td><code>orders.view</code> (Lihat Daftar &amp; Detail Transaksi)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>
            <tr>
                <td><code>orders.create</code> (Input Transaksi POS Baru)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>orders.update</code> (Edit / Update Pembayaran Order)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>orders.delete</code> (Hapus Transaksi - Hard Delete)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>orders.refund</code> (Pengajuan &amp; Approval Refund)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>

            <!-- Production -->
            <tr class="highlight-row">
                <td colspan="10"><strong>B. WORKSHOP &amp; PRODUCTION</strong></td>
            </tr>
            <tr>
                <td><code>production.view</code> (Lihat Antrean Produksi Cucian)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>production.update</code> (Update Status Stasiun Cucian)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>production.bulk_update</code> (Update Status Massal)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>

            <!-- CRM & Loyalty -->
            <tr class="highlight-row">
                <td colspan="10"><strong>C. CRM, CUSTOMER &amp; LOYALTY</strong></td>
            </tr>
            <tr>
                <td><code>customers.view</code> (Lihat Data Pelanggan)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>customers.create</code> (Tambah Pelanggan Baru)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>customers.update</code> (Edit Biodata Pelanggan)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>customers.delete</code> (Hapus Data Pelanggan)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>loyalty.manage</code> (Kelola Poin, Tier &amp; Penukaran)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>

            <!-- Inventory & Procurement -->
            <tr class="highlight-row">
                <td colspan="10"><strong>D. INVENTORY, FIFO STOCK &amp; PROCUREMENT</strong></td>
            </tr>
            <tr>
                <td><code>inventory.view</code> (Lihat Stok Bahan &amp; Log FIFO)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>inventory.create</code> (Tambah Item BHP Baru)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>inventory.update</code> (Koreksi / Adjust Stok BHP)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>purchase_requests.approve</code> (Approval Pengajuan PR)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>

            <!-- Finance & Accounting -->
            <tr class="highlight-row">
                <td colspan="10"><strong>E. FINANCE &amp; ACCOUNTING</strong></td>
            </tr>
            <tr>
                <td><code>journals.view</code> (Lihat Buku Jurnal &amp; Buku Besar)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>
            <tr>
                <td><code>journals.create</code> (Input Jurnal Umum Manual)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>
            <tr>
                <td><code>journals.post</code> (Posting Jurnal Transaksi)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>
            <tr>
                <td><code>journals.reverse</code> (Pembatalan / Reversal Jurnal)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>
            <tr>
                <td><code>accounting_periods.close</code> (Tutup Periode Buku)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>

            <!-- HR & Payroll -->
            <tr class="highlight-row">
                <td colspan="10"><strong>F. HR &amp; PAYROLL MANAGEMENT</strong></td>
            </tr>
            <tr>
                <td><code>employees.manage</code> (Kelola Biodata &amp; Akun Staf)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>attendances.manage</code> (Input &amp; Verifikasi Presensi)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>payroll.manage</code> (Generate, Finalize &amp; Slip Gaji)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>

            <!-- Fixed Assets -->
            <tr class="highlight-row">
                <td colspan="10"><strong>G. FIXED ASSETS &amp; DEPRECIATION</strong></td>
            </tr>
            <tr>
                <td><code>assets.manage</code> (Master Aset &amp; Maintenance Log)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>
            <tr>
                <td><code>depreciation.process</code> (Proses Penyusutan Bulanan)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>

            <!-- Reports & Analytics -->
            <tr class="highlight-row">
                <td colspan="10"><strong>H. REPORTS &amp; ANALYTICS</strong></td>
            </tr>
            <tr>
                <td><code>reports.sales</code> (Laporan Omset &amp; Penjualan)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>
            <tr>
                <td><code>reports.production</code> (Laporan Throughput &amp; Lead Time)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>reports.finance</code> (Laba Rugi, Neraca, Neraca Saldo)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>
            <tr>
                <td><code>reports.export</code> (Ekspor PDF, Excel, CSV)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
            </tr>

            <!-- Master Data & Governance -->
            <tr class="highlight-row">
                <td colspan="10"><strong>I. MASTER DATA &amp; SYSTEM GOVERNANCE</strong></td>
            </tr>
            <tr>
                <td><code>services.manage</code> (Master Layanan &amp; Tarif Cabang)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>branches.manage</code> (Master Cabang &amp; Scope Switch)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>users.manage</code> (Manajemen Staf &amp; Reset Password)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
            <tr>
                <td><code>roles.manage</code> (Konfigurasi RBAC &amp; Permissions)</td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-check">✓</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
                <td style="text-align:center;"><span class="badge badge-none">-</span></td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- ==================== BAB 4: RINCIAN LENGKAP FITUR ==================== -->
    <h1>4. Rincian Lengkap Fitur per Modul Bisnis</h1>

    <p>
        Bagian ini menjabarkan seluruh fungsionalitas, logika bisnis, dan integrasi teknis yang telah selesai dibangun dan beroperasi pada sistem.
    </p>

    <!-- MODUL 1 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 1: AUTENTIKASI, KEAMANAN TINGKAT TINGGI &amp; PROFILING</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>Two-Factor Authentication (2FA TOTP):</strong> Algoritma RFC 6238 via Google Authenticator, QR Code setup, dan 8 recovery codes.</li>
            <li><strong>2FA Login Challenge &amp; Trust Device:</strong> Opsi <em>"Percayai Perangkat 30 Hari"</em> dengan token SHA-256 pada <code>user_trusted_devices</code> dan cookie <code>2fa_device_trust</code>.</li>
            <li><strong>Kompresi Foto Profil WebP Dinamis:</strong> Konversi otomatis avatar ke <code>.webp</code> berkualitas tinggi berukuran ≤ 200KB.</li>
            <li><strong>Proteksi Header Keamanan (SecurityHeadersMiddleware):</strong> Pemasangan HSTS, CSP, X-Frame-Options, X-Content-Type-Options, X-XSS-Protection.</li>
            <li><strong>Jejak Audit Komprehensif (Audit Logging):</strong> Pencatatan login, logout, create, update, delete, status shift, dan mutasi finansial dengan IP Address dan User-Agent.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">POST /login</span> · <span class="feature-endpoint">POST /two-factor-challenge</span> · <span class="feature-endpoint">POST /profile/2fa/enable</span> · <span class="feature-endpoint">POST /profile/avatar</span>
        </div>
    </div>

    <!-- MODUL 2 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 2: POINT OF SALE (POS) &amp; TRANSAKSI KASIR HARIAN</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>Layar Kasir Cepat (Desktop &amp; Tablet-Ready):</strong> Katalog Kiloan, Satuan, Karpet/Gorden, Express dengan penetapan harga dinamis per cabang (<code>ServiceBranchPrice</code>).</li>
            <li><strong>Pendaftaran Pelanggan Instan:</strong> Pencarian live dan modal registrasi pelanggan baru langsung dari layar POS tanpa reload.</li>
            <li><strong>Multi-Metode Pembayaran:</strong> Tunai (kalkulator kembalian cepat), Transfer Bank, QRIS, dan Pembayaran Sebagian/Piutang.</li>
            <li><strong>Sequence Locked Order Numbering:</strong> Penomoran nota anti-bentrok per cabang (contoh: <code>ORD-WJK-202608-00042</code>).</li>
            <li><strong>Manajemen Shift Kasir &amp; Rekonsiliasi:</strong> Buka/tutup shift kasir, input kas fisik, kalkulasi selisih kas (over/short), kas kecil (Petty Cash), dan ekspor PDF ringkasan shift.</li>
            <li><strong>Cetak Nota &amp; Integrasi WhatsApp:</strong> Struk thermal 58mm/80mm, faktur A4, dan kirim pesan nota WhatsApp resmi (+62 811-5599-199).</li>
            <li><strong>Draft / Hold Order:</strong> Simpan transaksi sementara dan lanjutkan transaksi kapan saja.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">GET|POST /pos</span> · <span class="feature-endpoint">POST /pos/shift/open</span> · <span class="feature-endpoint">POST /pos/shift/close</span> · <span class="feature-endpoint">GET /invoices/{order}/receipt</span>
        </div>
    </div>

    <!-- MODUL 3 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 3: WORKSHOP PRODUCTION &amp; REALTIME ORDER TRACKING</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>Alur Produksi Linear 8-Stasiun:</strong> <code>TERIMA → PILAH → CUCI → KERING → LIPAT → CEK → SIAP → DIAMBIL</code>.</li>
            <li><strong>Penegakan Aturan Transisi (Linear Enforcement):</strong> <code>ProductionService</code> mencegah cucian melompati urutan pengerjaan stasiun.</li>
            <li><strong>Audit Log Stasiun (ProductionStatusLog):</strong> Rekam waktu pengerjaan, operator stasiun, dan catatan kendala teknis kain.</li>
            <li><strong>Portal Lacak Cucian Publik:</strong> Lacak nota secara mandiri melalui <code>/track?order_number=...</code> atau REST API <code>/api/v1/track</code>.</li>
            <li><strong>Notifikasi Otomatis Cucian Selesai:</strong> Tombol kirim notifikasi WhatsApp otomatis saat status mencapai <strong>SIAP</strong>.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">GET /production</span> · <span class="feature-endpoint">POST /production/update/{id}</span> · <span class="feature-endpoint">GET /track</span> · <span class="feature-endpoint">GET /invoices/{order}/ready-whatsapp</span>
        </div>
    </div>

    <!-- MODUL 4 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 4: CRM, MEMBERSHIP TIERING &amp; LOYALTY PROMOTIONS</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>4-Tier Keanggotaan:</strong> Kategori otomatis berjenjang (<strong>Bronze, Silver, Gold, Platinum</strong>) berdasarkan akumulasi transaksi.</li>
            <li><strong>Engine Poin Loyalitas Otomatis:</strong> Perolehan poin belanja dan penukaran langsung sebagai potongan nota di kasir.</li>
            <li><strong>Ledger &amp; Koreksi Poin (LoyaltyPointLog):</strong> Riwayat mutasi perolehan/pemakaian poin serta penyesuaian poin manual oleh Admin.</li>
            <li><strong>Engine Kupon Promosi Lanjutan:</strong> Diskon % atau nominal Rp, minimal belanja, kuota per pelanggan, dan pembatasan member baru/lama.</li>
            <li><strong>Ekspor Database Pelanggan:</strong> Unduh database pelanggan beserta saldo poin dalam format CSV, PDF, dan Excel.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">GET|POST /customers</span> · <span class="feature-endpoint">POST /customers/{id}/adjust-points</span> · <span class="feature-endpoint">GET|POST /promotions</span> · <span class="feature-endpoint">GET /customers/export/xlsx</span>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- MODUL 5 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 5: INVENTORY, FIFO STOCK &amp; SIKLUS PENGADAAN (PROCUREMENT)</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>Master Bahan Habis Pakai (BHP):</strong> Stok deterjen, softener, parfum, plastik packing, hanger per cabang dengan alert batas minimum.</li>
            <li><strong>Siklus Pengadaan 3-Tahap Terintegrasi:</strong>
                <ol>
                    <li><em>Purchase Request (PR):</em> Pengajuan kebutuhan dari cabang &amp; approval berjenjang Super Admin / Owner.</li>
                    <li><em>Purchase Order (PO):</em> Penerbitan PO resmi ke supplier, cetak PO PDF, kirim via WhatsApp, dan pantau pengiriman.</li>
                    <li><em>Goods Received Note (GRN):</em> Penerimaan fisik barang, auto-update stok inventori (FIFO batch), dan posting otomatis jurnal Utang Usaha / Persediaan.</li>
                </ol>
            </li>
            <li><strong>Master Supplier:</strong> Database pemasok lengkap dengan kontak, alamat, termin bayar, dan histori transaksi.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">GET /inventory</span> · <span class="feature-endpoint">GET|POST /procurement/purchase-requests</span> · <span class="feature-endpoint">GET|POST /procurement/purchase-orders</span> · <span class="feature-endpoint">GET|POST /procurement/grns</span>
        </div>
    </div>

    <!-- MODUL 6 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 6: FINANCE, ACCOUNTING &amp; AUTOMATED DOUBLE-ENTRY LEDGER</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>Chart of Accounts (COA) Standar:</strong> Struktur hierarki akun Level 1–4 untuk 5 kelompok akun standar dengan saldo Debit/Kredit.</li>
            <li><strong>Penjurnalan Otomatis Berbasis Event Bisnis:</strong>
                <ul>
                    <li><em>Order Lunas:</em> Debit Kas/Bank vs Kredit Pendapatan Laundry.</li>
                    <li><em>Penerimaan GRN:</em> Debit Persediaan Bahan vs Kredit Utang Usaha.</li>
                    <li><em>Finalisasi Payroll:</em> Debit Beban Gaji vs Kredit Utang Gaji / Kas.</li>
                    <li><em>Depresiasi Aset:</em> Debit Beban Penyusutan vs Kredit Akumulasi Penyusutan.</li>
                    <li><em>Beban Operasional:</em> Debit Beban Operasional vs Kredit Kas Kecil.</li>
                    <li><em>Pelunasan Supplier:</em> Debit Utang Usaha vs Kredit Kas/Bank.</li>
                </ul>
            </li>
            <li><strong>Jurnal Umum Manual &amp; Reversal:</strong> Fasilitas input mutasi seimbang dan pembatalan jurnal berjejak audit.</li>
            <li><strong>Tutup Buku &amp; Closing Checklist:</strong> Penguncian periode akuntansi bulanan (Accounting Period) untuk integritas audit.</li>
            <li><strong>Laporan Keuangan Eksekutif:</strong> Laba Rugi, Neraca, Neraca Saldo, Buku Besar (CSV, Excel, PDF Standar, PowerBI PDF).</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">GET /finance</span> · <span class="feature-endpoint">GET|POST /finance/journals</span> · <span class="feature-endpoint">GET /finance/reports/pdf</span> · <span class="feature-endpoint">GET /finance/reports/powerbi-pdf</span>
        </div>
    </div>

    <!-- MODUL 7 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 7: HR MANAGEMENT &amp; PENGGAJIAN TERKONSOLIDASI (PAYROLL)</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>Master Karyawan &amp; Integrasi Akun Sistem:</strong> Pengelolaan data NIK, jabatan, cabang, rekening BCA, pembuatan/penautan user login dan reset password langsung dari HR.</li>
            <li><strong>Presensi &amp; Lembur:</strong> Pencatatan kehadiran harian, shift kerja, dan jam lembur per cabang.</li>
            <li><strong>Engine Penggajian Bulanan Batch:</strong> Generate draf gaji seluruh karyawan per cabang dengan rincian: Gaji Pokok, Tunjangan, Transport/Makan, Lembur, Bonus, Potongan Absensi, BPJS Kesehatan &amp; Ketenagakerjaan, serta Kasbon.</li>
            <li><strong>Siklus Status Payroll:</strong> <code>DRAFT</code> → <code>FINAL</code> (Terkunci dari manipulasi) → <code>PAID</code>.</li>
            <li><strong>Cetak Slip Gaji &amp; Auto Sync Jurnal:</strong> Desain slip gaji profesional siap cetak/unduh dan posting otomatis ke jurnal umum.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">GET /hr</span> · <span class="feature-endpoint">POST /hr/payrolls</span> · <span class="feature-endpoint">POST /hr/payrolls/{payroll}/finalize</span> · <span class="feature-endpoint">GET /hr/payslip/{item}</span>
        </div>
    </div>

    <!-- MODUL 8 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 8: FIXED ASSETS &amp; JADWAL DEPRESIASI OTOMATIS</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>Inventarisasi Aset Tetap:</strong> Mesin cuci komersial, dryer, boiler, setrika, kendaraan, instalasi toko dengan kode aset unik.</li>
            <li><strong>Kalkulasi Depresiasi Otomatis:</strong> Garis Lurus (Straight-Line) &amp; Saldo Menurun Ganda (Double-Declining Balance) dengan jadwal penyusutan bulanan (<code>DepreciationSchedule</code>).</li>
            <li><strong>Log Pemeliharaan Mesin:</strong> Catatan riwayat servis berkala, penggantian suku cadang, kalibrasi, biaya servis, dan teknisi vendor.</li>
            <li><strong>Portfolio Analytics:</strong> Visualisasi grafik nilai buku vs akumulasi penyusutan aset per kategori/cabang.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">GET /assets</span> · <span class="feature-endpoint">POST /assets</span> · <span class="feature-endpoint">POST /assets/{asset}/maintenance</span> · <span class="feature-endpoint">GET /assets/export/pdf</span>
        </div>
    </div>

    <!-- MODUL 9 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 9: EXECUTIVE DASHBOARDS &amp; PERFORMANCE KPI ANALYTICS</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>5 Tampilan Dashboard Khusus Sesuai Peran:</strong> Owner (multi-cabang KPI), Branch Admin (operasional outlet), Workshop Supervisor (antrean stasiun), Cashier (kas &amp; shift), Finance (likuiditas &amp; AP/AR).</li>
            <li><strong>Leaderboard &amp; Kinerja Staf:</strong> Pemeringkatan produktivitas kasir (kecepatan &amp; nominal) dan operator workshop (throughput cucian).</li>
            <li><strong>Ekspor Laporan Kinerja:</strong> Download rekap performa dalam format PDF dan Excel.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">GET /dashboard</span> · <span class="feature-endpoint">GET /performance</span> · <span class="feature-endpoint">GET /performance/export/pdf</span> · <span class="feature-endpoint">POST /switch-branch</span>
        </div>
    </div>

    <!-- MODUL 10 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 10: CUSTOMER PORTAL, ONBOARDING GUIDE &amp; INTERACTIVE WEB</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>Landing Page Interaktif (Expressive UI):</strong> Katalog paket cuci, kalkulator estimasi biaya interaktif, dan ulasan pelanggan.</li>
            <li><strong>Peta Interaktif 5 Outlet Samarinda:</strong> Integrasi Leaflet Map Canvas interaktif memetakan 5 outlet Samarinda.</li>
            <li><strong>Order Online dengan Koordinat GPS:</strong> Pemesanan online layanan jemput-antar (Pickup &amp; Delivery) dengan koordinat GPS peta.</li>
            <li><strong>Portal Training Staf (<code>/guide</code>):</strong> Dokumentasi SOP operasional interaktif untuk kasir, operator, supervisor, dan finance.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">GET /</span> · <span class="feature-endpoint">GET /track</span> · <span class="feature-endpoint">GET /guide</span> · <span class="feature-endpoint">POST /api/v1/orders/online</span>
        </div>
    </div>

    <!-- MODUL 11 -->
    <div class="feature-card">
        <div class="feature-card-header">MODUL 11: 100% FEATURE-COMPLETE RESTFUL API ENGINE (V1)</div>
        <p><strong>Deskripsi &amp; Fungsionalitas:</strong></p>
        <ul>
            <li><strong>Arsitektur API Enterprise:</strong> 16 Controller API khusus dengan lebih dari 80 endpoint terproteksi token Laravel Sanctum.</li>
            <li><strong>Dokumentasi Interaktif Swagger UI:</strong> Akses dokumentasi pengembang dan uji coba interaktif endpoint di <code>/api/documentation</code>.</li>
            <li><strong>Suite Endpoint Lengkap:</strong> Auth, Profile, POS Tablet API, Workshop Status API, Public GPS Order API, serta seluruh modul manajemen CRUD.</li>
        </ul>
        <div style="font-size: 7.5pt; color: #475569;">
            <strong>Endpoint Kunci:</strong> <span class="feature-endpoint">POST /api/login</span> · <span class="feature-endpoint">GET /api/v1/dashboard/stats</span> · <span class="feature-endpoint">GET /api/documentation</span>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- ==================== BAB 5: KEAMANAN & AUDIT ==================== -->
    <h1>5. Tata Kelola Keamanan, Audit &amp; Integritas Data</h1>

    <h2>5.1 Mekanisme Pencegahan Kebocoran Data (Data Isolation)</h2>
    <p>
        Integritas data transaksi antar unit cabang dijamin melalui beberapa lapis pengamanan:
    </p>
    <ul>
        <li><strong>Database Constraint &amp; Foreign Key:</strong> Semua relasi data mengikat secara ketat integritas referensial ke tabel <code>branches</code> dengan indeks performa tinggi.</li>
        <li><strong>Automatic Branch Ingestion:</strong> Setiap entitas baru yang dibuat oleh kasir atau admin cabang secara otomatis mewarisi <code>branch_id</code> dari session aktif pengguna tanpa bergantung pada input form sisi klien.</li>
        <li><strong>Super User Audit Switcher:</strong> Hanya pengguna dengan izin eksplisit yang dapat mengeksekusi rute <code>POST /switch-branch</code>. Setiap pergantian cabang dicatat dalam audit trail.</li>
    </ul>

    <h2>5.2 Kebijakan Integritas Transaksi Keuangan</h2>
    <ul>
        <li><strong>Anti-Imbalance Protection:</strong> Sistem menolak eksekusi jurnal yang nilai total debit dan kreditnya tidak seimbang dengan melempar <code>JournalNotBalancedException</code>.</li>
        <li><strong>Strict Period Locking:</strong> Transaksi pada periode akuntansi yang telah berstatus <code>closed</code> tidak dapat diubah atau ditambah, mencegah distorsi laporan keuangan auditable.</li>
        <li><strong>Idempotent Event Posting:</strong> Observer keuangan menggunakan database transaction dan lock pengecekan entitas untuk memastikan tidak terjadi duplikasi jurnal ganda pada satu nomor order.</li>
    </ul>

    <h2>5.3 Backup &amp; Recovery Terjadwal</h2>
    <p>
        Sistem dilengkapi paket <code>spatie/laravel-backup</code> yang dikonfigurasi untuk mencadangkan database MySQL dan berkas storage secara otomatis setiap hari, menjamin kesiapan pemulihan bencana (<em>Disaster Recovery</em>).
    </p>

    <!-- ==================== BAB 6: KONTAK & PENUTUP ==================== -->
    <h1>6. Informasi Kontak &amp; Dukungan Sistem</h1>

    <table class="data-table">
        <tbody>
            <tr>
                <td style="width: 30%;"><strong>Nama Perusahaan:</strong></td>
                <td>Istana Premium Laundry Service Samarinda</td>
            </tr>
            <tr>
                <td><strong>Alamat Kantor Pusat:</strong></td>
                <td>Jl. KH. Wahid Hasyim 2 No.57, Samarinda, Kalimantan Timur 75119</td>
            </tr>
            <tr>
                <td><strong>Official Customer Care WhatsApp:</strong></td>
                <td><strong>+62 811-5599-199</strong></td>
            </tr>
            <tr>
                <td><strong>Live Production URL:</strong></td>
                <td><a href="https://istanasystem.alk-tech.my.id" target="_blank">https://istanasystem.alk-tech.my.id</a></td>
            </tr>
            <tr>
                <td><strong>Dokumentasi API Swagger:</strong></td>
                <td><a href="https://istanasystem.alk-tech.my.id/api/documentation" target="_blank">https://istanasystem.alk-tech.my.id/api/documentation</a></td>
            </tr>
            <tr>
                <td><strong>Repository Kode Sumber:</strong></td>
                <td><a href="https://github.com/fk0u/IstanaLaundryManagementSystem" target="_blank">https://github.com/fk0u/IstanaLaundryManagementSystem</a></td>
            </tr>
        </tbody>
    </table>

    <div class="footer-note">
        Dokumen ini dibuat secara otomatis oleh sistem sebagai representasi resmi status fitur, arsitektur, kredensial akun pengguna, dan hak akses RBAC Istana Laundry Management System.<br>
        &copy; 2026 Istana Laundry Management System · Hak Cipta Dilindungi Undang-Undang.
    </div>

</body>
</html>
HTML;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('a4', 'portrait');
$dompdf->render();

$outputPdf = $dompdf->output();

$outputPath1 = __DIR__ . '/../docs/ISTANA_LAUNDRY_FULL_RBAC_ACCOUNTS_AND_FEATURES.pdf';
$outputPath2 = __DIR__ . '/../docs/ISTANA_LAUNDRY_FEATURE_AND_RBAC_SPECIFICATION.pdf';

@file_put_contents($outputPath1, $outputPdf);
@file_put_contents($outputPath2, $outputPdf);

echo "PDF 1 Berhasil dibuat di: " . realpath($outputPath1) . " (" . filesize($outputPath1) . " bytes)\n";
if (file_exists($outputPath2)) {
    echo "PDF 2 Berhasil diperbarui di: " . realpath($outputPath2) . " (" . filesize($outputPath2) . " bytes)\n";
}
