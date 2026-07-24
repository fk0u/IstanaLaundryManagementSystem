# Requirements Document — Istana Laundry Management System

## Introduction

Istana Laundry Management System adalah platform Semi-ERP berbasis web yang dirancang untuk mengelola operasional bisnis laundry multi-cabang secara terintegrasi. Sistem ini dibangun dengan Laravel 13 + PHP 8.5+, Blade + Alpine.js 3, Tailwind CSS v4, dan MySQL/SQLite. Platform ini mencakup 14 modul utama: Authentication & RBAC, Workshop & Branch Management, Master Data, POS & Billing, Production Tracking, CRM & Loyalty, Promotions Engine, Inventory & Procurement, Finance & Accounting, HR Management, Fixed Asset, Dashboard & Analytics, Reporting & Export, dan Public Tracking.

Tujuan utama sistem adalah meningkatkan efisiensi operasional laundry, memberikan transparansi kepada pelanggan melalui pelacakan real-time, serta menyediakan laporan keuangan akurat yang mendukung keputusan bisnis.

---

## Glossary

- **System**: Istana Laundry Management System secara keseluruhan
- **Auth_Module**: Modul autentikasi dan manajemen hak akses berbasis peran
- **Branch**: Cabang laundry fisik yang terdaftar dalam sistem
- **Workshop**: Unit produksi/pengerjaan cucian dalam sebuah cabang
- **User**: Pengguna terdaftar dalam sistem dengan peran tertentu
- **Owner**: Pemilik bisnis dengan akses penuh ke seluruh sistem
- **Super_Admin**: Administrator sistem dengan hampir semua akses operasional
- **Branch_Admin**: Administrator yang mengelola satu atau lebih cabang tertentu
- **Workshop_Admin**: Administrator yang mengelola produksi dan kualitas di workshop
- **Cashier**: Kasir yang menangani transaksi harian di POS
- **Workshop_Staff**: Staf yang mengeksekusi proses cucian
- **CS_Marketing**: Staf customer service dan pemasaran
- **Finance**: Staf keuangan dan akuntansi
- **Developer**: Peran teknis dengan akses penuh termasuk konfigurasi sistem
- **POS**: Point of Sale, modul transaksi penjualan layanan laundry
- **Order**: Satu entri transaksi laundry dari seorang pelanggan
- **Order_Number**: Nomor unik per order yang di-generate otomatis per cabang
- **Customer**: Pelanggan terdaftar maupun walk-in
- **Walk_In**: Pelanggan tanpa data terdaftar sebelumnya
- **Production_Status**: Salah satu dari 8 status tetap: TERIMA, PILAH, CUCI, KERING, LIPAT, CEK, SIAP, DIAMBIL
- **QR_Code**: Kode QR unik yang dicetak pada tag order untuk tracking produksi
- **Loyalty_Point**: Poin yang diperoleh pelanggan berdasarkan nilai transaksi
- **Loyalty_Tier**: Tingkatan loyalitas: Bronze, Silver, Gold, Platinum
- **COA**: Chart of Accounts, daftar akun keuangan standar dan kustom
- **Double_Entry**: Prinsip akuntansi pencatatan debit dan kredit secara bersamaan
- **Journal**: Entri pencatatan transaksi dalam buku besar akuntansi
- **Accounting_Period**: Periode akuntansi bulanan yang dapat ditutup
- **FIFO**: First In First Out, metode pengeluaran stok berdasarkan urutan masuk
- **Purchase_Request**: Permintaan pembelian barang ke manajemen
- **Purchase_Order**: Dokumen pemesanan resmi ke supplier
- **GRN**: Goods Received Note, dokumen penerimaan barang
- **Fixed_Asset**: Aset tetap perusahaan seperti mesin cuci, kendaraan, furniture
- **Depreciation**: Penyusutan nilai aset tetap secara periodik
- **PP23**: Pajak Penghasilan Pasal 23
- **PPN**: Pajak Pertambahan Nilai
- **Rate_Limiter**: Mekanisme pembatasan jumlah request dalam rentang waktu tertentu
- **Audit_Trail**: Catatan log semua aktivitas pengguna untuk keperluan audit
- **Branch_Scope**: Pembatasan data berdasarkan cabang pengguna yang login
- **Thermal_Receipt**: Struk cetak format thermal 58mm atau 80mm
- **Invoice_PDF**: Faktur resmi dalam format PDF
- **Refund**: Pengembalian dana kepada pelanggan
- **Promo**: Promosi berupa diskon atau bonus layanan
- **Validator**: Komponen yang memvalidasi input dari pengguna
- **Repository**: Layer abstraksi akses data menggunakan Repository Pattern
- **Service**: Layer bisnis logika menggunakan Service Layer Pattern
- **Observer**: Mekanisme event listener untuk side-effect otomatis (auto journal, auto poin)
- **Seeder**: Script untuk mengisi data awal sistem
- **Pretty_Printer**: Komponen yang memformat objek kembali ke representasi string/dokumen yang valid

