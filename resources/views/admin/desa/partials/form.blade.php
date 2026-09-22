@php
    use App\Support\BaliRegion;

    /** @var \App\Models\Desa|null $desa */
    $desa = $desa ?? null;
    $currentActive = old('is_active', $desa?->is_active ?? true);

    $kabupatenList = BaliRegion::kabupaten();
    $selectedKabupaten = old('kabupaten', $desa?->kabupaten ?? '');
    $selectedKecamatan = old('kecamatan', $desa?->kecamatan ?? '');
    $kecamatanList = BaliRegion::kecamatanByKabupaten()[$selectedKabupaten] ?? [];

    // Data historis di luar daftar resmi tetap ditampilkan agar edit desa lama tidak kehilangan nilai.
    if ($selectedKabupaten !== '' && ! in_array($selectedKabupaten, $kabupatenList, true)) {
        $kabupatenList[] = $selectedKabupaten;
    }
    if ($selectedKecamatan !== '' && ! in_array($selectedKecamatan, $kecamatanList, true)) {
        $kecamatanList[] = $selectedKecamatan;
    }
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
        <x-form.select name="kabupaten" label="Kabupaten/Kota" :options="array_combine($kabupatenList, $kabupatenList)"
                      :value="$selectedKabupaten" required placeholder="— Pilih Kabupaten/Kota —" />
    </div>
    <div class="col-md-6">
        <x-form.select name="kecamatan" label="Kecamatan" :options="array_combine($kecamatanList, $kecamatanList)"
                      :value="$selectedKecamatan" required placeholder="— Pilih Kecamatan —" />
    </div>

    <div class="col-md-3">
        <x-form.input name="kode_pos" label="Kode Pos" :value="$desa?->kode_pos" autocomplete="postal-code" />
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

<script>
    (function () {
        var regions = @json(BaliRegion::REGIONS);
        var kabSelect = document.getElementById('kabupaten');
        var kecSelect = document.getElementById('kecamatan');
        var kodePosInput = document.getElementById('kode_pos');
        if (!kabSelect || !kecSelect || !kodePosInput) return;

        var updateKodePos = function () {
            var kab = kabSelect.value, kec = kecSelect.value;
            if (kab && kec && regions[kab] && regions[kab][kec] && !kodePosInput.value) {
                kodePosInput.value = regions[kab][kec];
            }
        };

        var rebuildKecamatan = function (kab, keepValue) {
            var prev = kecSelect.value;
            kecSelect.innerHTML = '<option value="">— Pilih Kecamatan —</option>';
            var kecs = Object.keys(regions[kab] || {});
            kecs.forEach(function (k) {
                var opt = document.createElement('option');
                opt.value = k;
                opt.textContent = k;
                kecSelect.appendChild(opt);
            });
            if (keepValue && kecs.indexOf(prev) !== -1) {
                kecSelect.value = prev;
            }
            if (kecSelect.dataset.original && kecs.indexOf(kecSelect.dataset.original) !== -1) {
                kecSelect.value = kecSelect.dataset.original;
                delete kecSelect.dataset.original;
            }
            updateKodePos();
        };

        // Simpan nilai awal (mode edit) sebelum event apapun. Saat validasi server gagal,
        // dropdown kecamatan sudah dirender server-side sesuai kabupaten terpilih.
        if (kecSelect.value) kecSelect.dataset.original = kecSelect.value;

        kabSelect.addEventListener('change', function () {
            rebuildKecamatan(kabSelect.value, false);
            kodePosInput.value = '';
        });
        kecSelect.addEventListener('change', updateKodePos);
    })();
</script>
