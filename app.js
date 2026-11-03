document.addEventListener('DOMContentLoaded', function () {
    // Toggle sidebar di layar kecil
    const btnToggle = document.getElementById('btn-toggle-sidebar');
    const sidebar = document.getElementById('sidebar');
    if (btnToggle && sidebar) {
        btnToggle.addEventListener('click', () => sidebar.classList.toggle('show'));
    }

    // Pencarian cepat (debounced AJAX ke /dashboard/search)
    const input = document.getElementById('quick-search');
    const resultsBox = document.getElementById('quick-search-results');
    let debounceTimer;

    if (input && resultsBox) {
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const keyword = this.value.trim();

            if (keyword.length < 2) {
                resultsBox.classList.add('d-none');
                resultsBox.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`${APP_URL}/dashboard/search?q=${encodeURIComponent(keyword)}`)
                    .then(res => res.json())
                    .then(data => renderResults(data.results || []))
                    .catch(() => resultsBox.classList.add('d-none'));
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!resultsBox.contains(e.target) && e.target !== input) {
                resultsBox.classList.add('d-none');
            }
        });
    }

    function renderResults(results) {
        if (results.length === 0) {
            resultsBox.innerHTML = '<div class="list-group-item text-muted small">Tidak ada hasil</div>';
            resultsBox.classList.remove('d-none');
            return;
        }

        resultsBox.innerHTML = results.map(r => `
            <a href="${r.url}" class="list-group-item list-group-item-action">
                <div class="d-flex justify-content-between">
                    <span class="fw-semibold">${r.nama}</span>
                    <span class="badge bg-${r.warna}">${r.status}</span>
                </div>
                <div class="tiny text-muted">${r.nip} &middot; ${r.jabatan} &middot; ${r.unit_kerja}</div>
            </a>
        `).join('');
        resultsBox.classList.remove('d-none');
    }

    // Tandai notifikasi sudah dibaca saat lonceng diklik
    const btnNotif = document.getElementById('btn-notif');
    if (btnNotif) {
        btnNotif.addEventListener('click', function () {
            fetch(`${APP_URL}/dashboard/baca-notifikasi`).then(() => {
                // Hilangkan badge angka secara langsung tanpa perlu reload halaman
                const badge = btnNotif.querySelector('.badge');
                if (badge) badge.remove();
            });
        }, { once: true });
    }
});

/** Konfirmasi sebelum aksi berbahaya (dipakai tombol hapus/kirim ulang) via SweetAlert2 */
function konfirmasiAksi(pesan, url) {
    Swal.fire({
        title: 'Konfirmasi',
        text: pesan,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d3b7a',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