---

## Requirements

### Requirement 1: Authentication & Role-Based Access Control (RBAC)

**User Story:** Sebagai User, saya ingin dapat login dengan aman dan mendapatkan akses hanya ke fitur yang sesuai peran saya, sehingga keamanan dan integritas data sistem terjaga.

#### Acceptance Criteria

1. THE Auth_Module SHALL menyediakan 8 peran berbeda: Developer, Owner, Super_Admin, Branch_Admin, Workshop_Admin, Cashier, Workshop_Staff, dan CS_Marketing serta Finance.
2. WHEN seorang User mengirimkan kredensial yang valid, THE Auth_Module SHALL mengautentikasi User dan membuat sesi login dalam waktu kurang dari 800ms.
3. IF seorang User mengirimkan kredensial yang tidak valid sebanyak 5 kali berturut-turut, THEN THE Auth_Module SHALL mengunci akun selama 15 menit dan mencatat kejadian tersebut ke Audit_Trail.
4. WHEN seorang User berhasil login, THE Auth_Module SHALL mengarahkan User ke dashboard yang sesuai dengan perannya.
5. THE Auth_Module SHALL menerapkan Rate_Limiter maksimum 10 percobaan login per menit dari satu alamat IP.
6. WHEN seorang User logout, THE Auth_Module SHALL menghapus sesi dan mengarahkan ke halaman login.
7. THE Auth_Module SHALL menyediakan fitur "Remember Me" yang memperpanjang sesi hingga 30 hari menggunakan cookie terenkripsi.
8. WHERE fitur Audit_Trail diaktifkan, THE Auth_Module SHALL mencatat setiap login, logout, dan percobaan login gagal beserta timestamp dan alamat IP.
9. THE Auth_Module SHALL menggunakan Laravel Breeze sebagai foundation autentikasi dengan Spatie Permission v8 untuk manajemen peran dan izin.
10. WHEN seorang User dengan peran Branch_Admin mengakses sistem, THE Auth_Module SHALL menerapkan Branch_Scope sehingga User hanya dapat melihat dan memodifikasi data cabangnya sendiri.
11. IF token sesi kadaluarsa, THEN THE Auth_Module SHALL mengarahkan User ke halaman login dengan pesan informatif.
12. THE Auth_Module SHALL mendukung perubahan password oleh User sendiri dengan validasi kekuatan password minimum 8 karakter mengandung huruf dan angka.

---

### Requirement 2: Workshop & Branch Management

**User Story:** Sebagai Owner atau Super_Admin, saya ingin mengelola data workshop dan cabang secara terpusat, sehingga setiap cabang dapat beroperasi secara mandiri namun tetap terintegrasi.

#### Acceptance Criteria

