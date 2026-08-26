# 📖 Panduan Lengkap CMS Website Madeena

---

## 📋 Daftar Isi

1. Ringkasan Cepat (Cheat Sheet)
2. Masuk ke CMS (Login)
3. Mengenal Beranda (Dashboard)
4. Mengelola Halaman Utama Website (Homepage & Multibahasa)
5. Mengelola Bahasa Website (Manajemen Bahasa)
6. Mengelola Produk Inovasi
7. Menulis & Mengelola Artikel (Fitur Akademik)
8. Mengelola Halaman Website (Halaman & Alur Publikasi)
9. Mengelola Acara (Event) & Buku Tamu Digital
10. Manajemen Pengguna (Hak Akses Admin & Penulis)
11. Pengaturan Website
12. Tanya Jawab & Pemecahan Masalah (FAQ)

---

## 1. Ringkasan Cepat (Cheat Sheet)

| Saya ingin... | Langkah singkat |
|---|---|
| **Mengedit halaman depan (Bahasa Indonesia)** | Klik 🏠 **Halaman Utama** > Pilih Bahasa **Bahasa Indonesia** > Edit blok > **💾 Simpan Draft** > **🚀 Update Prod** |
| **Menyiapkan halaman depan bahasa lain** | Klik 🏠 **Halaman Utama** > Klik **📋 Duplikat ke Bahasa Lain** > Pilih bahasa target > Terjemahkan isi blok > **💾 Simpan Draft** > **🚀 Update Prod** |
| **Menambah bahasa baru di website** | Klik 🌐 **Bahasa** > Klik **Buat Baru** > Isi kode & nama bahasa > Atur label antarmuka > **Simpan** |
| **Menambah produk baru** | Klik 📦 **Produk** > Klik **Buat Baru** > Isi spesifikasi & detail blok > **Simpan** |
| **Menulis artikel/penelitian akademik** | Klik 📝 **Artikel** > Klik **Buat Baru** > Tulis di *Konten Artikel* (rumus, sitasi, gambar) > Centang *Publikasikan* > **Simpan** |
| **Membuat halaman profil perusahaan** | Klik 📄 **Halaman** > Klik **Buat Baru** > Susun blok > Klik **Publikasikan** agar bisa dibaca publik |
| **Mengaktifkan buku tamu acara/event** | Klik 📅 **Kelola Event** > Buat Event & pastikan status **Aktif** menyala > Bagikan tautan buku tamu |
| **Mengubah nomor WhatsApp / Kontak / Logo** | Klik ⚙️ **Pengaturan** > Ubah di bagian yang diinginkan > **💾 Simpan** |

---

## 2. Masuk ke CMS (Login)

CMS (*Content Management System*) adalah panel kontrol website PT Madeena Karya Indonesia untuk mengelola seluruh konten dan publikasi secara mudah tanpa memerlukan keahlian pemrograman.

**Langkah 1**: Buka peramban (*browser*) seperti Google Chrome, Mozilla Firefox, atau Microsoft Edge.
**Langkah 2**: Masuk ke alamat website Anda dan tambahkan `/admin` di belakangnya (contoh: `https://madeena.co.id/admin`).
**Langkah 3**: Anda dapat masuk menggunakan akun **Madeena IAM SSO** (*Single Sign-On*) atau memasukkan email dan kata sandi lokal, kemudian klik tombol **Masuk** (*Sign In*).

![Halaman Login](./screenshots/01-login/login-overview.png)
*Gambar 1: Halaman login untuk mengakses panel admin CMS Madeena.*

---

## 3. Mengenal Beranda (Dashboard)

Setelah berhasil masuk, Anda akan diarahkan ke halaman **Beranda** (*Dashboard*).

![Beranda Panel Admin](./screenshots/02-dashboard/dashboard-overview.png)
*Gambar 2: Tampilan Beranda (Dashboard) CMS Madeena.*

### Menu Utama di Bilah Samping (*Sidebar*):

