<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Define readable labels and groupings for model attributes.
     */
    protected static $fieldMap = [
        // Identitas & Dasar
        'nik' => ['label' => 'NIK', 'section' => 'Informasi Dasar'],
        'nama' => ['label' => 'Nama Lengkap', 'section' => 'Informasi Dasar'],
        'tempat_lahir' => ['label' => 'Tempat Lahir', 'section' => 'Informasi Dasar'],
        'tanggal_lahir' => ['label' => 'Tanggal Lahir', 'section' => 'Informasi Dasar'],
        'jenis_kelamin_id' => ['label' => 'Jenis Kelamin', 'section' => 'Informasi Dasar'],
        'agama_id' => ['label' => 'Agama', 'section' => 'Informasi Dasar'],
        'golongan_darah_id' => ['label' => 'Golongan Darah', 'section' => 'Informasi Dasar'],
        'suku_id' => ['label' => 'Suku/Etnis', 'section' => 'Informasi Dasar'],

        // Alamat & Wilayah
        'alamat' => ['label' => 'Alamat KTP', 'section' => 'Wilayah & Alamat'],
        'alamat_sekarang' => ['label' => 'Alamat Domisili', 'section' => 'Wilayah & Alamat'],
        'dusun_id' => ['label' => 'Dusun', 'section' => 'Wilayah & Alamat'],
        'rw_id' => ['label' => 'RW', 'section' => 'Wilayah & Alamat'],
        'rt_id' => ['label' => 'RT', 'section' => 'Wilayah & Alamat'],

        // Keluarga & Hubungan
        'no_kk' => ['label' => 'Nomor KK', 'section' => 'Data Keluarga'],
        'kk_level_id' => ['label' => 'Hubungan Keluarga', 'section' => 'Data Keluarga'],
        'ayah_nik' => ['label' => 'NIK Ayah', 'section' => 'Data Keluarga'],
        'nama_ayah' => ['label' => 'Nama Ayah', 'section' => 'Data Keluarga'],
        'ibu_nik' => ['label' => 'NIK Ibu', 'section' => 'Data Keluarga'],
        'nama_ibu' => ['label' => 'Nama Ibu', 'section' => 'Data Keluarga'],

        // Pendidikan & Pekerjaan
        'pendidikan_kk_id' => ['label' => 'Pendidikan di KK', 'section' => 'Pendidikan & Ekonomi'],
        'pendidikan_sedang_id' => ['label' => 'Pendidikan Sedang Ditempuh', 'section' => 'Pendidikan & Ekonomi'],
        'pekerjaan_id' => ['label' => 'Pekerjaan', 'section' => 'Pendidikan & Ekonomi'],

        // Status & Kesehatan
        'status_kawin_id' => ['label' => 'Status Perkawinan', 'section' => 'Status & Lainnya'],
        'warganegara_id' => ['label' => 'Kewarganegaraan', 'section' => 'Status & Lainnya'],
        'status_rekam_id' => ['label' => 'Status Rekam', 'section' => 'Status & Lainnya'],
        'status_dasar_id' => ['label' => 'Status Dasar', 'section' => 'Status & Lainnya'],
        'ktp_el' => ['label' => 'KTP-el', 'section' => 'Status & Lainnya'],
        'hamil' => ['label' => 'Status Hamil', 'section' => 'Status & Lainnya'],
        'cacat_id' => ['label' => 'Jenis Cacat', 'section' => 'Kesehatan'],
        'cara_kb_id' => ['label' => 'Metode KB', 'section' => 'Kesehatan'],
    ];

    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $meta = []): void
    {
        /** @var Authenticatable|null $user */
        $user = Auth::user();

        // Auto-resolve changes if provided in meta
        if (isset($meta['changes']) && is_array($meta['changes'])) {
            $formattedChanges = [];
            foreach ($meta['changes'] as $key => $value) {
                if (isset(self::$fieldMap[$key])) {
                    $formattedChanges[$key] = [
                        'label' => self::$fieldMap[$key]['label'],
                        'section' => self::$fieldMap[$key]['section'],
                        'new' => $value,
                    ];
                }
            }
            $meta['detailed_changes'] = $formattedChanges;
        }

        ActivityLog::create([
            'user_id' => $user?->getAuthIdentifier(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'meta' => $meta ?: null,
        ]);
    }
}

