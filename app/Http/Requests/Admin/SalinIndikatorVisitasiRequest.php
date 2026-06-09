<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SalinIndikatorVisitasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_id' => ['required', 'integer', 'exists:periode_penilaian,id'],
            'desa_sumber_id' => ['required', 'integer', 'exists:desa,id'],
            'desa_tujuan_id' => ['required', 'integer', 'exists:desa,id', 'different:desa_sumber_id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'periode_id' => 'periode penilaian',
            'desa_sumber_id' => 'desa sumber',
            'desa_tujuan_id' => 'desa tujuan',
        ];
    }

    public function messages(): array
    {
        return [
            'desa_tujuan_id.different' => 'Desa tujuan tidak boleh sama dengan desa sumber.',
        ];
    }
}
