<?php

namespace App\Http\Requests\Admin;

use App\Models\IndikatorVisitasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateVisitasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $current = $this->route('visitasi');

        return [
            'periode_id' => ['required', 'integer', 'exists:periode_penilaian,id'],
            'desa_id' => ['required', 'integer', 'exists:desa,id'],
            'kode' => [
                'required', 'string', 'max:50',
                Rule::unique('indikator_visitasi', 'kode')
                    ->ignore($current?->id)
                    ->where(fn ($q) => $q
                        ->where('periode_id', $this->input('periode_id'))
                        ->where('desa_id', $this->input('desa_id'))),
            ],
            'indikator_visitasi' => ['required', 'string'],
            'deskripsi' => ['nullable', 'string'],
            'bobot' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'urutan' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $current = $this->route('visitasi');

            $totalExisting = IndikatorVisitasi::query()
                ->where('periode_id', $this->input('periode_id'))
                ->where('desa_id', $this->input('desa_id'))
                ->when($current, fn ($q) => $q->whereKeyNot($current->id))
                ->sum('bobot');

            $bobotBaru = (float) $this->input('bobot', 0);
            $totalAkhir = (float) $totalExisting + $bobotBaru;

            if (round($totalAkhir, 2) > 100) {
                $sisa = max(0, round(100 - (float) $totalExisting, 2));
                $v->errors()->add(
                    'bobot',
                    "Total bobot indikator desa ini akan menjadi {$totalAkhir} (melebihi 100). Sisa kuota: {$sisa}."
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'periode_id' => 'periode penilaian',
            'desa_id' => 'desa',
            'kode' => 'kode indikator',
            'indikator_visitasi' => 'indikator visitasi',
            'deskripsi' => 'deskripsi indikator',
            'bobot' => 'bobot',
            'urutan' => 'urutan',
        ];
    }

    public function messages(): array
    {
        return [
            'desa_id.required' => 'Desa wajib dipilih terlebih dahulu.',
            'kode.unique' => 'Kode indikator sudah dipakai pada desa di periode ini.',
            'bobot.min' => 'Bobot indikator minimal 0,01.',
            'bobot.max' => 'Bobot indikator tidak boleh lebih dari 100.',
        ];
    }
}
