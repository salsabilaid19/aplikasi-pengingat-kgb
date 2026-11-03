<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 fade-in-up">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-id-card me-2" style="color:var(--bps-primary);"></i>Detail Pegawai</h4>
    <div>
        <a href="<?= config('app.url') ?>/pegawai/<?= $pegawai['id'] ?>/edit" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-pen me-1"></i> Edit
        </a>
        <button type="button" class="btn btn-outline-danger btn-sm" title="Hapus (pegawai sudah keluar/pindah)"
            onclick="konfirmasiAksi('Hapus <?= e($pegawai['nama']) ?> dari daftar aktif? Gunakan ini untuk pegawai yang sudah keluar/pindah instansi. Riwayat KGB &amp; email tetap tersimpan untuk arsip.', '<?= config('app.url') ?>/pegawai/<?= $pegawai['id'] ?>/hapus')">
            <i class="fa-solid fa-user-slash me-1"></i> Hapus
        </button>
        <a href="<?= config('app.url') ?>/pengingat" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- ===================== INFORMASI PEGAWAI ===================== -->
    <div class="col-lg-5 fade-in-up">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold border-0 pb-0"><i class="fa-solid fa-circle-info me-2" style="color:var(--bps-primary);"></i>Informasi Pegawai</div>
            <div class="card-body">
                <table class="table table-borderless small mb-0">
                    <tr><td class="text-muted" width="40%">NIP</td><td><?= e($pegawai['nip']) ?></td></tr>
                    <tr><td class="text-muted">Nama</td><td class="fw-semibold"><?= e($pegawai['nama']) ?></td></tr>
                    <tr><td class="text-muted">Jabatan</td><td><?= e($pegawai['jabatan']) ?></td></tr>
                    <tr><td class="text-muted">Unit Kerja</td><td><?= e($pegawai['nama_unit'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted">Golongan</td><td><?= e($pegawai['golongan'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Email</td><td><?= e($pegawai['email'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">No. HP</td><td><?= e($pegawai['no_hp'] ?: '-') ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================== STATUS KGB ===================== -->
    <div class="col-lg-7 fade-in-up">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold border-0 pb-0"><i class="fa-solid fa-hourglass-half me-2" style="color:var(--bps-primary);"></i>Status KGB</div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="small text-muted">KGB Terakhir</div>
                        <div class="fw-semibold"><?= format_tanggal_indo($pegawai['tmt_kgb_terakhir']) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">KGB Berikutnya</div>
                        <div class="fw-semibold"><?= format_tanggal_indo($pegawai['tanggal_kgb_berikutnya']) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Sisa Hari</div>
                        <div class="fw-bold fs-5 text-<?= $status['warna'] ?>"><?= $sisaHari ?> hari</div>
                    </div>
                </div>
                <div class="text-center mb-3">
                    <span class="badge bg-<?= $status['warna'] ?> fs-6"><?= $status['emoji'] ?> <?= e($status['label']) ?></span>
                </div>

                <hr>
                <h6 class="small fw-semibold text-muted"><i class="fa-solid fa-envelope-open-text me-1"></i>RIWAYAT EMAIL</h6>
                <?php if (empty($riwayatEmail)): ?>
                    <p class="text-muted small">Belum ada email yang pernah dikirim.</p>
                <?php else: ?>
                    <ul class="list-unstyled small mb-0">
                        <?php foreach ($riwayatEmail as $log): ?>
                            <li class="d-flex justify-content-between border-bottom py-2">
                                <span>
                                    <?php $badgeEmail = ['Sudah Dikirim' => 'success', 'Gagal' => 'danger', 'Belum Dikirim' => 'secondary']; ?>
                                    <span class="badge bg-<?= $badgeEmail[$log['status']] ?? 'secondary' ?>"><?= e($log['status']) ?></span>
                                    <?php if (!empty($log['error_message'])): ?>
                                        <span class="text-danger tiny d-block"><?= e($log['error_message']) ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="text-muted tiny"><?= $log['waktu_pengiriman'] ? format_tanggal_indo($log['waktu_pengiriman']) : '-' ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
