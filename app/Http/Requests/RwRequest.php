<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RwRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        $rw = $this->route('rw');
        $rwId = $rw?->id;

        return [
            'dusun_id' => ['required', 'exists:dusuns,id'],
            'nomor' => [
                'required',
                'string',
                'max:10',
                Rule::unique('rws', 'nomor')
                    ->ignore($rwId)
                    ->where(fn ($query) => $query->where('dusun_id', $this->input('dusun_id'))),
            ],
            'kode' => ['nullable', 'string', 'max:20'],
        ];
    }
}
