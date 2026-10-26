# Mini Application OOP — Pendaftaran Kegiatan Kampus
TI033015 Pemrograman Berorientasi Objek · Pertemuan 5

## Masalah
Kampus punya beberapa jenis kegiatan (seminar, workshop) dengan aturan kuota yang sama, tetapi biaya dan info khusus yang berbeda. Satu class `Kegiatan` (Pertemuan 4) akan penuh `if/else` jika jenis bertambah.

## Kandidat class
`Kegiatan` (abstract), `Seminar`, `Workshop`, `Peserta`

## Desain & alasan relasi
- **Inheritance + abstract**: Seminar dan Workshop *adalah* Kegiatan; validasi nama, jadwal, kuota, dan status ditulis sekali di parent. `hitungBiaya()` dibuat abstract agar tiap jenis wajib mendefinisikannya.
- **Overriding**: `hitungBiaya()` (gratis vs biaya tetap) dan `tampilkanInfo()` di Seminar (menambah pembicara).
- **Polymorphism**: array berisi Workshop dan Seminar diproses dengan `foreach` memanggil `tampilkanInfo()` tanpa `if/else` jenis.
- **Composition**: Kegiatan *memiliki* daftar `Peserta`. Peserta bukan jenis kegiatan, jadi tidak diwariskan.

## Menjalankan
`php Tugas_P5_PBO.php` — 7 skenario (normal, batas, salah).

## Refleksi
(Isi dengan kata-kata sendiri: bagian tersulit, manfaat polymorphism yang terasa, yang bisa diperbaiki.)