1. THE System SHALL mendukung pendaftaran minimal 10 cabang (Branch) dalam satu instalasi sistem.
2. WHEN seorang Super_Admin membuat Branch baru, THE System SHALL menghasilkan kode cabang unik dan mengaktifkan Branch_Scope untuk seluruh data terkait cabang tersebut.
3. THE System SHALL mengasosiasikan setiap Workshop dengan tepat satu Branch.
4. WHEN data Branch diperbarui, THE System SHALL memperbarui semua referensi terkait tanpa menghapus data historis.
5. IF Branch dinonaktifkan, THEN THE System SHALL mencegah pembuatan Order baru pada Branch tersebut namun tetap memungkinkan akses ke data historis.
6. THE System SHALL menyediakan middleware Branch_Scope yang secara otomatis memfilter semua query database berdasarkan Branch User yang sedang login.
7. WHILE seorang Branch_Admin sedang login, THE System SHALL hanya menampilkan data yang berasal dari Branch milik Branch_Admin tersebut.
8. THE System SHALL menyimpan informasi Branch mencakup: nama, alamat, nomor telepon, kode cabang, status aktif, dan koordinat GPS (opsional).
9. WHERE fitur multi-Workshop dalam satu Branch diperlukan, THE System SHALL memungkinkan satu Branch memiliki lebih dari satu Workshop.
10. THE System SHALL menyediakan Seeder untuk data Branch dan Workshop awal saat instalasi sistem.

---

### Requirement 3: Master Data Management

**User Story:** Sebagai Super_Admin atau Branch_Admin, saya ingin mengelola data master layanan, harga, dan akun keuangan, sehingga operasional dapat berjalan dengan data yang konsisten dan akurat.

#### Acceptance Criteria

1. THE System SHALL menyediakan manajemen layanan laundry mencakup: nama layanan, jenis (kilogram/satuan/kategori), harga dasar, estimasi durasi, dan status aktif.
2. WHEN harga layanan diperbarui, THE System SHALL menyimpan riwayat harga lama dengan timestamp sehingga Order historis tidak terpengaruh perubahan harga.
3. THE System SHALL mendukung struktur COA standar dengan kategori: Aset, Liabilitas, Ekuitas, Pendapatan, dan Beban.
4. WHEN akun COA kustom dibuat, THE System SHALL memvalidasi bahwa kode akun bersifat unik dan mengikuti struktur hierarki yang telah ditentukan.
5. IF akun COA yang sudah memiliki Journal dihapus, THEN THE System SHALL menolak penghapusan dan menampilkan pesan error yang informatif.
6. THE System SHALL menyediakan Seeder untuk COA standar laundry, layanan dasar, dan data referensi lainnya.
7. WHERE layanan memiliki variasi harga per cabang, THE System SHALL mendukung penetapan harga berbeda untuk setiap Branch dengan tetap memiliki harga dasar global.
8. THE System SHALL memvalidasi semua input master data menggunakan Form Requests dengan pesan error dalam Bahasa Indonesia.
9. THE System SHALL menyimpan log perubahan (Audit_Trail) untuk seluruh master data yang dimodifikasi.

---

### Requirement 4: POS & Billing

**User Story:** Sebagai Cashier, saya ingin memproses transaksi laundry dengan cepat dan akurat termasuk kalkulasi promo dan cetak struk, sehingga pelanggan terlayani dengan baik dan catatan keuangan terjaga.

#### Acceptance Criteria

1. WHEN Cashier membuat Order baru, THE POS SHALL menghasilkan Order_Number unik dengan format `[KODE_CABANG]-[TAHUN][BULAN]-[SEQUENCE]` secara otomatis.
2. THE POS SHALL menyediakan pencarian Customer berdasarkan nama, nomor telepon, atau nomor member dengan hasil muncul dalam waktu kurang dari 300ms.
3. WHEN Customer tidak ditemukan, THE POS SHALL memungkinkan Cashier membuat Order sebagai Walk_In atau mendaftarkan Customer baru dalam satu alur.
4. THE POS SHALL menghitung total tagihan secara real-time ketika item layanan ditambahkan, diubah, atau dihapus.
5. WHEN Promo berlaku untuk sebuah Order, THE POS SHALL menghitung dan menampilkan diskon secara otomatis berdasarkan aturan Promo yang aktif.
6. THE POS SHALL mendukung 3 metode pembayaran: Cash, Transfer Manual, dan Invoice (untuk pelanggan korporat).
7. WHEN pembayaran Cash dilakukan, THE POS SHALL menghitung kembalian secara otomatis berdasarkan nominal yang diterima.
8. WHEN Order berhasil dibayar, THE POS SHALL mencetak Thermal_Receipt dan menawarkan pilihan cetak Invoice_PDF.
9. THE POS SHALL menambahkan Loyalty_Point ke akun Customer secara otomatis setelah pembayaran berhasil melalui Observer.
10. THE POS SHALL membuat Journal entry secara otomatis melalui Observer ketika pembayaran Order selesai.
11. WHEN Refund diajukan, THE POS SHALL memulai alur approval 4 tahap: Cashier → Branch_Admin → Finance → Owner sebelum refund diproses.
12. IF stok item terkait Order tidak tersedia dalam inventori, THEN THE POS SHALL menampilkan peringatan namun tetap mengizinkan Order dilanjutkan.
13. THE POS SHALL mendukung penambahan catatan khusus per Order (misalnya: noda membandel, pakaian sensitif) yang akan tercetak pada struk.
14. WHILE dalam mode POS aktif, THE POS SHALL memungkinkan Cashier memproses Order berikutnya tanpa harus kembali ke menu utama.
15. THE POS SHALL menyimpan draft Order secara otomatis setiap 30 detik untuk mencegah kehilangan data.

