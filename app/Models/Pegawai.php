<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasProfileImage;

class Pegawai extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasProfileImage;

    protected $fillable = [
        'nama',
        'nik',
        'nip',
        'jabatan',
        'email',
        'nomor_hp',
        'status',
        'gambar',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'foto_ktp',
        'sk_pengangkatan',
        'sk_pemberhentian',
        'masa_jabatan_mulai',
        'masa_jabatan_selesai',
        'universitas',
        'pendidikan_terakhir',
        'tahun_lulus',
        'sertifikat_pelatihan',
        'bahasa',
        'last_login',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'masa_jabatan_mulai' => 'date',
        'masa_jabatan_selesai' => 'date',
        'last_login' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get image folder for pegawai
     */
    protected function getImageFolder(): string
    {
        return 'pegawai/foto-profil';
    }

    /**
     * Pastikan path gambar pegawai selalu mengarah ke folder pegawai.
     */
    public function getGambarAttribute($value): ?string
    {
        if (!$value) return null;
        return str_starts_with($value, 'pegawai/') ? $value : "pegawai/{$value}";
    }
}
