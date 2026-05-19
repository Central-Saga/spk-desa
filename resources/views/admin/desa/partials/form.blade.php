@php
    /** @var \App\Models\Desa|null $desa */
    $desa = $desa ?? null;
    $currentActive = old('is_active', $desa?->is_active ?? true);
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <x-form.input name="nama" label="Nama Desa" :value="$desa?->nama" required />
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <x-form.checkbox name="is_active" label="Desa aktif" :checked="(bool) $currentActive" />
    </div>

    <div class="col-12">
        <x-form.textarea name="alamat" label="Alamat" :value="$desa?->alamat" required rows="2" />
    </div>

    <div class="col-md-6">
        <x-form.input name="kecamatan" label="Kecamatan" :value="$desa?->kecamatan" required />
    </div>
    <div class="col-md-6">
        <x-form.input name="kabupaten" label="Kabupaten" :value="$desa?->kabupaten" required />
    </div>

    <div class="col-md-3">
        <x-form.input name="kode_pos" label="Kode Pos" :value="$desa?->kode_pos" />
    </div>
    <div class="col-md-4">
        <x-form.input name="telepon" label="Telepon" :value="$desa?->telepon" />
    </div>
    <div class="col-md-5">
        <x-form.input name="email" type="email" label="Email" :value="$desa?->email" />
    </div>

    <div class="col-md-7">
        <x-form.input name="kepala_desa" label="Nama Kepala Desa" :value="$desa?->kepala_desa" />
    </div>
    <div class="col-md-5">
        <x-form.input name="jumlah_penduduk" type="number" label="Jumlah Penduduk"
                      :value="$desa?->jumlah_penduduk" />
    </div>
</div>
