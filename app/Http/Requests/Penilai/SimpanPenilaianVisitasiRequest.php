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

        return $user?->can('penilaian-visitasi.create') ?? false;
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
            'penilaian.*.bukti_gambar' => ['nullable', File::image()->max('2mb')],
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
            'penilaian.*.bukti_gambar.image' => 'Bukti gambar harus berupa file gambar yang valid.',
            'penilaian.*.bukti_gambar.max' => 'Ukuran bukti gambar maksimal 2 MB.',
        ];
    }
}
