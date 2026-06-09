@php
    /** @var \App\Models\IndikatorVisitasi|null $visitasi */
    $visitasi = $visitasi ?? null;
    $currentActive = old('is_active', $visitasi?->is_active ?? true);
    $desaValue = $visitasi?->desa_id ?? ($desa?->id ?? null);
    $kodeValue = $visitasi?->kode ?? ($kodeSaran ?? '');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-form.select name="desa_id" label="Desa"
                       :options="$desaOptions->pluck('nama', 'id')"
                       :value="$desaValue"
                       placeholder="— Pilih desa —"
                       help="Pertanyaan visitasi ini hanya berlaku untuk desa yang dipilih."
                       required />
    </div>
    <div class="col-md-3">
        <x-form.input name="kode" label="Kode Indikator"
                      :value="$kodeValue"
                      placeholder="VIS-BEBANDEM-01" required />
    </div>
    <div class="col-md-3">
        <x-form.input name="urutan" type="number" label="Urutan"
                      :value="$visitasi?->urutan ?? ($urutanDefault ?? 1)" required />
    </div>

    <div class="col-12">
        <x-form.input name="indikator_visitasi" label="Pertanyaan / Indikator Visitasi"
                      :value="$visitasi?->indikator_visitasi"
                      placeholder="Contoh: Apakah desa memiliki prestasi tingkat kabupaten/provinsi/nasional?"
                      required />
    </div>

    <div class="col-12">
        <x-form.textarea name="deskripsi" label="Deskripsi / Keterangan"
                         :value="$visitasi?->deskripsi"
                         rows="3"
                         placeholder="Contoh: Lampirkan bukti piagam, dokumentasi, atau SK penghargaan." />
    </div>

    <div class="col-md-4">
        <x-form.input name="bobot" type="number" label="Bobot Indikator"
                      :value="$visitasi?->bobot"
                      :help="'Sisa kuota bobot desa ini: ' . number_format($sisaBobot ?? 100, 2)"
                      required />
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <x-form.checkbox name="is_active" label="Indikator aktif" :checked="(bool) $currentActive" />
    </div>
</div>
