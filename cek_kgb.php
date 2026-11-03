<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cek Tanggal KGB - <?= e(config('app.name')) ?></title>
<link href="<?= config('app.url') ?>/assets/css/vendor/bootstrap.min.css" rel="stylesheet">
<link href="<?= config('app.url') ?>/assets/css/vendor/fontawesome.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= config('app.url') ?>/assets/css/app.css">
</head>
<body>
<div class="row g-0 login-split">
    <div class="col-lg-5 login-visual d-none d-lg-flex">
        <div class="content fade-in-up">
            <div class="logo-circle"><img src="<?= config('app.url') ?>/assets/images/logo-bps.png" alt="Logo BPS"></div>
            <h2 class="fw-bold mb-1">Cek Tanggal KGB Anda</h2>
            <p class="opacity-75 mb-0">Badan Pusat Statistik Kota Bandung</p>
            <hr class="opacity-25 my-4" style="max-width:80px;">
            <p class="small opacity-75 mb-0">
                <i class="fa-solid fa-shield-halved me-1"></i>
                Masukkan NIP Anda untuk melihat status Kenaikan Gaji Berkala tanpa perlu login sebagai admin.
            </p>
        </div>
    </div>

    <div class="col-lg-7 login-form-side">
        <div class="login-card card shadow p-4 fade-in-up" style="max-width:520px;">
            <div class="text-center mb-3 d-lg-none">
                <div class="logo-circle mx-auto" style="background:var(--bps-light-blue);"><img src="<?= config('app.url') ?>/assets/images/logo-bps.png" alt="Logo BPS"></div>
            </div>
            <div class="text-center mb-4">
                <h5 class="fw-bold mb-0" style="color:var(--bps-primary-dark);">Cek Tanggal KGB</h5>
                <p class="text-muted small mb-0">Masukkan NIP Anda, tanpa perlu akun/login</p>
            </div>

            <form method="GET" action="<?= config('app.url') ?>/cek-kgb/cari" class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                    <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP" required
                           value="<?= e($nipDicari) ?>" autofocus>
                    <button type="submit" class="btn btn-bps"><i class="fa-solid fa-magnifying-glass me-1"></i> Cek</button>
                </div>
            </form>

            <?php if (!empty($error)): ?>
                <div class="alert alert-warning py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i><?= e($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($hasil)): ?>
                <div class="card border" style="border-radius:14px;">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1"><?= e($hasil['nama']) ?></h6>
                        <p class="text-muted small mb-3"><?= e($hasil['jabatan']) ?> &middot; <?= e($hasil['nama_unit'] ?? '-') ?></p>

                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="tiny text-muted">KGB Terakhir</div>
                                <div class="fw-semibold small"><?= format_tanggal_indo($hasil['tmt_terakhir']) ?></div>
                            </div>
                            <div class="col-4">
                                <div class="tiny text-muted">KGB Berikutnya</div>
                                <div class="fw-semibold small"><?= format_tanggal_indo($hasil['tanggal_kgb']) ?></div>
                            </div>
                            <div class="col-4">
                                <div class="tiny text-muted">Sisa Hari</div>
                                <div class="fw-bold text-<?= $hasil['status']['warna'] ?>"><?= $hasil['sisa_hari'] ?> hari</div>
                            </div>
                        </div>

                        <div class="text-center">
                            <span class="badge bg-<?= $hasil['status']['warna'] ?> fs-6">
                                <?= $hasil['status']['emoji'] ?> <?= e($hasil['status']['label']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <p class="text-center text-muted tiny mt-4 mb-0">
                Admin instansi? <a href="<?= config('app.url') ?>/login">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
