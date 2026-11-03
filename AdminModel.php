<?php
/**
 * models/AdminModel.php
 * Model untuk tabel admins: autentikasi dan pengelolaan profil admin.
 */
class AdminModel extends Model
{
    protected string $table = 'admins';

    public function findByEmail(string $email): ?array
    {
        return Database::selectOne('SELECT * FROM admins WHERE email = ?', [$email]);
    }

    /**
     * Verifikasi kredensial login. Kembalikan data admin jika cocok, null jika tidak.
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        $admin = $this->findByEmail($email);
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return null;
    }

    /** Ganti password admin, otomatis di-hash */
    public function updatePassword(int $id, string $newPassword): int
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->update($id, ['password' => $hash]);
    }

    public function updateProfile(int $id, string $nama, string $email, ?string $fotoPath = null): int
    {
        $data = ['nama' => $nama, 'email' => $email];
        if ($fotoPath !== null) {
            $data['foto_profil'] = $fotoPath;
        }
        return $this->update($id, $data);
    }

    /** Daftar seluruh admin, terbaru dulu (untuk halaman manajemen Pengguna) */
    public function semuaAdmin(): array
    {
        return Database::select('SELECT id, nama, email, foto_profil, created_at FROM admins ORDER BY created_at ASC');
    }

    public function jumlahAdmin(): int
    {
        $row = Database::selectOne('SELECT COUNT(*) AS total FROM admins');
        return (int) ($row['total'] ?? 0);
    }

    public function emailSudahDipakai(string $email, ?int $kecualiId = null): bool
    {
        if ($kecualiId !== null) {
            $row = Database::selectOne('SELECT id FROM admins WHERE email = ? AND id != ?', [$email, $kecualiId]);
        } else {
            $row = Database::selectOne('SELECT id FROM admins WHERE email = ?', [$email]);
        }
        return $row !== null;
    }

    /** Buat admin baru dengan password langsung di-hash */
    public function buatAdminBaru(string $nama, string $email, string $password): int
    {
        return $this->insert([
            'nama'     => $nama,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ]);
    }
}
