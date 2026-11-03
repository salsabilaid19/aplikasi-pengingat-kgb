<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk - <?= e(config('app.name')) ?></title>
<link href="<?= config('app.url') ?>/assets/css/vendor/bootstrap.min.css" rel="stylesheet">
<link href="<?= config('app.url') ?>/assets/css/vendor/fontawesome.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= config('app.url') ?>/assets/css/app.css">
</head>
<body>
<div class="row g-0 login-split">
    <!-- ===================== SISI KIRI: VISUAL ===================== -->
    <div class="col-lg-6 login-visual d-none d-lg-flex">
        <div class="content fade-in-up">
            <div class="logo-circle"><img src="<?= config('app.url') ?>/assets/images/logo-bps.png" alt="Logo BPS"></div>
            <h2 class="fw-bold mb-1">Sistem Informasi<br>Pengingat Kenaikan Gaji Berkala</h2>
            <p class="opacity-75 mb-0">Badan Pusat Statistik Kota Bandung</p>
            <hr class="opacity-25 my-4" style="max-width:80px;">
            <p class="small opacity-75 mb-0">
                <i class="fa-solid fa-shield-halved me-1"></i>
                Kelola data KGB pegawai secara terpusat, akurat, dan tepat waktu.
            </p>
        </div>
    </div>

    <!-- ===================== SISI KANAN: FORM LOGIN ===================== -->
    <div class="col-lg-6 login-form-side">
        <div class="login-card card shadow p-4 fade-in-up">
            <div class="text-center mb-3 d-lg-none">
                <div class="logo-circle mx-auto" style="background:var(--bps-light-blue);"><img src="<?= config('app.url') ?>/assets/images/logo-bps.png" alt="Logo BPS"></div>
            </div>
            <div class="text-center mb-4">
                <h5 class="fw-bold mb-0" style="color:var(--bps-primary-dark);">Masuk ke Akun Anda</h5>
                <p class="text-muted small mb-0">Silakan masuk untuk melanjutkan</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small"><i class="fa-solid fa-circle-exclamation me-1"></i><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= config('app.url') ?>/login">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" required autofocus placeholder="nama@instansi.go.id">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                </div>
                <button type="submit" class="btn btn-bps w-100 py-2">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk
                </button>
            </form>

            <p class="text-center text-muted tiny mt-4 mb-0">
                &copy; <?= date('Y') ?> BPS Kota Bandung &middot; Pelayanan Statistik Terintegrasi Untuk Negeri
            </p>
            <p class="text-center tiny mt-2 mb-0">
                <a href="<?= config('app.url') ?>/cek-kgb"><i class="fa-solid fa-magnifying-glass me-1"></i>Pegawai? Cek tanggal KGB Anda di sini</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