- 🏠 **Halaman Utama**: Mengatur tata letak, teks, banner hero, dan bagian-bagian di halaman depan untuk setiap bahasa.
- 🌐 **Bahasa**: Mendaftarkan bahasa baru, mengaktifkan bahasa, dan mengatur terjemahan label antarmuka (*UI labels*).
- 📦 **Produk**: Mengelola katalog produk DDR (*Digital Direct Radiography*) dan spesifikasi teknisnya.
- 📝 **Artikel**: Menulis publikasi riset, artikel ilmiah, dan berita dengan editor akademik canggih.
- 📄 **Halaman**: Membuat halaman profil mandiri (seperti Sejarah Perusahaan, Visi & Misi) lengkap dengan fitur draft, pratinjau, dan publikasi.
- 📅 **Kelola Event**: Mengelola acara atau pameran dan mengaktifkan formulir buku tamu digital.
- ✉️ **Semua Pesan Tamu**: Memoderasi pesan, testimoni, serta kesan & pesan yang dikirim oleh pengunjung acara.
- ⚙️ **Pengaturan**: Mengatur informasi kontak resmi, akun media sosial, logo, warna tema, dan SEO Google.
- 👥 **Manajemen Pengguna**: Mengatur akun administrator dan staf penulis artikel.

---

## 4. Mengelola Halaman Utama Website (Homepage & Multibahasa)

Halaman utama website PT Madeena dibangun dengan sistem blok modular yang fleksibel dan mendukung multibahasa secara dinamis.

![Halaman Utama Editor](./screenshots/03-homepage/homepage-overview.png)
*Gambar 3: Editor Halaman Utama dengan daftar blok penyusun halaman.*

### Langkah-Langkah Mengedit Halaman Utama:

1. **Pilih Bahasa yang Ingin Diedit**:
   Gunakan menu pemilih bahasa di bagian atas editor untuk memilih bahasa yang ingin dikelola (misal: *Bahasa Indonesia* atau *English*).
2. **Buka dan Edit Blok**:
   Klik tanda panah (🔽) pada blok yang ingin diubah (seperti *Hero Banner*, *Produk*, *Tentang Kami*, atau *Kontak*).

   ![Edit Blok Halaman Utama](./screenshots/03-homepage/homepage-edit.png)
   *Gambar 4: Mengubah teks, gambar, dan tombol aksi di dalam blok halaman utama.*

3. **Pratinjau Perubahan**:
   Klik tombol **👁️ Pratinjau** di pojok kanan atas. Sistem akan membuka tab baru untuk memperlihatkan tampilan draf halaman tanpa mengganggu pengunjung umum.
4. **Simpan Draft vs Update Produksi**:
   - **💾 Simpan Draft**: Menyimpan pekerjaan Anda sebagai draf perantara. Pengunjung website belum akan melihat perubahan ini.
   - **🚀 Update Prod**: Menerapkan seluruh draf yang tersimpan langsung ke website live agar bisa dilihat oleh semua pengunjung publik.

> ⚠️ **Penting**: Perubahan yang Anda simpan sebagai draf tidak akan muncul di website publik sampai Anda menekan tombol hijau **Update Prod**.

### Menyalin Tata Letak ke Bahasa Lain (*Duplikasi Bahasa*):

Jika Anda ingin membuat versi halaman utama untuk bahasa baru (misal bahasa Inggris):
1. Buka halaman utama bahasa sumber (misal Bahasa Indonesia).
2. Klik tombol **📋 Duplikat ke Bahasa Lain** di bagian atas.
3. Pilih bahasa target yang terdaftar.
4. Sistem akan menyalin seluruh susunan blok sumber ke dalam **draf bahasa target**.
5. Ganti teks pada blok-blok bahasa target tersebut dengan terjemahan yang sesuai, lalu klik **Simpan Draft** dan **Update Prod**.

*Catatan: Sistem melindungi bahasa target agar tidak tertimpa jika bahasa target tersebut sudah memiliki draf atau konten yang dipublikasikan.*

---

## 5. Mengelola Bahasa Website (Manajemen Bahasa)

Website Madeena mendukung sistem multibahasa dinamis yang memungkinkan Anda menambah bahasa baru tanpa perlu mengubah kode program.

### Langkah-Langkah Menambah Bahasa Baru:
1. Klik menu 🌐 **Bahasa** di bilah samping kiri.
2. Klik tombol **Buat Baru**.
3. Isi informasi bahasa:
   - **Kode Bahasa**: Kode standar huruf kecil (contoh: `id`, `en`, `ja`, `pt-br`). *Perhatian: Kode bahasa tidak dapat diubah setelah dibuat.*
   - **Nama Bahasa**: Nama bahasa dalam bahasa Inggris (contoh: `Japanese`).
   - **Nama Asli (Native Name)**: Nama bahasa dalam aksara aslinya (contoh: `日本語`).
   - **Urutan Tampilan**: Angka urutan dalam menu pemilih bahasa publik.
   - **Status Aktif**: Jika dimatikan, bahasa ini tidak akan muncul di menu bahasa publik (berguna saat Anda masih menyiapkan konten).
   - **Label UI / Terjemahan Antarmuka**: Teks terjemahan untuk tombol umum seperti navigasi, kontak, tombol baca, dan hak cipta.