---

### Requirement 5: Production Tracking

**User Story:** Sebagai Workshop_Admin dan Workshop_Staff, saya ingin melacak dan memperbarui status pengerjaan cucian secara real-time dengan scan QR, sehingga setiap Order dapat dipantau progresnya secara akurat.

#### Acceptance Criteria

1. THE System SHALL mendefinisikan tepat 8 Production_Status secara berurutan: TERIMA → PILAH → CUCI → KERING → LIPAT → CEK → SIAP → DIAMBIL.
2. WHEN Workshop_Staff memperbarui Production_Status sebuah Order, THE System SHALL hanya mengizinkan perpindahan ke status berikutnya (forward-only) dan menolak perpindahan ke status sebelumnya.
3. THE System SHALL mencetak QR_Code unik untuk setiap Order yang dapat digunakan untuk update status melalui scan.
4. WHEN QR_Code di-scan menggunakan perangkat mobile, THE System SHALL menampilkan informasi Order terkini dan memungkinkan update status massal dalam satu operasi.
5. IF Order telah mencapai status DIAMBIL, THEN THE System SHALL mencegah perubahan status lebih lanjut dan menandai Order sebagai selesai.
6. THE System SHALL mencatat timestamp setiap perubahan Production_Status beserta identitas User yang melakukan perubahan.
7. WHEN Production_Status berubah ke SIAP, THE System SHALL menghasilkan notifikasi yang dapat dikirimkan ke Customer melalui WhatsApp (manual trigger oleh CS_Marketing).
8. THE System SHALL menampilkan tampilan kanban atau list semua Order beserta Production_Status saat ini yang dapat difilter per Branch.
9. WHERE status update massal diperlukan, THE System SHALL memungkinkan Workshop_Admin memilih beberapa Order sekaligus dan mengubah status secara batch.
10. THE System SHALL melacak estimasi selesai berdasarkan jenis layanan dan menampilkan peringatan jika Order melebihi estimasi waktu.
11. THE System SHALL menyediakan Public Tracking page yang dapat diakses Customer tanpa login menggunakan Order_Number untuk melihat Production_Status terkini.

---

### Requirement 6: CRM & Loyalty Program

**User Story:** Sebagai CS_Marketing, saya ingin mengelola data pelanggan, riwayat transaksi, dan program loyalitas, sehingga hubungan pelanggan terjaga dan pelanggan termotivasi untuk terus menggunakan layanan.

#### Acceptance Criteria

