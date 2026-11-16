<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_data_diri';

function e($teks)
{
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

function formatTanggalIndonesia($tanggal)
{
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
    ];

    $d = DateTime::createFromFormat('Y-m-d', $tanggal);

    if (!$d || $d->format('Y-m-d') !== $tanggal) {
        return null;
    }

    $hari       = (int) $d->format('j');
    $namaBulan  = $bulan[(int) $d->format('n')];
    $tahun      = $d->format('Y');

    return $hari . ' ' . $namaBulan . ' ' . $tahun;
}

$pilihan_hobi   = ['Membaca', 'Olahraga', 'Musik', 'Lainnya'];

$nama           = '';
$tanggal_lahir  = '';
$hobi_dipilih   = [];

$errors         = [];
$berhasil       = false;
$tanggal_tampil = '';
$hobi_tampil    = '';
$status_db      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama          = trim($_POST['nama'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $kiriman_hobi  = $_POST['hobi'] ?? [];

    if (is_array($kiriman_hobi)) {
        $hobi_dipilih = array_values(array_intersect($pilihan_hobi, $kiriman_hobi));
    }

    if ($nama === '') {
        $errors[] = 'Nama Lengkap wajib diisi.';
    }

    if ($tanggal_lahir === '') {
        $errors[] = 'Tanggal Lahir wajib diisi.';
    } elseif (formatTanggalIndonesia($tanggal_lahir) === null) {
        $errors[] = 'Format Tanggal Lahir tidak valid.';
    }

    if (!$errors) {
        $berhasil       = true;
        $tanggal_tampil = formatTanggalIndonesia($tanggal_lahir);

        if ($hobi_dipilih) {
            $hobi_tampil = implode(', ', $hobi_dipilih);
        } else {
            $hobi_tampil = 'Tidak ada hobi yang dipilih';
        }

        // Simpan ke database
        mysqli_report(MYSQLI_REPORT_OFF);
        $koneksi = @new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($koneksi->connect_errno) {
            $status_db = 'Data belum tersimpan ke database (cek MySQL dan nama database).';
        } else {
            $koneksi->set_charset('utf8mb4');

            $hobi_simpan = implode(', ', $hobi_dipilih);
            $sql  = 'INSERT INTO data_diri (nama_lengkap, tanggal_lahir, hobi) VALUES (?, ?, ?)';
            $stmt = $koneksi->prepare($sql);

            if ($stmt && $stmt->bind_param('sss', $nama, $tanggal_lahir, $hobi_simpan) && $stmt->execute()) {
                $status_db = 'Data berhasil disimpan ke database.';
            } else {
                $status_db = 'Data belum tersimpan ke database (tabel data_diri belum ada?).';
            }

            $koneksi->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $berhasil ? 'Tampilan Data Anda' : 'Formulir Data Diri' ?></title>

  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f2f5;
      display: flex;
      justify-content: center;
      padding: 40px 16px;
    }

    .card {
      background: #fff;
      width: 100%;
      max-width: 420px;
      padding: 28px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
      margin-top: 0;
    }

    label,
    legend {
      display: block;
      font-weight: bold;
      margin: 16px 0 6px;
    }

    input[type=text],
    input[type=date] {
      width: 100%;
      padding: 9px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }

    fieldset {
      border: 0;
      padding: 0;
      margin: 0;
    }

    .hobi label {
      font-weight: normal;
      margin: 6px 0;
    }

    button {
      width: 100%;
      margin-top: 20px;
      padding: 11px;
      background: #1769e0;
      color: #fff;
      border: 0;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
    }

    button:hover {
      background: #1156b8;
    }

    a {
      color: #1769e0;
      text-decoration: none;
    }

    .baris {
      display: flex;
      padding: 12px 0;
      border-bottom: 1px solid #eee;
    }

    .baris dt {
      width: 130px;
      font-weight: bold;
    }

    .baris dd {
      margin: 0;
    }

    .error {
      background: #fdecec;
      color: #c62828;
      padding: 10px 14px;
      border-radius: 6px;
    }

    .error ul {
      margin: 0;
      padding-left: 18px;
    }

    .catatan {
      color: #777;
      font-size: 0.85rem;
      margin-top: 16px;
    }

    @media (max-width: 480px) {
      .card {
        padding: 20px;
      }

      .baris {
        flex-direction: column;
      }
    }
  </style>
</head>

<body>
  <main class="card">
    <?php if ($berhasil): ?>
      <h1>Tampilan Data Anda</h1>
      <dl>
        <div class="baris">
          <dt>Nama Lengkap:</dt>
          <dd><?= e($nama) ?></dd>
        </div>

        <div class="baris">
          <dt>Tanggal Lahir:</dt>
          <dd><?= e($tanggal_tampil) ?></dd>
        </div>

        <div class="baris">
          <dt>Hobi:</dt>
          <dd><?= e($hobi_tampil) ?></dd>
        </div>
      </dl>

      <a href="<?= e(basename($_SERVER['PHP_SELF'])) ?>">&laquo; Kembali ke Form</a>

      <?php if ($status_db): ?>
        <p class="catatan"><?= e($status_db) ?></p>
      <?php endif; ?>

    <?php else: ?>
      <h1>Formulir Data Diri</h1>
      <?php if ($errors): ?>
        <div class="error" role="alert">
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?= e($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="<?= e(basename($_SERVER['PHP_SELF'])) ?>" method="POST">
        <label for="nama">Nama Lengkap: *</label>
        <input
          type="text"
          id="nama"
          name="nama"
          value="<?= e($nama) ?>"
          maxlength="100"
          required>

        <label for="tanggal_lahir">Tanggal Lahir: *</label>
        <input
          type="date"
          id="tanggal_lahir"
          name="tanggal_lahir"
          value="<?= e($tanggal_lahir) ?>"
          required>

        <fieldset>
          <legend>Hobi:</legend>
          <div class="hobi">
            <?php foreach ($pilihan_hobi as $hobi): ?>
              <label>
                <input
                  type="checkbox"
                  name="hobi[]"
                  value="<?= e($hobi) ?>"
                  <?= in_array($hobi, $hobi_dipilih, true) ? 'checked' : '' ?>>
                <?= e($hobi) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <button type="submit">Kirim</button>
      </form>

    <?php endif; ?>
  </main>
</body>
</html>