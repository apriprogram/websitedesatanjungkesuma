<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PendudukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    protected function prepareForValidation(): void
    {
        $customJob = trim((string) $this->input('pekerjaan_custom'));

        $payload = [
            'hamil' => $this->input('hamil') === '' ? null : $this->input('hamil'),
            'pekerjaan_custom' => $customJob === '' ? null : $customJob,
            'remove_foto_profil' => $this->boolean('remove_foto_profil'),
            'remove_foto_ktp' => $this->boolean('remove_foto_ktp'),
            'remove_foto_kk' => $this->boolean('remove_foto_kk'),
        ];

        $nullableReferences = [
            'agama_id',
            'pendidikan_kk_id',
            'pendidikan_sedang_id',
            'pekerjaan_id',
            'status_kawin_id',
            'kk_level_id',
            'warganegara_id',
            'golongan_darah_id',
            'cacat_id',
            'cara_kb_id',
            'status_rekam_id',
            'dusun_id',
            'rw_id',
            'rt_id',
            'status_dasar_id',
            'suku_id',
        ];

        foreach ($nullableReferences as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $payload[$field] = null;
            }
        }

        $this->merge($payload);
    }

    public function messages(): array
    {
        return [
            'no_kk.required' => 'Nomor KK wajib diisi. Gunakan nomor KK yang sudah terdaftar.',
            'no_kk.exists' => 'Nomor KK tidak ditemukan di data keluarga.',
            'nik.required' => 'NIK wajib diisi. Pastikan sesuai dengan KTP.',
            'nik.unique' => 'NIK sudah terdaftar pada penduduk lain.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin_id.required' => 'Pilih salah satu jenis kelamin yang tersedia.',
            'jenis_kelamin_id.exists' => 'Pilihan jenis kelamin tidak valid.',
            'dusun_id.exists' => 'Dusun yang dipilih tidak dikenal.',
            'rw_id.exists' => 'RW yang dipilih tidak dikenal.',
            'rt_id.exists' => 'RT yang dipilih tidak dikenal.',
            'status_dasar_id.exists' => 'Status dasar yang dipilih tidak dikenal.',
            'agama_id.exists' => 'Agama yang dipilih tidak dikenal.',
            'pendidikan_kk_id.exists' => 'Pilihan pendidikan (dalam KK) tidak dikenal.',
            'pendidikan_sedang_id.exists' => 'Pilihan pendidikan aktif tidak dikenal.',
            'pekerjaan_id.exists' => 'Pekerjaan yang dipilih tidak dikenal.',
            'status_kawin_id.exists' => 'Status perkawinan yang dipilih tidak dikenal.',
            'warganegara_id.exists' => 'Kewarganegaraan yang dipilih tidak dikenal.',
            'nomor_hp.max' => 'Nomor HP tidak boleh lebih dari 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 100 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'no_kk' => 'Nomor KK',
            'nik' => 'NIK',
            'nama' => 'Nama lengkap',
            'jenis_kelamin_id' => 'Jenis kelamin',
            'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir',
            'agama_id' => 'Agama',
            'pendidikan_kk_id' => 'Pendidikan (dalam KK)',
            'pendidikan_sedang_id' => 'Pendidikan aktif',
            'pekerjaan_id' => 'Pekerjaan',
            'pekerjaan_custom' => 'Pekerjaan lainnya',
            'status_kawin_id' => 'Status perkawinan',
            'kk_level_id' => 'Hubungan dalam KK',
            'warganegara_id' => 'Kewarganegaraan',
            'dusun_id' => 'Dusun',
            'rw_id' => 'RW',
            'rt_id' => 'RT',
            'status_dasar_id' => 'Status dasar',
            'golongan_darah_id' => 'Golongan darah',
            'suku_id' => 'Suku / etnis',
            'nomor_hp' => 'Nomor HP',
            'email' => 'Email',
            'foto_profil' => 'foto profil',
            'foto_ktp' => 'foto KTP',
            'foto_kk' => 'foto KK',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.penduduks.index');
    }

    public function rules(): array
    {
        $penduduk = $this->route('penduduk');
        $ignoreId = $penduduk?->id;

        return [
            'no_kk' => ['required', 'string', 'max:30', 'exists:keluargas,no_kk'],
            'nik' => [
                'required',
                'string',
                'max:20',
                Rule::unique('penduduks', 'nik')->ignore($ignoreId),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'jenis_kelamin_id' => ['required', 'exists:ref_jenis_kelamin,id'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama_id' => ['nullable', 'exists:ref_agama,id'],
            'pendidikan_kk_id' => ['nullable', 'exists:ref_pendidikan,id'],
            'pendidikan_sedang_id' => ['nullable', 'exists:ref_pendidikan,id'],
            'pekerjaan_id' => ['nullable', 'exists:ref_pekerjaan,id'],
            'pekerjaan_custom' => ['nullable', 'string', 'max:100'],
            'status_kawin_id' => ['nullable', 'exists:ref_status_kawin,id'],
            'kk_level_id' => ['nullable', 'exists:ref_hub_keluarga,id'],
            'warganegara_id' => ['nullable', 'exists:ref_warganegara,id'],
            'ayah_nik' => ['nullable', 'string', 'max:20'],
            'nama_ayah' => ['nullable', 'string', 'max:100'],
            'ibu_nik' => ['nullable', 'string', 'max:20'],
            'nama_ibu' => ['nullable', 'string', 'max:100'],
            'golongan_darah_id' => ['nullable', 'exists:ref_golongan_darah,id'],
            'akta_lahir' => ['nullable', 'string', 'max:100'],
            'dokumen_pasport' => ['nullable', 'string', 'max:100'],
            'tanggal_akhir_paspor' => ['nullable', 'date'],
            'dokumen_kitas' => ['nullable', 'string', 'max:100'],
            'akta_perkawinan' => ['nullable', 'string', 'max:100'],
            'tanggal_perkawinan' => ['nullable', 'date'],
            'akta_perceraian' => ['nullable', 'string', 'max:100'],
            'tanggal_perceraian' => ['nullable', 'date'],
            'cacat_id' => ['nullable', 'exists:ref_cacat,id'],
            'cara_kb_id' => ['nullable', 'exists:ref_cara_kb,id'],
            'hamil' => ['nullable', 'boolean'],
            'ktp_el' => ['nullable', 'boolean'],
            'status_rekam_id' => ['nullable', 'exists:ref_status_rekam,id'],
            'alamat' => ['nullable', 'string'],
            'alamat_sekarang' => ['nullable', 'string'],
            'dusun_id' => ['nullable', 'exists:dusuns,id'],
            'rw_id' => ['nullable', 'exists:rws,id'],
            'rt_id' => ['nullable', 'exists:rts,id'],
            'status_dasar_id' => ['nullable', 'exists:ref_status_dasar,id'],
            'suku_id' => ['nullable', 'exists:ref_suku,id'],
            'tag_id_card' => ['nullable', 'string', 'max:100'],
            'id_asuransi' => ['nullable', 'string', 'max:100'],
            'no_asuransi' => ['nullable', 'string', 'max:100'],
            'foto_profil' => ['nullable', 'image', 'max:5120'],
            'foto_ktp' => ['nullable', 'image', 'max:5120'],
            'foto_kk' => ['nullable', 'image', 'max:5120'],
            'nomor_hp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'remove_foto_profil' => ['sometimes', 'boolean'],
            'remove_foto_ktp' => ['sometimes', 'boolean'],
            'remove_foto_kk' => ['sometimes', 'boolean'],
        ];
    }
}
