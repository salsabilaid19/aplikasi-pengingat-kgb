<?php $isEdit = $unit !== null; ?>

<h4 class="fw-bold mb-3 fade-in-up">
    <i class="fa-solid fa-building me-2" style="color:var(--bps-primary);"></i>
    <?= $isEdit ? 'Edit Unit Kerja' : 'Tambah Unit Kerja' ?>
</h4>

<?php if (!empty($error)): ?><div class="alert alert-danger py-2 small"><i class="fa-solid fa-circle-exclamation me-1"></i><?= e($error) ?></div><?php endif; ?>

<div class="card fade-in-up" style="max-width:520px;">
    <div class="card-body">
        <form method="POST" action="<?= config('app.url') ?>/<?= $isEdit ? 'unit-kerja/' . $unit['id'] . '/perbarui' : 'unit-kerja/simpan' ?>">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Nama Unit Kerja</label>
                <input type="text" name="nama_unit" class="form-control" required autofocus
                       value="<?= e($unit['nama_unit'] ?? '') ?>" placeholder="mis. Seksi Statistik Sosial">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-bps"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan</button>
                <a href="<?= config('app.url') ?>/unit-kerja" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
