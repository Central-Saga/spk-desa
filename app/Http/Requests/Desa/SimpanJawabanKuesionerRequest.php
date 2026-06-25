<?php

namespace App\Http\Requests\Desa;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SimpanJawabanKuesionerRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user?->can('kuesioner.isi') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        $rules = [
            'periode_id' => ['required', 'integer', 'exists:periode_penilaian,id'],
            'finalisasi' => ['nullable', 'boolean'],

            'jawaban' => ['required', 'array', 'min:1'],
            'jawaban.*.kuesioner_id' => ['required', 'integer', 'exists:kuesioner,id'],
            'jawaban.*.jawaban' => ['nullable', 'string'],
            'jawaban.*.keterangan' => ['nullable', 'string'],
        ];

        if ($user->isSuperAdmin()) {
            $rules['jawaban.*.skor'] = ['nullable', 'numeric', 'min:0', 'max:100'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (! $this->boolean('finalisasi')) {
                return;
            }

            /** @var User $user */
            $user = $this->user();

            if ($user->isSuperAdmin()) {
                $jawaban = (array) $this->input('jawaban', []);

                foreach ($jawaban as $idx => $item) {
                    if (! isset($item['skor']) || $item['skor'] === '' || $item['skor'] === null) {
                        $v->errors()->add(
                            "jawaban.{$idx}.skor",
                            'Saat finalisasi, semua indikator wajib memiliki skor.'
                        );
                    }
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jawaban.required' => 'Form jawaban tidak boleh kosong.',
            'jawaban.*.skor.numeric' => 'Skor harus berupa angka.',
            'jawaban.*.skor.min' => 'Skor minimal 0.',
            'jawaban.*.skor.max' => 'Skor maksimal 100.',
        ];
    }
}
