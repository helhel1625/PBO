<?php
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

/*
 * Abstract class : Kegiatan
 * Inheritance    : Seminar, Workshop extends Kegiatan
 * Overriding     : hitungBiaya(), tampilkanInfo()
 * Polymorphism   : foreach $daftar -> tampilkanInfo() / hitungBiaya()
 * Composition    : Kegiatan has-a Peserta
 */

class Peserta
{
    public function __construct(private string $nama)
    {
        if (trim($nama) === "") {
            throw new InvalidArgumentException("Nama peserta tidak boleh kosong");
        }
    }

    public function getNama(): string
    {
        return $this->nama;
    }
}

abstract class Kegiatan
{
    private string $namaAcara;
    private string $jadwal;
    private int $kuota;
    private string $status = "buka";
    private array $peserta = []; // composition: daftar objek Peserta

    public function __construct(string $namaAcara, string $jadwal, int $kuota)
    {
        $this->setNamaAcara($namaAcara);
        $this->setJadwal($jadwal);
        $this->setKuota($kuota);
    }

    // Wajib diwujudkan subclass
    abstract public function hitungBiaya(): float;

    public function getNamaAcara(): string { return $this->namaAcara; }
    public function getKuota(): int { return $this->kuota; }
    public function getStatus(): string { return $this->status; }

    public function setNamaAcara(string $namaAcara): void
    {
        if (strlen(trim($namaAcara)) < 3) {
            throw new InvalidArgumentException("Nama acara minimal 3 karakter");
        }
        $this->namaAcara = trim($namaAcara);
    }

    public function setJadwal(string $jadwal): void
    {
        if (trim($jadwal) === "") {
            throw new InvalidArgumentException("Jadwal tidak boleh kosong");
        }
        $this->jadwal = trim($jadwal);
    }

    public function setKuota(int $kuota): void
    {
        if ($kuota < 0) {
            throw new InvalidArgumentException("Kuota tidak valid: $kuota (tidak boleh negatif)");
        }
        $this->kuota = $kuota;
        if ($kuota === 0) {
            $this->status = "tutup";
        }
    }

    public function daftar(Peserta $peserta): void
    {
        if ($this->status !== "buka" || $this->kuota <= 0) {
            throw new RuntimeException("Pendaftaran {$this->namaAcara} sudah ditutup atau kuota penuh");
        }
        $this->peserta[] = $peserta;
        $this->kuota--;
        echo "{$peserta->getNama()} terdaftar di {$this->namaAcara}. Sisa kuota: {$this->kuota}\n";
        if ($this->kuota === 0) {
            $this->status = "tutup"; // otomatis tutup saat kuota habis
        }
    }

    public function bukaPendaftaran(): void
    {
        if ($this->kuota <= 0) {
            throw new RuntimeException("Pendaftaran tidak bisa dibuka: kuota {$this->namaAcara} habis");
        }
        $this->status = "buka";
    }

    public function tampilkanInfo(): void
    {
        echo "Kegiatan : {$this->namaAcara}\n";
        echo "Jadwal   : {$this->jadwal}\n";
        echo "Kuota    : {$this->kuota} (peserta: " . count($this->peserta) . ")\n";
        echo "Biaya    : Rp" . number_format($this->hitungBiaya(), 0, ',', '.') . "\n";
        echo "Status   : {$this->status}\n";
    }
}

class Seminar extends Kegiatan
{
    public function __construct(string $namaAcara, string $jadwal, int $kuota, private string $pembicara)
    {
        parent::__construct($namaAcara, $jadwal, $kuota);
    }

    public function hitungBiaya(): float // override: seminar gratis
    {
        return 0;
    }

    public function tampilkanInfo(): void // override: tambah info pembicara
    {
        parent::tampilkanInfo();
        echo "Pembicara: {$this->pembicara}\n";
    }
}

class Workshop extends Kegiatan
{
    public function __construct(string $namaAcara, string $jadwal, int $kuota, private float $biaya)
    {
        parent::__construct($namaAcara, $jadwal, $kuota);
    }

    public function hitungBiaya(): float // override: biaya tetap
    {
        return $this->biaya;
    }
}

// ===== PENGUJIAN =====
function skenario(string $judul, callable $uji): void
{
    echo "\n### $judul\n";
    try {
        $uji();
    } catch (Throwable $e) {
        echo "[DITOLAK - " . get_class($e) . "] " . $e->getMessage() . "\n";
    }
}

$kegiatan1 = new Workshop("Skill Exchange", "15 Oktober 2026", 2, 50000);
$kegiatan2 = new Workshop("Latihan Kepemimpinan", "25 Oktober 2026", 30, 75000);
$kegiatan3 = new Seminar("Seminar PBO", "1 November 2026", 100, "Ahmad Fauzi");
$daftar = [$kegiatan1, $kegiatan2, $kegiatan3]; // tipe berbeda, satu array

echo "=== DATA AWAL (polymorphism) ===\n";
foreach ($daftar as $k) {
    $k->tampilkanInfo(); // satu pemanggilan, perilaku berbeda
    echo "-------------------------------------\n";
}

skenario("Skenario 1 (NORMAL): pendaftaran berhasil", fn() => $kegiatan2->daftar(new Peserta("Andi")));

skenario("Skenario 2 (BATAS): kuota habis -> otomatis tutup", function () use ($kegiatan1) {
    $kegiatan1->daftar(new Peserta("Andi"));
    $kegiatan1->daftar(new Peserta("Budi"));
    echo "Status: " . $kegiatan1->getStatus() . "\n";
});

skenario("Skenario 3 (BATAS): mendaftar setelah kuota habis", fn() => $kegiatan1->daftar(new Peserta("Citra")));
skenario("Skenario 4 (SALAH): kuota negatif", fn() => new Seminar("Seminar PBO", "1 November 2026", -5, "Ahmad Fauzi"));
skenario("Skenario 5 (SALAH): nama acara kosong", fn() => $kegiatan2->setNamaAcara("  "));
skenario("Skenario 6 (SALAH): buka pendaftaran saat kuota 0", fn() => $kegiatan1->bukaPendaftaran());
skenario("Skenario 7 (SALAH): membuat objek dari abstract class", fn() => new Kegiatan("Umum", "1 Desember 2026", 10));

echo "\n=== DATA AKHIR ===\n";
foreach ($daftar as $k) {
    $k->tampilkanInfo();
    echo "-------------------------------------\n";
}