4. Klik **Simpan**.

*Catatan: Bahasa default (Bahasa Indonesia) dilindungi oleh sistem dan tidak dapat dinonaktifkan atau dihapus.*

---

## 6. Mengelola Produk Inovasi

Menu ini digunakan untuk menampilkan katalog sistem *Digital Direct Radiography* (DDR) dan inovasi teknologi medis lainnya.

![Daftar Produk](./screenshots/04-produk/produk-overview.png)
*Gambar 5: Daftar katalog produk inovasi PT Madeena.*

### Menambah atau Mengubah Produk:
1. Klik menu 📦 **Produk** > Klik **Buat Baru** atau ikon pensil (✏️) pada produk yang ada.

   ![Edit Produk](./screenshots/04-produk/produk-edit.png)
   *Gambar 6: Formulir pengisian spesifikasi dan gambar produk.*

2. Pada tab **Info Produk**:
   - Masukkan **Nama Produk** dan **Tagline**.
   - Isi tabel **Spesifikasi** (tambahkan baris untuk parameter seperti Resolusi Detektor, Tegangan Operasi kV, dan Bidang Aplikasi).
   - Unggah **Gambar Produk**.
   - Pastikan sakelar **Aktif** menyala agar produk tampil di website publik.
3. Pada tab **Halaman Detail Produk**:
   - Susun konten penjelasan mendalam menggunakan sistem blok (deskripsi fitur, galeri foto, video demonstrasi).
4. Klik tombol **Simpan**.

---

## 7. Menulis & Mengelola Artikel (Fitur Akademik)

CMS Madeena dilengkapi dengan pengolah kata ilmiah khusus untuk memublikasikan hasil penelitian dan jurnal medis standar internasional (gaya Elsevier/Nature).

![Daftar Artikel](./screenshots/05-artikel/artikel-overview.png)
*Gambar 7: Daftar artikel penelitian dan publikasi ilmiah.*

### Alur Menulis Artikel:
1. Klik menu 📝 **Artikel** > Klik **Buat Baru**.

   ![Buat Artikel Baru](./screenshots/05-artikel/artikel-edit.png)
   *Gambar 8: Tab metadata artikel dan penentuan status publikasi.*

2. **Tab Metadata Artikel**:
   - Isi **Judul Artikel** dan tentukan **Bahasa Konten**.
   - Isi **Kategori Konten** (misal: *Inovasi*, *Radiologi Medis*, *Kemitraan*).
   - Tentukan **Penempatan di Halaman Utama** jika ingin artikel ini ditampilkan pada bagian khusus di halaman depan.
   - Unggah **Gambar Sampul**.
   - Aktifkan sakelar **Publikasikan** jika artikel sudah siap dibaca publik.
3. **Tab Info Akademik**:
   - Masukkan **Abstrak Penelitian** dan **Kata Kunci** (*Keywords*).
   - Tambahkan daftar **Penulis Tambahan / Afiliasi** (nama institusi dan email).
4. **Tab Konten Artikel**:
   - Tulis isi artikel menggunakan *Rich Editor* ilmiah. Sistem secara otomatis menyimpan tulisan Anda setiap 3 detik.

   ![Editor Artikel](./screenshots/05-artikel/artikel-editor.png)
   *Gambar 9: Rich Editor artikel ilmiah dengan dukungan rumus KaTeX, gambar ber-caption, dan sitasi.*

### Menggunakan Blok Khusus Akademik:

- **A. Persamaan Matematika (Equation)**:
  Klik ikon rumus dan ketik persamaan dalam format LaTeX (contoh: `E = mc^2` atau `\sigma = \sqrt{\frac{1}{N}\sum_{i=1}^N (x_i - \mu)^2}`). Beri ID Referensi (contoh: `eq-1`).
- **B. Gambar Ilmiah (Figure)**:
  Unggah gambar, isi keterangan gambar (*Caption*), dan beri ID Referensi (contoh: `fig-1`). Sistem akan otomatis memberi nomor urut seperti *Gambar 1: Ilustrasi Detektor CCXD*.
- **C. Tabel Ilmiah (Table)**:
  Masukkan tabel HTML dan beri ID Referensi (contoh: `tbl-1`).
