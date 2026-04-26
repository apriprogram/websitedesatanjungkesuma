<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KeluargaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        $keluarga = $this->route('keluarga');
        $ignoreId = $keluarga?->id;

        return [
            'no_kk' => [
                'required',
                'string',
                'max:30',
                Rule::unique('keluargas', 'no_kk')->ignore($ignoreId),
            ],
            'kepala_nik' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'dusun_id' => ['nullable', 'exists:dusuns,id'],
            'rw_id' => ['nullable', 'exists:rws,id'],
            'rt_id' => ['nullable', 'exists:rts,id'],
        ];
    }
}