1. THE System SHALL menyimpan profil Customer mencakup: nama lengkap, nomor telepon, email (opsional), alamat, tanggal bergabung, Loyalty_Tier, total poin, dan riwayat transaksi.
2. THE System SHALL mendefinisikan 4 Loyalty_Tier dengan threshold poin: Bronze (0-999 poin), Silver (1000-4999 poin), Gold (5000-9999 poin), Platinum (10000+ poin).
3. WHEN total Loyalty_Point Customer melampaui threshold Loyalty_Tier berikutnya, THE System SHALL secara otomatis meningkatkan Loyalty_Tier Customer melalui Observer.
4. THE System SHALL mengakumulasikan Loyalty_Point berdasarkan nilai transaksi dengan rasio yang dapat dikonfigurasi (default: 1 poin per Rp 1.000).
5. WHEN Loyalty_Point digunakan sebagai pembayaran sebagian, THE System SHALL menghitung konversi poin ke nilai rupiah dan mengurangi total tagihan secara otomatis.
6. THE System SHALL menerapkan expiry Loyalty_Point otomatis setelah 365 hari tidak ada transaksi dari Customer terkait.
7. WHEN Loyalty_Point kadaluarsa, THE System SHALL mencatat log expiry dan mengirimkan notifikasi ke CS_Marketing untuk follow-up.
8. THE System SHALL menampilkan riwayat lengkap transaksi, perolehan poin, penggunaan poin, dan perubahan tier untuk setiap Customer.
9. THE System SHALL memungkinkan CS_Marketing untuk melakukan segmentasi Customer berdasarkan Loyalty_Tier, frekuensi transaksi, dan nilai transaksi rata-rata.
10. WHERE Customer memiliki nomor telepon terdaftar, THE System SHALL menyediakan fitur kirim notifikasi WhatsApp manual dari antarmuka CRM.
11. IF Customer melakukan pengembalian dana (Refund), THEN THE System SHALL mengurangi Loyalty_Point yang telah diperoleh dari transaksi yang di-refund secara proporsional.

---

### Requirement 7: Promotions Engine

**User Story:** Sebagai Owner atau Branch_Admin, saya ingin membuat dan mengelola program promosi, sehingga dapat meningkatkan volume transaksi dan loyalitas pelanggan.

#### Acceptance Criteria

1. THE System SHALL mendukung pembuatan Promo dengan tipe: diskon persen, diskon nominal, beli X gratis Y, dan diskon berbasis Loyalty_Tier.
2. WHEN Promo dibuat, THE System SHALL memvalidasi bahwa periode berlaku Promo tidak tumpang tindih dengan Promo lain pada layanan yang sama.
3. THE System SHALL mengevaluasi semua Promo aktif secara otomatis ketika Order dibuat di POS dan menerapkan Promo yang paling menguntungkan Customer (jika tidak ada aturan stack).
4. WHERE Promo memiliki syarat minimum transaksi, THE System SHALL hanya menerapkan Promo tersebut jika nilai Order memenuhi syarat minimum.
5. WHEN Promo berakhir (melewati tanggal akhir), THE System SHALL secara otomatis menonaktifkan Promo tanpa intervensi manual.
6. THE System SHALL mendukung Promo yang dibatasi jumlah penggunaan total maupun per Customer.
7. IF kuota penggunaan Promo habis, THEN THE System SHALL menonaktifkan Promo dan menampilkan notifikasi kepada Cashier saat Promo dicoba diterapkan.
8. THE System SHALL menyediakan laporan efektivitas Promo mencakup: jumlah penggunaan, total diskon diberikan, dan dampak terhadap revenue.

---

### Requirement 8: Inventory & Procurement

**User Story:** Sebagai Branch_Admin, saya ingin mengelola stok bahan habis pakai dan peralatan laundry dengan metode FIFO, serta mengatur alur pembelian dari request hingga penerimaan barang, sehingga operasional tidak terganggu oleh kehabisan stok.

#### Acceptance Criteria