- **D. Daftar Pustaka & Sitasi**:
  Tambahkan blok *Daftar Pustaka*, isi data jurnal/buku. Di dalam paragraf tulisan Anda, ketik `[@1]` atau `[@2]`. CMS akan otomatis mengubahnya menjadi angka rujukan klik yang terhubung langsung ke daftar referensi di bawah artikel!

---

## 8. Mengelola Halaman Website (Halaman & Alur Publikasi)

Fitur 📄 **Halaman** digunakan untuk membuat halaman profil perusahaan yang berdiri sendiri (contoh: Sejarah Perusahaan, Visi & Misi, Sertifikasi Mutu ISO).

![Daftar Halaman](./screenshots/06-halaman/halaman-overview.png)
*Gambar 10: Daftar Halaman Kustom dengan status publikasi.*

### Alur Kerja Publikasi Halaman:

1. **Halaman Baru Dimulai Sebagai Draf**:
   Ketika Anda membuat halaman baru, halaman tersebut otomatis berstatus **Draft (Belum Dipublikasikan)** sehingga aman dari akses publik luar.
2. **Pratinjau Draf**:
   Klik tombol **👁️ Pratinjau** pada daftar halaman untuk memeriksa tampilan halaman draf di tab baru peramban Anda.
3. **Mempublikasikan Halaman**:
   Jika konten sudah siap dan disetujui, klik tombol hijau **Publikasikan**. Halaman akan langsung aktif dan dapat diakses publik pada alamat `/halaman/{slug-halaman}`.
4. **Menarik Kembali ke Draf (*Unpublish*)**:
   Jika halaman perlu ditarik sementara dari publik, klik tombol **Batal Publikasi**. Pengunjung umum yang membuka alamat tersebut akan melihat pesan *404 Halaman Tidak Ditemukan*.

> ⚠️ **Catatan Penting untuk Operator**:
> Fitur publikasi halaman berfungsi sebagai sakelar akses publik. Jika Anda mengedit halaman yang **sudah berstatus Terpublikasi**, setiap perubahan yang Anda simpan akan **langsung terupdate di website live**.
> *Tips: Jika ingin melakukan perombakan besar pada halaman yang sudah live, Anda dapat menekan "Batal Publikasi" terlebih dahulu atau membuat halaman draf baru.*

*(Catatan Navigasi: Opsi centang Header/Footer di form halaman merupakan catatan internal. Untuk menambahkan tautan halaman ke menu atas website, gunakan menu **Pengaturan > Navigasi Tambahan**).*

---

## 9. Mengelola Acara (Event) & Buku Tamu Digital

CMS Madeena dilengkapi modul acara untuk mendukung partisipasi PT Madeena dalam pameran alkes, simposium kesehatan, dan expo teknologi.

![Daftar Event](./screenshots/09-events/events-overview.png)
*Gambar 11: Daftar Acara dan Event PT Madeena.*

### Mengatur Event:
1. Klik menu 📅 **Kelola Event** > Klik **Buat Baru**.
2. Masukkan **Nama Acara**, **Deskripsi**, serta **Tanggal Mulai dan Selesai**.
3. **Sakelar Status Aktif (`is_active`)**:
   - **Aktif (Nyala)**: Formulir buku tamu di `/events/{slug}/feedback` dapat diakses dan menerima kiriman kesan & pesan dari pengunjung pameran.
   - **Nonaktif (Mati)**: Formulir buku tamu otomatis ditutup (menampilkan 404).

### Memoderasi Pesan Tamu (Guest Messages):
Setiap ulasan atau pesan yang dikirim oleh tamu pameran akan otomatis tercatat di menu ✉️ **Semua Pesan Tamu**.

![Daftar Pesan Tamu](./screenshots/10-guest-messages/guest-messages-overview.png)
*Gambar 12: Daftar pesan dan kesan tamu yang masuk.*

- Klik ikon edit (✏️) untuk melihat detail instansi, jabatan, dan nomor kontak tamu.
- **Sakelar Visibilitas (`is_visible`)**:
  - Jika Anda ingin menampilkan pesan tersebut di layar pameran live (`/events/{slug}/display`), pastikan status visibilitas aktif.
  - Jika pesan bersifat pribadi atau tidak layak tayang di layar umum, matikan sakelar visibilitas.

*Sistem dilengkapi perlindungan anti-spam otomatis, batas kecepatan pengiriman per menit, dan pencegahan pengiriman ganda.*

---

## 10. Manajemen Pengguna (Hak Akses Admin & Penulis)

