@php
    /** @var \App\Models\Kuesioner|null $kuesioner */
    $kuesioner = $kuesioner ?? null;
    $currentActive = old('is_active', $kuesioner?->is_active ?? true);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-form.input name="kategori" label="Kategori"
                      :value="$kuesioner?->kategori"
                      placeholder="Contoh: Transparansi Informasi" required />
    </div>
    <div class="col-md-3">
        <x-form.input name="kode_indikator" label="Kode Indikator"
                      :value="$kuesioner?->kode_indikator"
                      placeholder="TI-01" required />
    </div>
    <div class="col-md-3">
        <x-form.input name="urutan" type="number" label="Urutan"
                      :value="$kuesioner?->urutan ?? ($urutanDefault ?? 1)" required />
    </div>

    <div class="col-12">
        <x-form.textarea name="pertanyaan" label="Pertanyaan"
                         :value="$kuesioner?->pertanyaan"
                         rows="3" required />
    </div>

    <div class="col-md-4">
        <x-form.input name="bobot_indikator" type="number" label="Bobot Indikator"
                      :value="$kuesioner?->bobot_indikator"
                      :help="'Sisa kuota bobot periode ini: ' . number_format($sisaBobot ?? 100, 2)"
                      required />
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <x-form.checkbox name="is_active" label="Indikator aktif" :checked="(bool) $currentActive" />
    </div>
</div>
