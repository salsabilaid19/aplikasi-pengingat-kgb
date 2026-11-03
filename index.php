<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 fade-in-up">
    <div>
        <h4 class="fw-bold mb-0"><i class="fa-solid fa-building me-2" style="color:var(--bps-primary);"></i>Unit Kerja</h4>
        <p class="text-muted small mb-0">Daftar unit kerja/bidang di lingkungan instansi</p>
    </div>
    <a href="<?= config('app.url') ?>/unit-kerja/tambah" class="btn btn-bps btn-sm">
        <i class="fa-solid fa-plus me-1"></i> Tambah Unit Kerja
    </a>
</div>

<?php if (!empty($success)): ?><div class="alert alert-success py-2 small"><i class="fa-solid fa-circle-check me-1"></i><?= e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-warning py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i><?= e($error) ?></div><?php endif; ?>

<div class="card fade-in-up">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th style="width:50px;">No</th><th>Nama Unit Kerja</th><th>Jumlah Pegawai</th><th style="width:140px;">Aksi</th></tr>
                </thead>
                <tbody>
                <?php if (empty($daftar)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada unit kerja. Klik "Tambah Unit Kerja" untuk membuat yang pertama.</td></tr>
                <?php endif; ?>
                <?php foreach ($daftar as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td class="fw-semibold"><?= e($u['nama_unit']) ?></td>
                        <td><span class="badge bg-primary-subtle text-primary-emphasis"><?= $jumlahPerUnit[$u['id']] ?? 0 ?> pegawai</span></td>
                        <td class="text-nowrap">
                            <a href="<?= config('app.url') ?>/unit-kerja/<?= $u['id'] ?>/edit" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                onclick="konfirmasiAksi('Hapus unit kerja &quot;<?= e($u['nama_unit']) ?>&quot;? Pegawai yang terkait tidak akan ikut terhapus, hanya unit kerjanya dikosongkan.', '<?= config('app.url') ?>/unit-kerja/<?= $u['id'] ?>/hapus')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
