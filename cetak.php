<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= e($judul) ?></title>
<link href="<?= config('app.url') ?>/assets/css/vendor/bootstrap.min.css" rel="stylesheet">
<style>
    body { padding: 24px; font-size: 13px; }
    .kop { border-bottom: 3px solid #0d3b7a; padding-bottom: 12px; margin-bottom: 20px; display:flex; align-items:center; gap:12px; }
    .kop h1 { font-size: 16px; margin:0; color:#0d3b7a; }
    .kop p { margin:0; font-size:11px; color:#666; }
    table { font-size: 12px; }
    @media print {
        .no-print { display: none !important; }
        body { padding: 0; }
    }
</style>
</head>
<body>

<div class="no-print mb-3 text-end">
    <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak Sekarang</button>
</div>

<div class="kop">
    <div>
        <h1><?= e(config('app.name')) ?></h1>
        <p>Dicetak pada <?= format_tanggal_indo(date('Y-m-d')) ?> pukul <?= date('H:i') ?> WIB</p>
    </div>
</div>

<h5 class="fw-bold mb-3"><?= e($judul) ?></h5>

<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th style="width:30px;">No</th>
            <?php foreach ($kolom as $k): ?>
                <th><?= e($k) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($data)): ?>
            <tr><td colspan="<?= count($kolom) + 1 ?>" class="text-center text-muted py-3">Tidak ada data untuk laporan ini.</td></tr>
        <?php endif; ?>
        <?php foreach ($data as $i => $baris): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <?php foreach ($baris as $nilai): ?>
                    <td><?= e((string) $nilai) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="text-muted small mt-4">Total data: <?= count($data) ?> baris.</p>

<?php if (!empty($modePdf)): ?>
<script>window.onload = () => window.print();</script>
<?php endif; ?>
</body>
</html>
