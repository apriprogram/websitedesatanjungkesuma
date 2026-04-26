<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DusunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        $dusunId = $this->route('dusun')?->id ?? null;

        return [
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('dusuns', 'nama')->ignore($dusunId),
            ],
            'kode' => ['nullable', 'string', 'max:20'],
        ];
    }
}
