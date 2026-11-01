<?php
/* DATA LAYANAN & HARGA — dibaca dari tabel `layanan` (database).
   Ubah harga/layanan di phpMyAdmin, tampilan web ikut berubah.
   Dipakai oleh: Home, Services, Price List, form Booking, dan validasi booking. */

require_once __DIR__ . '/db.php';

function rp($angka): string {
  return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/* Semua layanan aktif, urut sesuai kolom `urutan`. Hasil disimpan sementara per request. */
function layanan_semua(): array {
  static $cache = null;
  if ($cache !== null) return $cache;
  try {
    $cache = db()->query(
      'SELECT id_layanan, nama_layanan, deskripsi, harga, satuan, gambar, gambar_harga,
              ikon, kategori, kelompok, tampil_layanan, tampil_harga, bisa_booking, unggulan
       FROM layanan
       WHERE aktif = 1
       ORDER BY urutan, id_layanan'
    )->fetchAll();
  } catch (Throwable $e) {
    error_log('Gagal memuat layanan: ' . $e->getMessage());
    $cache = [];
  }
  return $cache;
}

function layanan_harga_teks(array $r): string {
  return rp($r['harga']) . ($r['satuan'] ?? '');
}

/* Nama layanan yang boleh dibooking (juga dipakai untuk filter kategori galeri) */
function layanan_nama_booking(): array {
  $nama = [];
  foreach (layanan_semua() as $r) {
    if ((int) $r['bisa_booking'] === 1) $nama[] = $r['nama_layanan'];
  }
  return $nama;
}

/* Pilihan layanan di form booking: [ikon, nama, teks harga] */
function layanan_booking(): array {
  $o = [];
  foreach (layanan_semua() as $r) {
    if ((int) $r['bisa_booking'] === 1) {
      $o[] = [$r['ikon'], $r['nama_layanan'], 'Mulai ' . layanan_harga_teks($r)];
    }
  }
  return $o;
}

/* Tombol filter kategori halaman Services: [label, kunci, ikon] */
function layanan_filter(): array {
  $f = [['Semua Layanan']];
  foreach (layanan_semua() as $r) {
    if ((int) $r['tampil_layanan'] === 1) $f[] = [$r['nama_layanan'], $r['kategori'], $r['ikon']];
  }
  return $f;
}

/* Item kartu untuk s_cards(), sesuai 'source' di data/content.php */
function layanan_items(string $sumber): array {
  $o = [];
  foreach (layanan_semua() as $r) {
    $teks = layanan_harga_teks($r);

    if ($sumber === 'layanan' || $sumber === 'beranda_layanan') {
      if ((int) $r['tampil_layanan'] !== 1) continue;
      $o[] = ['img' => $r['gambar'], 'icon' => $r['ikon'], 'title' => $r['nama_layanan'],
              'text' => $r['deskripsi'], 'label' => 'Mulai dari', 'price' => $teks,
              'cat' => $r['kategori'], 'url' => 'booking.php'];
    } elseif ($sumber === 'beranda_harga') {
      if ((int) $r['unggulan'] !== 1) continue;
      $o[] = ['icon' => $r['ikon'], 'title' => $r['nama_layanan'], 'label' => 'Mulai dari',
              'price' => $teks, 'url' => 'pricelist.php'];
    } elseif ($sumber === 'harga_utama' || $sumber === 'harga_lainnya') {
      if ((int) $r['tampil_harga'] !== 1) continue;
      if ($r['kelompok'] !== ($sumber === 'harga_utama' ? 'utama' : 'lainnya')) continue;
      $o[] = ['img' => ($r['gambar_harga'] ?: $r['gambar']), 'title' => $r['nama_layanan'],
              'text' => $r['deskripsi'], 'price' => 'Mulai dari ' . $teks, 'url' => 'booking.php'];
    }
  }
  if ($sumber === 'beranda_layanan') $o = array_slice($o, 0, 5);
  return $o;
}
