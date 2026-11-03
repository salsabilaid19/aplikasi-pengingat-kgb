<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-0"><?= e($label) ?></h5>
        <p class="text-muted small mb-0"><?= count($pegawai) ?> pegawai ditemukan</p>
    </div>
    <a href="<?= config('app.url') ?>/dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Unit Kerja</th><th>Tanggal KGB</th><th>Sisa Hari</th><th></th></tr>
            </thead>
            <tbody>
            <?php if (empty($pegawai)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data.</td></tr>
            <?php endif; ?>
            <?php foreach ($pegawai as $p): $s = hitung_sisa_hari($p['tanggal_kgb_berikutnya']); ?>
                <tr>
                    <td><?= e($p['nip']) ?></td>
                    <td><?= e($p['nama']) ?></td>
                    <td><?= e($p['jabatan']) ?></td>
                    <td><?= e($p['nama_unit'] ?? '-') ?></td>
                    <td><?= format_tanggal_indo($p['tanggal_kgb_berikutnya']) ?></td>
                    <td><?= $s ?> hari</td>
                    <td><a href="<?= config('app.url') ?>/pegawai/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