1. THE System SHALL melacak stok barang menggunakan metode FIFO dengan batch tracking untuk setiap item inventori.
2. WHEN stok item jatuh di bawah batas minimum yang ditentukan, THE System SHALL mengirimkan low stock alert kepada Branch_Admin yang bersangkutan.
3. THE System SHALL mendukung alur pengadaan: Purchase_Request → Purchase_Order → GRN (Goods Received Note) dengan status tracking di setiap tahap.
4. WHEN Purchase_Request dibuat oleh staf, THE System SHALL mengirimkan notifikasi ke Branch_Admin atau Super_Admin untuk persetujuan.
5. WHEN Purchase_Order disetujui, THE System SHALL menghasilkan dokumen Purchase_Order yang dapat dicetak atau diekspor ke PDF.
6. WHEN GRN dibuat setelah barang diterima, THE System SHALL memperbarui stok secara otomatis dengan batch number dan tanggal masuk sebagai referensi FIFO.
7. THE System SHALL mencatat harga satuan pada setiap batch penerimaan untuk perhitungan COGS yang akurat.
8. IF barang yang diterima tidak sesuai Purchase_Order (kualitas/kuantitas), THEN THE System SHALL menyediakan mekanisme retur ke supplier dengan dokumen pendukung.
9. THE System SHALL membuat Journal entry secara otomatis melalui Observer ketika GRN dikonfirmasi, mencatat debit inventori dan kredit hutang usaha.
10. THE System SHALL menyediakan laporan stok real-time, laporan pergerakan stok, dan laporan nilai inventori per Branch.

---

### Requirement 9: Finance & Accounting

**User Story:** Sebagai Finance, saya ingin sistem mencatat semua transaksi keuangan secara Double_Entry secara otomatis dan menyediakan laporan keuangan yang akurat, sehingga laporan keuangan dapat dipercaya untuk pengambilan keputusan bisnis.

#### Acceptance Criteria

1. THE System SHALL menerapkan prinsip Double_Entry secara otomatis untuk setiap transaksi keuangan melalui Observer, memastikan total debit selalu sama dengan total kredit.
2. THE System SHALL menyediakan COA standar bisnis laundry dengan minimal 50 akun bawaan dan mendukung penambahan akun kustom.
3. WHEN Accounting_Period ditutup (periode closing), THE System SHALL mencegah pembuatan atau modifikasi Journal pada periode tersebut dan menghasilkan laporan keuangan akhir periode.
4. THE System SHALL mendukung perhitungan dan pelaporan PP23 serta PPN sesuai peraturan perpajakan Indonesia yang berlaku.
5. THE System SHALL menyediakan fitur Manual_Journal untuk penyesuaian akuntansi yang memerlukan input langsung dari Finance.
6. WHEN Manual_Journal dibuat, THE System SHALL memvalidasi bahwa entri seimbang (total debit = total kredit) sebelum menyimpan.
7. IF Manual_Journal mengandung entri yang tidak seimbang, THEN THE System SHALL menolak penyimpanan dan menampilkan selisih nilai yang perlu disesuaikan.
8. THE System SHALL menghasilkan laporan keuangan standar: Neraca (Balance Sheet), Laba Rugi (Income Statement), dan Arus Kas (Cash Flow Statement) per periode.
9. THE System SHALL memisahkan laporan keuangan per Branch serta menyediakan laporan konsolidasi untuk Owner.
10. WHERE transaksi melibatkan pajak, THE System SHALL menghitung nilai pajak secara otomatis berdasarkan konfigurasi tarif pajak yang ditetapkan.
11. THE System SHALL menyimpan semua Journal dengan status: Draft, Posted, dan Reversed untuk keperluan audit dan kontrol.

---

### Requirement 10: HR Management

**User Story:** Sebagai Super_Admin atau Branch_Admin, saya ingin mengelola data karyawan, absensi, dan penggajian dasar, sehingga administrasi sumber daya manusia berjalan terorganisir.

#### Acceptance Criteria

1. THE System SHALL menyimpan data karyawan mencakup: nama, NIK, jabatan, cabang, tanggal bergabung, gaji pokok, dan status aktif.
2. THE System SHALL menyediakan fitur pencatatan absensi harian dengan metode: manual input oleh Branch_Admin.
3. WHEN data absensi bulan berjalan telah lengkap, THE System SHALL menghitung gaji karyawan berdasarkan gaji pokok, kehadiran, dan komponen tambahan yang dikonfigurasi.
4. THE System SHALL mendukung komponen gaji: gaji pokok, tunjangan tetap, tunjangan tidak tetap, dan potongan.
5. WHEN payroll diproses, THE System SHALL menghasilkan slip gaji per karyawan yang dapat dicetak atau diekspor ke PDF.
6. IF karyawan dinonaktifkan, THEN THE System SHALL mencegah karyawan tersebut login ke sistem dan mempertahankan data historis absensi dan penggajian.
7. THE System SHALL mencatat riwayat jabatan dan perubahan gaji karyawan beserta tanggal efektif perubahan.

