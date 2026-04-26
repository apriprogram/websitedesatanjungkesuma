<?php

namespace App\Models;

use App\Models\References\RefAgama;
use App\Models\References\RefCaraKb;
use App\Models\References\RefCacat;
use App\Models\References\RefGolonganDarah;
use App\Models\References\RefHubKeluarga;
use App\Models\References\RefJenisKelamin;
use App\Models\References\RefPekerjaan;
use App\Models\References\RefPendidikan;
use App\Models\References\RefStatusDasar;
use App\Models\References\RefStatusKawin;
use App\Models\References\RefStatusRekam;
use App\Models\References\RefSuku;
use App\Models\References\RefWarganegara;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penduduk extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_akhir_paspor' => 'date',
        'tanggal_perkawinan' => 'date',
        'tanggal_perceraian' => 'date',
        'hamil' => 'boolean',
        'ktp_el' => 'boolean',
    ];

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class, 'no_kk', 'no_kk');
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class);
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function jenisKelamin(): BelongsTo
    {
        return $this->belongsTo(RefJenisKelamin::class, 'jenis_kelamin_id');
    }

    public function agama(): BelongsTo
    {
        return $this->belongsTo(RefAgama::class, 'agama_id');
    }

    public function pendidikanKk(): BelongsTo
    {
        return $this->belongsTo(RefPendidikan::class, 'pendidikan_kk_id');
    }

    public function pendidikanSedang(): BelongsTo
    {
        return $this->belongsTo(RefPendidikan::class, 'pendidikan_sedang_id');
    }

    public function pekerjaan(): BelongsTo
    {
        return $this->belongsTo(RefPekerjaan::class, 'pekerjaan_id');
    }

    public function statusKawin(): BelongsTo
    {
        return $this->belongsTo(RefStatusKawin::class, 'status_kawin_id');
    }

    public function kkLevel(): BelongsTo
    {
        return $this->belongsTo(RefHubKeluarga::class, 'kk_level_id');
    }

    public function warganegara(): BelongsTo
    {
        return $this->belongsTo(RefWarganegara::class, 'warganegara_id');
    }

    public function golonganDarah(): BelongsTo
    {
        return $this->belongsTo(RefGolonganDarah::class, 'golongan_darah_id');
    }

    public function cacat(): BelongsTo
    {
        return $this->belongsTo(RefCacat::class, 'cacat_id');
    }

    public function caraKb(): BelongsTo
    {
        return $this->belongsTo(RefCaraKb::class, 'cara_kb_id');
    }

    public function statusRekam(): BelongsTo
    {
        return $this->belongsTo(RefStatusRekam::class, 'status_rekam_id');
    }

    public function statusDasar(): BelongsTo
    {
        return $this->belongsTo(RefStatusDasar::class, 'status_dasar_id');
    }

    public function suku(): BelongsTo
    {
        return $this->belongsTo(RefSuku::class, 'suku_id');
    }

    public function pendudukPindahs(): HasMany
    {
        return $this->hasMany(PendudukPindah::class);
    }

    public function pendudukMeninggals(): HasMany
    {
        return $this->hasMany(PendudukMeninggal::class);
    }

    /**
     * Pastikan path foto profil selalu mengarah ke folder penduduk.
     */
    public function getFotoProfilAttribute($value): ?string
    {
        if (!$value) return null;
        return str_starts_with($value, 'penduduk/') ? $value : "penduduk/{$value}";
    }

    /**
     * Pastikan path foto KTP selalu mengarah ke folder penduduk.
     */
    public function getFotoKtpAttribute($value): ?string
    {
        if (!$value) return null;
        return str_starts_with($value, 'penduduk/') ? $value : "penduduk/{$value}";
    }

    /**
     * Pastikan path foto KK selalu mengarah ke folder penduduk.
     */
    public function getFotoKkAttribute($value): ?string
    {
        if (!$value) return null;
        return str_starts_with($value, 'penduduk/') ? $value : "penduduk/{$value}";
    }
}
