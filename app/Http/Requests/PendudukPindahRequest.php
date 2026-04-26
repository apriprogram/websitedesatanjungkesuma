<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PendudukPindahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        return [
            'penduduk_id' => ['required', 'exists:penduduks,id'],
            'tanggal_pindah' => ['required', 'date'],
            'alasan_pindah' => ['nullable', 'string'],
            'alamat_tujuan' => ['nullable', 'string'],
            'desa_tujuan' => ['nullable', 'string', 'max:100'],
            'kecamatan_tujuan' => ['nullable', 'string', 'max:100'],
            'kabupaten_tujuan' => ['nullable', 'string', 'max:100'],
            'provinsi_tujuan' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
