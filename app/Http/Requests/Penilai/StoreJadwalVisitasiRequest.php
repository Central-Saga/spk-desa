<?php

namespace App\Http\Requests\Penilai;

use App\Enums\StatusVisitasi;
use App\Models\JadwalVisitasi;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreJadwalVisitasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user?->can('jadwal-visitasi.create') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'desa_id' => ['required', 'integer', 'exists:desa,id'],
            'periode_id' => ['required', 'integer', 'exists:periode_penilaian,id'],
            'tanggal_visitasi' => ['required', 'date'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => ['nullable', 'date_format:H:i', 'after:waktu_mulai'],
            'lokasi' => ['required', 'string', 'max:255'],
            'petugas_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::in(array_column(StatusVisitasi::cases(), 'value'))],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $tanggal = $this->date('tanggal_visitasi')?->toDateString();
            $mulai = $this->input('waktu_mulai');
            $selesai = $this->input('waktu_selesai') ?: '23:59';
            $petugasId = $this->input('petugas_id');

            if (! $tanggal || ! $mulai || ! $petugasId) {
                return;
            }

            $bentrok = JadwalVisitasi::query()
                ->where('petugas_id', $petugasId)
                ->whereDate('tanggal_visitasi', $tanggal)
                ->where(function ($q) use ($mulai, $selesai) {
                    $q->whereBetween('waktu_mulai', [$mulai, $selesai])
                        ->orWhereBetween('waktu_selesai', [$mulai, $selesai])
                        ->orWhere(function ($q2) use ($mulai, $selesai) {
                            $q2->where('waktu_mulai', '<=', $mulai)
                                ->where(function ($q3) use ($selesai) {
                                    $q3->where('waktu_selesai', '>=', $selesai)
                                        ->orWhereNull('waktu_selesai');
                                });
                        });
                })
                ->exists();

            if ($bentrok) {
                $v->errors()->add('petugas_id', 'Petugas sudah memiliki jadwal lain pada rentang waktu yang sama.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'desa_id' => 'Desa',
            'periode_id' => 'Periode',
            'tanggal_visitasi' => 'Tanggal visitasi',
            'waktu_mulai' => 'Waktu mulai',
            'waktu_selesai' => 'Waktu selesai',
            'lokasi' => 'Lokasi',
            'petugas_id' => 'Petugas penilai',
            'status' => 'Status',
            'catatan' => 'Catatan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
            'waktu_mulai.date_format' => 'Format waktu mulai harus HH:MM.',
            'waktu_selesai.date_format' => 'Format waktu selesai harus HH:MM.',
        ];
    }
}