Menu 👥 **Manajemen Pengguna** digunakan untuk mengatur siapa saja yang berhak masuk ke dalam CMS.

![Daftar Pengguna](./screenshots/08-pengguna/pengguna-overview.png)
*Gambar 13: Daftar pengguna CMS dan perannya.*

### Jenis Peran (*Role*):
- **Admin**: Memiliki kendali penuh atas semua menu (Halaman Utama, Bahasa, Produk, Artikel, Halaman, Event, Pengaturan, dan Pengguna).
- **User (Penulis)**: Dikhususkan bagi peneliti atau staf penulis yang hanya memiliki akses untuk menulis dan mengedit artikel penelitian milik mereka sendiri. Menu lain akan disembunyikan secara otomatis.

---

## 11. Pengaturan Website

Menu ⚙️ **Pengaturan** digunakan untuk mengatur identitas global dan branding perusahaan.

![Pengaturan Website](./screenshots/07-pengaturan/pengaturan-overview.png)
*Gambar 14: Pengaturan kontak resmi, SEO, branding logo, dan tombol WhatsApp.*

- **Informasi Kontak**: Mengubah alamat kantor pusat Yogyakarta, email resmi, dan nomor telepon layanan.
- **Media Sosial**: Menautkan akun resmi LinkedIn, Instagram, dan YouTube riset.
- **SEO**: Mengatur judul (*Meta Title*) dan deskripsi singkat (*Meta Description*) yang muncul pada pencarian Google.
- **Navigasi Tambahan**: Menambahkan menu link kustom pada navigasi atas website.
- **Pengaturan Tampilan (Branding)**: Mengunggah file Logo resmi PT Madeena, menentukan warna tema utama (*Primary/Secondary*), dan memilih jenis huruf (*Font Family*).
- **Tombol WhatsApp Melayang**: Menampilkan tombol chat WhatsApp di sudut kanan bawah website agar pengunjung dapat langsung berkonsultasi dengan staf teknis.

---

## 12. Tanya Jawab & Pemecahan Masalah (FAQ)

**T: Mengapa perubahan yang saya simpan di Halaman Utama belum terlihat oleh pengunjung umum?**
J: Anda kemungkinan baru menekan tombol **💾 Simpan Draft**. Untuk menerapkannya ke pengunjung umum, klik tombol hijau **🚀 Update Prod** di pojok kanan atas editor.

**T: Mengapa bahasa yang baru saya buat belum muncul di pemilih bahasa website?**
J: Pastikan status **Aktif** pada bahasa tersebut sudah dinyalakan di menu 🌐 **Bahasa**, dan pastikan Anda sudah mengisi draf halaman utama untuk bahasa tersebut serta menekan tombol **Update Prod**.

**T: Bagaimana cara membuat halaman yang hanya bisa dilihat oleh tim internal terlebih dahulu?**
J: Biarkan status halaman dalam keadaan **Draft (Belum Dipublikasikan)**, lalu gunakan tombol **👁️ Pratinjau** untuk melihat tampilannya. Pengunjung umum tidak akan bisa membuka halaman tersebut sampai Anda menekan **Publikasikan**.

**T: Mengapa formulir buku tamu event menampilkan pesan "Halaman Tidak Ditemukan (404)"?**
J: Periksa menu 📅 **Kelola Event** dan pastikan sakelar **Aktif** pada acara tersebut dalam posisi menyala.

**T: Bagaimana cara membuat kutipan rumus atau gambar akademik di dalam teks artikel?**
J: Pastikan blok rumus atau gambar telah diberi ID referensi (contoh: `eq-1` atau `fig-1`), lalu di dalam teks ketikkan `[@Persamaan 1]` atau `[@Gambar 1]`.

**T: Saya lupa kata sandi login, bagaimana cara memperbaruinya?**
J: Jika akun Anda terhubung dengan Madeena IAM SSO, silakan gunakan fitur pemulihan kata sandi pada portal Madeena IAM. Jika menggunakan akun lokal, hubungi administrator utama untuk mengatur ulang kata sandi Anda.

---

### 📞 Kontak Bantuan Teknis

Jika memerlukan bantuan teknis lebih lanjut mengenai pengoperasian CMS, silakan hubungi:

- **Tim Teknis PT Madeena Karya Indonesia**: madeenajog@gmail.com
- **Layanan WhatsApp**: +62 857 2830 4141
- **Jam Operasional Dukungan**: Senin – Jumat, 08.00 – 17.00 WIB
