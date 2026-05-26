@php
    /** @var \App\Models\IndikatorVisitasi|null $visitasi */
    $visitasi = $visitasi ?? null;
    $currentActive = old('is_active', $visitasi?->is_active ?? true);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-form.input name="kategori" label="Kategori"
                      :value="$visitasi?->kategori"
                      placeholder="Contoh: Observasi" required />
    </div>
    <div class="col-md-3">
        <x-form.input name="kode" label="Kode Indikator"
                      :value="$visitasi?->kode"
                      placeholder="V-OBSV-01" required />
    </div>
    <div class="col-md-3">
        <x-form.input name="urutan" type="number" label="Urutan"
                      :value="$visitasi?->urutan ?? ($urutanDefault ?? 1)" required />
    </div>

    <div class="col-12">
        <x-form.input name="indikator_visitasi" label="Indikator Visitasi"
                      :value="$visitasi?->indikator_visitasi"
                      placeholder="Judul indikator visitasi"
                      required />
    </div>

    <div class="col-12">
        <x-form.textarea name="deskripsi" label="Deskripsi Indikator"
                         :value="$visitasi?->deskripsi"
                         rows="3"
                         placeholder="Penjelasan detail indikator visitasi" />
    </div>

    <div class="col-md-4">
        <x-form.input name="bobot" type="number" label="Bobot Indikator"
                      :value="$visitasi?->bobot"
                      :help="'Sisa kuota bobot periode ini: ' . number_format($sisaBobot ?? 100, 2)"
                      required />
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <x-form.checkbox name="is_active" label="Indikator aktif" :checked="(bool) $currentActive" />
    </div>
</div>
