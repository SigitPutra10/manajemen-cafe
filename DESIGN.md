# DESIGN.md — Sistem Desain Manajemen Cafe & POS

## 1. Filosofi & Referensi Visual
- **Karakter Desain:** Hangat, Taktil, Fungsional, dan Efisien (terinspirasi dari *Square POS*, *Toast*, dan estetika kedai kopi modern).
- **Prioritas Utama:** Kecepatan baca dan kemudahan klik untuk kasir/barista di layar desktop maupun tablet (minimal ukuran area sentuh tombol `44px x 44px`).
- **Kepadatan Informasi:** Padat namun terstruktur rapi dengan pembatas garis tipis (`1px`), bukan kartu melayang yang terlalu banyak ruang kosong sia-sia.

## 2. Palet Warna (Strict Mode)
Dilarang menggunakan warna di luar daftar ini kecuali untuk status operasional yang jelas:
- **Latar Utama (Canvas):** `#F7F5F0` (Krem hangat / kertas gading agar mata kasir tidak cepat lelah menatap layar seharian)
- **Latar Kartu / Panel (Surface):** `#FFFFFF` (Putih bersih untuk area keranjang pesanan dan tabel data)
- **Latar Hover / Elemen Sekunder:** `#EFECE6`
- **Garis Pembatas (Border):** `#E2DDD5` (`1px solid`, digunakan sebagai pemisah antar panel dan item)
- **Teks Utama:** `#1C1917` (Hitam arang hangat, kontras sangat tinggi)
- **Teks Sekunder / Keterangan:** `#57534E` (Abu-abu batu hangat untuk kategori menu atau catatan tambahan)
- **Aksen Utama (Primary Action / Tombol Bayar):** `#9A3412` (Terakota / Espresso pekat — hanya untuk tombol aksi terpenting seperti "Bayar Sekarang" atau "Simpan Pesanan")
- **Aksen Hover:** `#7C2D12`
- **Warna Status Semantik:**
  - Tersedia / Lunas: `#15803D` (Hijau daun tua) dengan latar `#DCFCE7`
  - Stok Menipis / Menunggu: `#B45309` (Amber hangat) dengan latar `#FEF3C7`
  - Habis / Batal: `#B91C1C` (Merah bata) dengan latar `#FEE2E2`

## 3. Tipografi
- **Font Antarmuka & Judul:** `Plus Jakarta Sans` (Weight: `600` untuk judul bagian dan nama menu, `700` untuk total harga)
- **Font Angka & Harga (Wajib):** Gunakan `tabular-nums` (`font-variant-numeric: tabular-nums`) pada semua nominal Rupiah, kuantitas, dan kode struk agar deretan angka sejajar lurus ke bawah.
- **Format Mata Uang:** Selalu gunakan format standar Indonesia yang bersih, contoh: `Rp 28.000` (tanpa `,00` di belakang).

## 4. Tata Letak & Komponen UI
- **Layout Kasir (POS):** Gunakan tata letak *Split-Screen* (2 kolom):
  - **Kiri (65% lebar):** Bar pencarian cepat + tab kategori menu horizontal + grid kartu produk (maksimal 3–4 kolom di desktop).
  - **Kanan (35% lebar, Sticky):** Panel keranjang pesanan aktif, pilihan tipe pesanan (*Dine-in* / *Takeaway*), rincian subtotal/pajak, dan tombol bayar besar di bagian bawah.
- **Radius Sudut:** Maksimal `8px` untuk kartu menu dan modal; `6px` untuk tombol dan input form.
- **Bayangan (Shadow):** Hindari `drop-shadow` tebal. Gunakan garis tepi `1px solid #E2DDD5` atau bayangan sangat tipis (`0 1px 2px rgba(28, 25, 23, 0.05)`).

## 5. Bahasa & Copywriting (Bahasa Indonesia Alami)
- Gunakan istilah operasional cafe sehari-hari yang singkat dan langsung dipahami staf:
  - Gunakan **"Bayar Sekarang"**, bukan *"Lanjutkan ke Proses Checkout"*.
  - Gunakan **"Stok Habis"**, bukan *"Maaf, item ini sedang tidak tersedia saat ini"*.
  - Gunakan **"Meja 04"**, **"Tanpa Gula"**, **"Es Sedikit"**.

## 6. Aturan Larangan Keras (Anti-Slop)
- ❌ **DILARANG** menggunakan warna gradasi ungu, biru neon, atau cyan.
- ❌ **DILARANG** memakai efek *glassmorphism* (latar belakang blur transparan).
- ❌ **DILARANG** meletakkan emoji (☕, ✨, 🚀, 🔥) di dalam nama tombol, judul menu, atau navigasi.
- ❌ **DILARANG** menampilkan angka statistik atau grafik hiasan palsu yang tidak terhubung dengan data transaksi nyata.
