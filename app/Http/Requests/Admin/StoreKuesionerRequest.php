<?php

namespace App\Http\Requests\Admin;

use App\Models\Kuesioner;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreKuesionerRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user?->can('kuesioner.create') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'periode_id' => ['required', 'integer', 'exists:periode_penilaian,id'],
            'kategori' => ['required', 'string', 'max:100'],
            'kode_indikator' => [
                'required', 'string', 'max:50',
                Rule::unique('kuesioner', 'kode_indikator')
                    ->where(fn ($q) => $q->where('periode_id', $this->input('periode_id'))
                        ->whereNull('deleted_at')),
            ],
            'pertanyaan' => ['required', 'string'],
            'bobot_indikator' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'urutan' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $totalExisting = Kuesioner::query()
                ->where('periode_id', $this->input('periode_id'))
                ->sum('bobot_indikator');

            $bobotBaru = (float) $this->input('bobot_indikator', 0);
            $totalAkhir = (float) $totalExisting + $bobotBaru;

            if (round($totalAkhir, 2) > 100) {
                $sisa = max(0, round(100 - (float) $totalExisting, 2));
                $v->errors()->add(
                    'bobot_indikator',
                    "Total bobot indikator periode ini akan menjadi {$totalAkhir} (melebihi 100). Sisa kuota: {$sisa}."
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'periode_id' => 'Periode',
            'kategori' => 'Kategori',
            'kode_indikator' => 'Kode indikator',
            'pertanyaan' => 'Pertanyaan',
            'bobot_indikator' => 'Bobot indikator',
            'urutan' => 'Urutan',
            'is_active' => 'Status aktif',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kode_indikator.unique' => 'Kode indikator sudah dipakai pada periode ini.',
            'bobot_indikator.min' => 'Bobot indikator minimal 0,01.',
            'bobot_indikator.max' => 'Bobot indikator tidak boleh lebih dari 100.',
        ];
    }
}
