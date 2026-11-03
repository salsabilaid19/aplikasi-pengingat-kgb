<?php
/**
 * services/EmailService.php
 * Membungkus PHPMailer untuk mengirim email pengingat KGB via SMTP Gmail,
 * dan mencatat setiap percobaan (sukses maupun gagal) ke tabel email_log.
 */

require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService
{
    private EmailLogModel $emailLogModel;

    public function __construct()
    {
        $this->emailLogModel = new EmailLogModel();
    }

    /**
     * Kirim satu email pengingat KGB untuk seorang pegawai.
     * Selalu mencatat hasilnya (sukses/gagal) ke email_log, apa pun hasilnya.
     *
     * @param array $pegawai baris dari tabel pegawai (idealnya hasil PegawaiModel::findWithUnit)
     * @return bool true jika terkirim, false jika gagal
     */
    public function kirimPengingatKgb(array $pegawai): bool
    {
        if (empty($pegawai['email'])) {
            $this->emailLogModel->catatGagal(
                (int) $pegawai['id'],
                $pegawai['nama'],
                '(tidak ada alamat email)',
                $pegawai['tanggal_kgb_berikutnya'],
                'Pegawai tidak memiliki alamat email yang terdaftar.'
            );
            return false;
        }

        $mail = $this->buatKoneksiMailer();
        $sisaHari = hitung_sisa_hari($pegawai['tanggal_kgb_berikutnya']);

        try {
            $mail->addAddress($pegawai['email'], $pegawai['nama']);
            $mail->Subject = 'Pengingat Kenaikan Gaji Berkala (KGB) - ' . $pegawai['nama'];
            $mail->Body = $this->buatIsiEmail($pegawai, $sisaHari);
            $mail->AltBody = strip_tags($this->buatIsiEmail($pegawai, $sisaHari));

            $mail->send();

            $this->emailLogModel->catatSukses(
                (int) $pegawai['id'],
                $pegawai['nama'],
                $pegawai['email'],
                $pegawai['tanggal_kgb_berikutnya']
            );
            return true;
        } catch (PHPMailerException $e) {
            $this->emailLogModel->catatGagal(
                (int) $pegawai['id'],
                $pegawai['nama'],
                $pegawai['email'],
                $pegawai['tanggal_kgb_berikutnya'],
                $mail->ErrorInfo ?: $e->getMessage()
            );
            return false;
        }
    }

    /** Siapkan objek PHPMailer dengan konfigurasi SMTP dari .env */
    private function buatKoneksiMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
        $mail->Port = (int) env('MAIL_PORT', 587);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Timeout = 8;
        $mail->SMTPKeepAlive = false;
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', config('app.name')));

        // Simpan detail SMTP ke log aplikasi (bukan ke user) jika gagal, untuk debugging admin
        $mail->SMTPDebug = 0;

        return $mail;
    }

    /** Susun isi email HTML pengingat KGB */
    private function buatIsiEmail(array $pegawai, int $sisaHari): string
    {
        $tglKgb = format_tanggal_indo($pegawai['tanggal_kgb_berikutnya']);
        $namaApp = e(config('app.name'));

        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; color: #1a1a1a;">
            <div style="background: #0d3b7a; color: #fff; padding: 16px 24px; border-radius: 8px 8px 0 0;">
                <h2 style="margin:0; font-size: 18px;">{$namaApp}</h2>
            </div>
            <div style="border: 1px solid #e5e5e5; border-top: none; padding: 24px; border-radius: 0 0 8px 8px;">
                <p>Yth. Bapak/Ibu <strong>{$pegawai['nama']}</strong>,</p>
                <p>Kami informasikan bahwa Kenaikan Gaji Berkala (KGB) Bapak/Ibu di bulan ini pada tanggal:</p>
                <table style="width:100%; border-collapse: collapse; margin: 16px 0;">
                    <tr>
                        <td style="padding:8px 0; color:#666;">Tanggal KGB</td>
                        <td style="padding:8px 0; font-weight:bold;">{$tglKgb}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:#666;">Sisa Hari</td>
                        <td style="padding:8px 0; font-weight:bold;">{$sisaHari} hari</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:#666;">Jabatan</td>
                        <td style="padding:8px 0;">{$pegawai['jabatan']}</td>
                    </tr>
                </table>
                <p style="margin-top:24px; color:#666; font-size:13px;">
                    Email ini dikirim otomatis oleh sistem {$namaApp}. Mohon tidak membalas email ini.
                </p>
            </div>
        </div>
        HTML;
    }
}
