<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PendudukMeninggalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        return [
            'penduduk_id' => ['required', 'exists:penduduks,id'],
            'tanggal_meninggal' => ['required', 'date'],
            'penyebab' => ['nullable', 'string', 'max:150'],
            'tempat_meninggal' => ['nullable', 'string', 'max:150'],
            'akta_meninggal_no' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ];
    }
}
