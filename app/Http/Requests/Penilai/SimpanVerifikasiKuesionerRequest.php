<?php

namespace App\Http\Requests\Penilai;

use Illuminate\Foundation\Http\FormRequest;

class SimpanVerifikasiKuesionerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'verifikasi' => ['required', 'array', 'min:1'],
            'verifikasi.*.kuesioner_id' => ['required', 'exists:kuesioner,id'],
            'verifikasi.*.status_verifikasi' => ['required', 'in:ya,tidak'],
            'verifikasi.*.catatan' => ['nullable', 'string', 'max:500'],
            'verifikasi.*.jawaban_desa_skor' => ['nullable', 'numeric'],
            'verifikasi.*.jawaban_desa_teks' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'verifikasi.required' => 'Data verifikasi wajib diisi.',
            'verifikasi.*.status_verifikasi.required' => 'Status verifikasi wajib dipilih.',
            'verifikasi.*.status_verifikasi.in' => 'Status verifikasi harus "Ya" atau "Tidak".',
        ];
    }

    public function attributes(): array
    {
        return [
            'verifikasi.*.status_verifikasi' => 'status verifikasi',
            'verifikasi.*.catatan' => 'catatan',
        ];
    }
}