---

### Requirement 11: Fixed Asset Management

**User Story:** Sebagai Finance atau Super_Admin, saya ingin mendaftarkan aset tetap, menghitung depresiasi, dan menjadwalkan maintenance, sehingga nilai aset terpantau dan perawatan terjadwal.

#### Acceptance Criteria

1. THE System SHALL menyediakan register Fixed_Asset mencakup: nama aset, kode aset, kategori, lokasi (Branch), tanggal perolehan, nilai perolehan, nilai sisa, umur ekonomis, dan metode depresiasi.
2. THE System SHALL mendukung 2 metode Depreciation: Straight Line (Garis Lurus) dan Double Declining Balance.
3. WHEN Accounting_Period ditutup, THE System SHALL menghitung dan membuat Journal Depreciation secara otomatis untuk semua Fixed_Asset aktif.
4. THE System SHALL menghasilkan jadwal depresiasi (depreciation schedule) per aset dalam format tabel yang dapat diekspor ke Excel.
5. THE System SHALL menyediakan fitur pencatatan jadwal maintenance preventif per Fixed_Asset dengan tanggal jatuh tempo.
6. WHEN tanggal maintenance Fixed_Asset sudah dalam 7 hari ke depan, THE System SHALL menampilkan pengingat kepada Branch_Admin terkait.
7. IF Fixed_Asset dijual atau dihapus (disposal), THEN THE System SHALL menghitung keuntungan/kerugian disposal dan membuat Journal disposal secara otomatis.

---

### Requirement 12: Dashboard & Analytics

**User Story:** Sebagai User dengan berbagai peran, saya ingin melihat dashboard yang disesuaikan dengan tanggung jawab saya, sehingga saya dapat membuat keputusan berdasarkan data yang relevan dengan cepat.

#### Acceptance Criteria

1. THE System SHALL menyediakan dashboard berbeda untuk setiap peran: Owner (Executive Summary), Super_Admin (Operasional), Branch_Admin (Cabang), Workshop_Admin (Produksi), Cashier (POS), Finance (Keuangan), CS_Marketing (CRM).
2. WHEN Owner login, THE System SHALL menampilkan Executive Summary mencakup: total revenue semua cabang, perbandingan periode, jumlah order aktif, dan top performing Branch menggunakan Chart.js.
3. WHEN Branch_Admin login, THE System SHALL menampilkan data cabang terkait saja: revenue hari ini, jumlah order, stok kritis, dan ringkasan produksi.
4. THE System SHALL memperbarui data dashboard secara otomatis dengan interval yang dapat dikonfigurasi (default setiap 5 menit).
5. THE System SHALL menampilkan grafik tren transaksi, distribusi layanan, dan performa cabang menggunakan Chart.js.
6. WHERE dark mode diaktifkan oleh pengguna, THE System SHALL menampilkan semua komponen dashboard dengan skema warna gelap yang konsisten.
7. THE System SHALL memastikan dashboard dapat diakses dan terbaca dengan baik pada perangkat mobile (responsive design).

---

### Requirement 13: Reporting & Export

**User Story:** Sebagai Owner, Finance, atau Branch_Admin, saya ingin mengekspor laporan operasional dan keuangan dalam format Excel dan PDF, sehingga laporan dapat dibagikan ke pihak terkait atau diarsipkan.

#### Acceptance Criteria

