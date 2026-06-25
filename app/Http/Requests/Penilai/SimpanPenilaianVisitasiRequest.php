<?php

namespace App\Http\Requests\Penilai;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class SimpanPenilaianVisitasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return $user->isStaffPenilaian() || $user->isSuperAdmin();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'penilaian' => ['required', 'array', 'min:1'],
            'penilaian.*.indikator' => ['required', 'string', 'max:150'],
            'penilaian.*.bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'penilaian.*.skor' => ['required', 'numeric', 'min:0', 'max:100'],
            'penilaian.*.keterangan' => ['nullable', 'string'],
            'penilaian.*.bukti_gambar' => ['nullable', 'array'],
            'penilaian.*.bukti_gambar.*' => ['required', File::image()->max('2mb')],
            'penilaian.*.hapus_gambar' => ['nullable', 'array'],
            'penilaian.*.hapus_gambar.*' => ['integer', 'exists:bukti_visitasi_gambar,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'penilaian.*.skor.required' => 'Skor wajib diisi pada setiap indikator.',
            'penilaian.*.skor.min' => 'Skor minimal 0.',
            'penilaian.*.skor.max' => 'Skor maksimal 100.',
            'penilaian.*.bukti_gambar.*.image' => 'Setiap bukti gambar harus berupa file gambar yang valid.',
            'penilaian.*.bukti_gambar.*.max' => 'Ukuran tiap bukti gambar maksimal 2 MB.',
            'penilaian.*.hapus_gambar.*.exists' => 'Gambar yang ditandai hapus tidak valid.',
        ];
    }
}
