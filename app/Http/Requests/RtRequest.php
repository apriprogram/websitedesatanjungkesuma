<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        $rt = $this->route('rt');
        $rtId = $rt?->id;

        return [
            'rw_id' => ['required', 'exists:rws,id'],
            'nomor' => [
                'required',
                'string',
                'max:10',
                Rule::unique('rts', 'nomor')
                    ->ignore($rtId)
                    ->where(fn ($query) => $query->where('rw_id', $this->input('rw_id'))),
            ],
            'kode' => ['nullable', 'string', 'max:20'],
        ];
    }
}
