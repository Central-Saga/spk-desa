<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->can('desa.update')) {
            return true;
        }

        // Staff Admin Desa boleh update desanya sendiri
        $desa = $this->route('desa') ?? $user->desa;

        return $user->can('desa.update-own') && $desa && $user->desa_id === $desa->id;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'alamat' => ['required', 'string'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kabupaten' => ['required', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'kepala_desa' => ['nullable', 'string', 'max:150'],
            'jumlah_penduduk' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'Nama desa',
            'alamat' => 'Alamat',
            'kecamatan' => 'Kecamatan',
            'kabupaten' => 'Kabupaten',
            'kode_pos' => 'Kode pos',
            'telepon' => 'Telepon',
            'email' => 'Email',
            'kepala_desa' => 'Kepala desa',
            'jumlah_penduduk' => 'Jumlah penduduk',
            'is_active' => 'Status aktif',
        ];
    }
}
