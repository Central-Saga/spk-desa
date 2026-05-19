<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoleSlug;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user?->can('pengguna.create') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(RoleSlug::values())],
            'desa_id' => ['nullable', 'integer', 'exists:desa,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->input('role') === RoleSlug::StaffAdminDesa->value && ! $this->filled('desa_id')) {
                $v->errors()->add('desa_id', 'Staff Admin Desa wajib terhubung ke salah satu desa.');
            }

            if ($this->input('role') !== RoleSlug::StaffAdminDesa->value && $this->filled('desa_id')) {
                $v->errors()->add('desa_id', 'Hanya Staff Admin Desa yang boleh memiliki desa.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'role' => 'Role',
            'desa_id' => 'Desa',
            'is_active' => 'Status aktif',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal :min karakter.',
        ];
    }
}
