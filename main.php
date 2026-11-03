<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(config('app.name')) ?></title>

<link href="<?= config('app.url') ?>/assets/css/vendor/bootstrap.min.css" rel="stylesheet">
<link href="<?= config('app.url') ?>/assets/css/vendor/fontawesome.min.css" rel="stylesheet">
<link href="<?= config('app.url') ?>/assets/css/vendor/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= config('app.url') ?>/assets/css/vendor/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= config('app.url') ?>/assets/css/app.css">

<script>
const APP_URL = "<?= config('app.url') ?>";
</script>
<script src="<?= config('app.url') ?>/assets/js/vendor/jquery.min.js"></script>
<script src="<?= config('app.url') ?>/assets/js/vendor/bootstrap.bundle.min.js"></script>
<script src="<?= config('app.url') ?>/assets/js/vendor/sweetalert2.all.min.js"></script>
<script src="<?= config('app.url') ?>/assets/js/vendor/jquery.dataTables.min.js"></script>
<script src="<?= config('app.url') ?>/assets/js/vendor/dataTables.bootstrap5.min.js"></script>
<script src="<?= config('app.url') ?>/assets/js/vendor/chart.umd.min.js"></script>
<!--
    PENTING: Semua library (CSS & JS) sekarang disimpan LOKAL di dalam proyek
    (public/assets/*/vendor/), bukan diambil dari CDN internet. Ini disengaja agar
    aplikasi tetap berfungsi penuh walau dijalankan di jaringan yang memblokir akses
    CDN eksternal (banyak terjadi di jaringan instansi/kantor).

    Library dimuat di <head> (bukan sebelum </body>) supaya SIAP sebelum <script>
    khusus tiap halaman (mis. yang memanggil `new Chart(...)` di dashboard) dijalankan,
    karena isi $content disisipkan di tengah <body>, sebelum posisi lama script ini.
-->
</head>
<body>

<?php
// Deteksi menu aktif berdasarkan path saat ini, untuk memberi highlight di sidebar
$__uriSaatIni = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$__basePath = parse_url(config('app.url'), PHP_URL_PATH) ?? '';
if ($__basePath !== '' && str_starts_with($__uriSaatIni, $__basePath)) {
    $__uriSaatIni = substr($__uriSaatIni, strlen($__basePath));
}
$__uriSaatIni = '/' . trim($__uriSaatIni, '/');

// Closure (bukan fungsi biasa + global) karena file ini di-require dari dalam
// method Controller::view(), sehingga tidak berada di scope global asli PHP.
$__menuAktif = function (string $prefix) use ($__uriSaatIni): string {
    return str_starts_with($__uriSaatIni, $prefix) ? 'active' : '';
};

$__notifModel = new NotifikasiModel();
$__belumDibaca = $__notifModel->jumlahBelumDibaca();
$__notifTerbaru = $__notifModel->terbaru(5);
?>

<!-- ===================== HEADER ===================== -->
<header class="app-header">
    <div class="header-inner d-flex align-items-center justify-content-between px-3 px-md-4 py-2">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm header-icon-btn d-md-none" id="btn-toggle-sidebar" style="width:36px;height:36px;">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="app-logo"><img src="<?= config('app.url') ?>/assets/images/logo-bps.png" alt="Logo BPS"></div>
            <div class="d-none d-sm-block">
                <div class="app-title">SISTEM INFORMASI<br>PENGINGAT KENAIKAN GAJI BERKALA (KGB)</div>
                <div class="app-subtitle">BADAN PUSAT STATISTIK KOTA BANDUNG</div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 gap-md-3">
            <div class="header-search d-none d-lg-block position-relative" style="width:280px;">
                <input type="text" class="form-control form-control-sm" id="quick-search" placeholder="Cari nama, NIP, jabatan...">
                <div id="quick-search-results" class="list-group position-absolute shadow d-none" style="z-index:1050; width:100%; top:38px;"></div>
            </div>

            <div class="dropdown">
                <button class="header-icon-btn position-relative" data-bs-toggle="dropdown" id="btn-notif">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($__belumDibaca > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $__belumDibaca > 9 ? '9+' : $__belumDibaca ?>
                        </span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:300px;">
                    <li class="px-2 pb-2 fw-bold small text-muted">Notifikasi</li>
                    <?php if (empty($__notifTerbaru)): ?>
                        <li><em class="px-2 text-muted small">Belum ada notifikasi</em></li>
                    <?php else: ?>
                        <?php foreach ($__notifTerbaru as $__n): ?>
                            <li class="px-2 py-1 small border-bottom">
                                <?= e($__n['pesan']) ?>
                                <div class="tiny text-muted"><?= format_tanggal_indo($__n['created_at']) ?></div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li><a class="dropdown-item small text-center mt-1" href="<?= config('app.url') ?>/dashboard">Lihat Dashboard &raquo;</a></li>
                </ul>
            </div>

            <div class="dropdown">
                <button class="profile-chip" data-bs-toggle="dropdown">
                    <?php if (!empty($_SESSION['admin_foto'])): ?>
                        <img src="<?= config('app.url') ?>/<?= e($_SESSION['admin_foto']) ?>" alt="Foto profil">
                    <?php else: ?>
                        <span class="avatar-fallback"><i class="fa-solid fa-user"></i></span>
                    <?php endif; ?>
                    <span class="small d-none d-sm-inline"><?= e($_SESSION['admin_nama'] ?? 'Administrator') ?></span>
                    <i class="fa-solid fa-chevron-down tiny"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= config('app.url') ?>/profil"><i class="fa-solid fa-id-badge me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="<?= config('app.url') ?>/logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Keluar</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>
<div class="app-header-accent"></div>

<div class="d-flex align-items-start">

    <!-- ===================== SIDEBAR ===================== -->
    <nav class="sidebar p-3" id="sidebar">
        <ul class="nav flex-column gap-1 flex-grow-1">
            <li class="nav-item">
                <a class="nav-link <?= $__menuAktif('/dashboard') ?>" href="<?= config('app.url') ?>/dashboard">
                    <span class="menu-icon-circle mic-blue"><i class="fa-solid fa-gauge"></i></span> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $__menuAktif('/pengingat') ?> <?= $__menuAktif('/pegawai') ?>" href="<?= config('app.url') ?>/pengingat">
                    <span class="menu-icon-circle mic-blue"><i class="fa-solid fa-users"></i></span> Data Pegawai
                    <i class="fa-solid fa-chevron-right chevron-soon"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $__menuAktif('/unit-kerja') ?>" href="<?= config('app.url') ?>/unit-kerja">
                    <span class="menu-icon-circle mic-orange"><i class="fa-solid fa-building"></i></span> Unit Kerja
                    <i class="fa-solid fa-chevron-right chevron-soon"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $__menuAktif('/kalender') ?>" href="<?= config('app.url') ?>/kalender">
                    <span class="menu-icon-circle mic-teal"><i class="fa-solid fa-calendar-days"></i></span> Riwayat KGB
                    <i class="fa-solid fa-chevron-right chevron-soon"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $__menuAktif('/log-email') ?>" href="<?= config('app.url') ?>/log-email">
                    <span class="menu-icon-circle mic-purple"><i class="fa-solid fa-envelope"></i></span> Notifikasi
                    <i class="fa-solid fa-chevron-right chevron-soon"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $__menuAktif('/laporan') ?>" href="<?= config('app.url') ?>/laporan">
                    <span class="menu-icon-circle mic-teal"><i class="fa-solid fa-chart-column"></i></span> Laporan
                    <i class="fa-solid fa-chevron-right chevron-soon"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $__menuAktif('/backup') ?>" href="<?= config('app.url') ?>/backup">
                    <span class="menu-icon-circle mic-gray"><i class="fa-solid fa-gear"></i></span> Pengaturan
                    <i class="fa-solid fa-chevron-right chevron-soon"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $__menuAktif('/pengguna') ?>" href="<?= config('app.url') ?>/pengguna">
                    <span class="menu-icon-circle mic-gray"><i class="fa-solid fa-user-shield"></i></span> Pengguna
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $__menuAktif('/profil') ?>" href="<?= config('app.url') ?>/profil">
                    <span class="menu-icon-circle mic-blue"><i class="fa-solid fa-id-badge"></i></span> Profil Admin
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= config('app.url') ?>/logout" style="color:var(--bps-red);">
                    <span class="menu-icon-circle mic-red"><i class="fa-solid fa-right-from-bracket"></i></span> Keluar
                </a>
            </li>
        </ul>

        <svg class="sidebar-illustration" width="140" height="70" viewBox="0 0 140 70" xmlns="http://www.w3.org/2000/svg">
            <g fill="#005BAC" opacity="0.12">
                <rect x="5" y="30" width="18" height="40"/>
                <rect x="26" y="15" width="16" height="55"/>
                <rect x="45" y="35" width="20" height="35"/>
                <rect x="68" y="20" width="16" height="50"/>
                <rect x="87" y="40" width="22" height="30"/>
                <rect x="112" y="25" width="18" height="45"/>
            </g>
        </svg>
        <div class="sidebar-slogan">
            &ldquo;Pelayanan Statistik Terintegrasi<br>Untuk Negeri&rdquo;
        </div>
    </nav>

    <!-- ===================== MAIN CONTENT ===================== -->
    <div class="flex-grow-1" style="min-width:0;">
        <main class="p-3 p-md-4">
            <?= $content ?>
        </main>

        <!-- ===================== FOOTER ===================== -->
        <footer class="app-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <img src="<?= config('app.url') ?>/assets/images/logo-bps.png" alt="Logo BPS" style="width:22px;height:22px;object-fit:contain;">
                <span class="fw-semibold">BPS Kota Bandung</span>
            </div>
            <div class="text-center flex-grow-1">
                <div>SI-KGB &middot; BPS Kota Bandung &copy; <?= date('Y') ?></div>
                <div class="footer-slogan">Pelayanan Statistik Terintegrasi Untuk Negeri</div>
            </div>
            <div class="tiny">Versi 1.0</div>
        </footer>
    </div>
</div>

<script src="<?= config('app.url') ?>/assets/js/app.js"></script>
</body>
</html>
