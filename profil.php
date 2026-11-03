<h4 class="fw-bold mb-3">Profil Admin</h4>

<?php if (!empty($success)): ?><div class="alert alert-success py-2 small"><?= e($success) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger py-2 small"><?= e($error) ?></div><?php endif; ?>

<div class="card border-0" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="<?= config('app.url') ?>/profil/perbarui" enctype="multipart/form-data">
            <div class="text-center mb-3">
                <?php if (!empty($admin['foto_profil'])): ?>
                    <img src="<?= config('app.url') ?>/<?= e($admin['foto_profil']) ?>" class="rounded-circle" width="90" height="90" style="object-fit:cover">
                <?php else: ?>
                    <i class="fa-solid fa-user" style="font-size:90px; color:#ccc"></i>
                <?php endif; ?>
                <div class="mt-2">
                    <input type="file" name="foto_profil" accept="image/png, image/jpeg" class="form-control form-control-sm">
                    <div class="form-text tiny">JPG/PNG, maksimal 2MB.</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Nama</label>
                <input type="text" name="nama" class="form-control" required value="<?= e($admin['nama']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= e($admin['email']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Password Baru</label>
                <input type="password" name="password_baru" class="form-control" placeholder="Kosongkan jika tidak diganti" minlength="8">
                <div class="form-text tiny">Minimal 8 karakter. Disimpan dengan hashing bcrypt.</div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan</button>
        </form>
    </div>
</div>
