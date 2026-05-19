@php
    /** @var \App\Models\User|null $pengguna */
    $pengguna = $pengguna ?? null;
    $isEdit = ($mode ?? 'create') === 'edit';

    $rolesOptions = collect($roles)->mapWithKeys(fn ($r) => [$r->value => $r->label()])->all();
    $desaOptions = $desa->mapWithKeys(fn ($d) => [$d->id => $d->nama])->all();

    $currentRole = old('role', $pengguna?->roles->first()?->name);
    $currentDesa = old('desa_id', $pengguna?->desa_id);
    $currentActive = old('is_active', $pengguna?->is_active ?? true);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-form.input name="name" label="Nama Lengkap" :value="$pengguna?->name" required />
    </div>
    <div class="col-md-6">
        <x-form.input name="username" label="Username" :value="$pengguna?->username" required
                      help="Hanya huruf, angka, tanda hubung, dan garis bawah." />
    </div>
    <div class="col-md-6">
        <x-form.input name="email" type="email" label="Email" :value="$pengguna?->email" required />
    </div>
    <div class="col-md-6">
        <x-form.select name="role" label="Role" :options="$rolesOptions" :value="$currentRole"
                       required help="Tentukan peran pengguna di sistem." />
    </div>
    <div class="col-md-6">
        <x-form.select name="desa_id" label="Desa (khusus Staff Admin Desa)"
                       :options="$desaOptions" :value="$currentDesa"
                       placeholder="— Tidak terhubung ke desa —"
                       help="Wajib dipilih untuk Staff Admin Desa." />
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <x-form.checkbox name="is_active" label="Akun aktif" :checked="(bool) $currentActive" />
    </div>
    <div class="col-md-6">
        <x-form.input name="password" type="password" label="Password"
                      :required="! $isEdit"
                      help="{{ $isEdit ? 'Kosongkan jika tidak ingin mengganti password.' : 'Minimal 8 karakter.' }}" />
    </div>
    <div class="col-md-6">
        <x-form.input name="password_confirmation" type="password" label="Konfirmasi Password" />
    </div>
</div>