1. THE System SHALL mendukung ekspor laporan ke format Excel (.xlsx) menggunakan Laravel Excel dan ke format PDF menggunakan DomPDF.
2. THE System SHALL menyediakan minimal laporan berikut: Laporan Penjualan Harian/Bulanan, Laporan Produksi, Laporan Keuangan (Neraca, Laba Rugi), Laporan Inventori, Laporan Pelanggan & Loyalitas, dan Laporan Penggajian.
3. WHEN laporan dihasilkan, THE System SHALL menerapkan Branch_Scope sesuai peran User yang meminta laporan.
4. THE System SHALL memungkinkan Owner dan Finance untuk menghasilkan laporan konsolidasi lintas semua Branch.
5. WHEN laporan berukuran besar (lebih dari 10.000 baris) di-generate, THE System SHALL memproses ekspor secara asinkron (background job) dan mengirimkan notifikasi kepada User ketika laporan siap diunduh.
6. THE System SHALL menyediakan filter laporan berdasarkan: rentang tanggal, cabang, kategori layanan, dan dimensi lain yang relevan per jenis laporan.
7. THE System SHALL memformat Invoice_PDF dengan kop surat cabang, logo, dan nomor faktur yang terstruktur menggunakan DomPDF.

---

### Requirement 14: Public Order Tracking

**User Story:** Sebagai Customer, saya ingin dapat melacak status cucian saya secara real-time tanpa perlu login ke sistem, sehingga saya tahu kapan cucian siap diambil.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman publik yang dapat diakses tanpa autentikasi untuk pelacakan Order menggunakan Order_Number.
2. WHEN Customer memasukkan Order_Number yang valid di halaman publik, THE System SHALL menampilkan Production_Status terkini, nama layanan, estimasi selesai, dan lokasi Branch.
3. IF Order_Number tidak ditemukan atau tidak valid, THEN THE System SHALL menampilkan pesan error yang informatif dan menyarankan Customer untuk menghubungi Branch terkait.
4. THE System SHALL menampilkan timeline visual Production_Status dari TERIMA hingga DIAMBIL dengan indikator status saat ini.
5. THE System SHALL membatasi akses halaman publik menggunakan Rate_Limiter maksimum 30 request per menit per alamat IP untuk mencegah scraping data.
6. THE System SHALL tidak menampilkan informasi sensitif seperti data keuangan atau informasi pribadi Customer lain pada halaman publik.

---

### Requirement 15: Non-Functional Requirements

**User Story:** Sebagai seluruh pengguna sistem, saya ingin sistem bekerja dengan andal, cepat, aman, dan mudah digunakan di berbagai perangkat, sehingga produktivitas kerja meningkat dan data bisnis terlindungi.

#### Acceptance Criteria

1. THE System SHALL merespons setiap request HTTP dengan waktu rata-rata kurang dari 800ms di lingkungan production dengan beban normal.
2. THE System SHALL mendukung dark mode yang dapat diaktifkan dan dinonaktifkan oleh User dengan preferensi tersimpan per akun.
3. THE System SHALL menggunakan primary color #FF6600 (oranye) sebagai warna brand utama di seluruh antarmuka.
4. THE System SHALL menampilkan antarmuka yang responsif dan dapat digunakan dengan baik pada resolusi layar mulai dari 375px (mobile) hingga 1920px (desktop).
5. THE System SHALL mengimplementasikan HTTPS untuk semua komunikasi dan menyimpan password menggunakan algoritma bcrypt dengan salt faktor minimum 10.
6. THE System SHALL menerapkan Rate_Limiter pada semua endpoint API publik dan endpoint sensitif.
7. THE System SHALL menjalankan backup otomatis database menggunakan Spatie Laravel Backup setiap hari dan menyimpan minimal 7 backup terakhir.
8. THE System SHALL mencatat semua aksi modifikasi data ke Audit_Trail mencakup: User yang melakukan aksi, waktu, tabel yang dimodifikasi, nilai sebelum, dan nilai sesudah.
9. THE System SHALL menggunakan Repository Pattern dan Service Layer untuk memisahkan logika bisnis dari lapisan presentasi dan data.
10. THE System SHALL mengimplementasikan Branch_Scope Middleware yang diaktifkan secara otomatis untuk semua route yang memerlukan isolasi data per cabang.
11. THE System SHALL menggunakan Laravel Form Requests untuk validasi input di semua endpoint yang menerima data dari pengguna.
12. THE System SHALL didesain untuk dapat menjalankan lebih dari 10 cabang secara bersamaan tanpa degradasi performa yang signifikan.
