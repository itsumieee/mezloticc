<div align="center">

# PROFILE / INDEX

**Public game profiles, organized in one place.**

An expanding collection of account tools for Roblox and other games.

<br />

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![React](https://img.shields.io/badge/React-18-61DAFB?style=flat-square&logo=react&logoColor=111827)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2-9553E9?style=flat-square&logo=inertia&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-7-646CFF?style=flat-square&logo=vite&logoColor=white)

</div>

---

## Tentang proyek

**Profile / Index** adalah direktori game yang menghubungkan pengguna ke alat pemeriksaan profil publik. Pilih game dari halaman utama untuk membuka fitur yang tersedia. Antarmuka dibangun dengan React dan Inertia.js, sementara Laravel menangani routing, layanan, dan data dari API.

> Data yang tidak tersedia dari sumber resmi ditampilkan sebagai tidak tersedia. Aplikasi tidak mengarang detail akun, jumlah item, rating, ataupun nilai.

## Game dan fitur

| Game | Status | Fitur |
| --- | --- | --- |
| **Roblox** | Tersedia | Pencarian profil publik, avatar, inventory, limited, bundle, statistik, riwayat, watchlist, perbandingan, serta ekspor data. |
| **Mobile Legends: Bang Bang** | Pratinjau | Antarmuka laporan akun berdasarkan Player ID dan Server ID. Belum terhubung ke API MLBB; lookup, rating, koleksi skin, dan estimasi top-up belum tersedia. |
| Free Fire, PUBG Mobile, Honor of Kings | Segera hadir | Kartu game informatif; alat pemeriksaan akun belum tersedia. |

## Tampilan

- Halaman utama berfungsi sebagai indeks game yang dapat dikembangkan.
- Setiap game yang tersedia membuka halaman fiturnya sendiri.
- Scene 3D latar bersama mengikuti gerakan halaman dan pointer, dengan dukungan tampilan responsif.
- Animasi menghormati preferensi `prefers-reduced-motion`.

## Teknologi

| Bagian | Teknologi |
| --- | --- |
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | React 18, Inertia.js, Vite |
| Styling | Tailwind CSS |
| 3D & animasi | React Three Fiber, drei, Framer Motion, Lenis, GSAP ScrollTrigger |

## Menjalankan secara lokal

### Prasyarat

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm

### Instalasi

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

Jika file `.env` sudah ada, lewati perintah `Copy-Item`. Pastikan konfigurasi database pada `.env` sudah sesuai sebelum menjalankan migrasi.

### Mode pengembangan

Jalankan seluruh layanan pengembangan Laravel dan Vite:

```powershell
composer run dev
```

Atau jalankan backend dan frontend secara terpisah di terminal masing-masing:

```powershell
php artisan serve
npm run dev
```

Untuk membuat build frontend produksi:

```powershell
npm run build
```

## Struktur frontend

```text
resources/
├── js/
│   ├── Components/   # Komponen UI bersama
│   ├── Layouts/      # Shell dan navigasi Inertia
│   └── Pages/        # Halaman game dan alat akun
├── css/              # Gaya aplikasi
└── views/
    └── app.blade.php # Mount point Inertia
```

## Rute utama

| Rute | Kegunaan |
| --- | --- |
| `/` | Indeks dan pemilih game |
| `/roblox` | Alat akun Roblox |
| `/mlbb` | Pratinjau laporan akun MLBB |
| `/history` | Riwayat pencarian |
| `/compare` | Perbandingan profil |
| `/watchlist` | Daftar pantauan |
| `/api-docs` | Dokumentasi API |
| `/health` | Pemeriksaan kesehatan aplikasi |

## Catatan data dan aset

- Konten Roblox berasal dari aplikasi atau respons API Roblox yang tersedia untuk umum.
- Halaman MLBB saat ini hanya pratinjau UI dan tidak mengklaim menghasilkan data akun nyata.
- Gambar game MLBB dan Roblox berada di `public/images/games/`; ilustrasi judul lain yang belum tersedia dibuat lokal dan bergaya ilustratif.
- Ekspor JSON/SVG pada pratinjau MLBB tidak memuat data akun terverifikasi.
