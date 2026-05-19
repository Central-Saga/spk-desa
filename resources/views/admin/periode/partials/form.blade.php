@php
    /** @var \App\Models\PeriodePenilaian|null $periode */
    $periode = $periode ?? null;
    $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
    $currentStatus = old('status', $periode?->status?->value ?? 'draft');
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <x-form.input name="nama" label="Nama Periode" :value="$periode?->nama"
                      placeholder="Contoh: Penilaian Apresiasi Desa 2026" required />
    </div>
    <div class="col-md-4">
        <x-form.input name="tahun" type="number" label="Tahun"
                      :value="$periode?->tahun ?? now()->year" required />
    </div>

    <div class="col-md-6">
        <x-form.input name="tanggal_mulai" type="date" label="Tanggal Mulai"
                      :value="$periode?->tanggal_mulai?->format('Y-m-d')" required />
    </div>
    <div class="col-md-6">
        <x-form.input name="tanggal_selesai" type="date" label="Tanggal Selesai"
                      :value="$periode?->tanggal_selesai?->format('Y-m-d')" required />
    </div>

    <div class="col-md-6">
        <x-form.select name="status" label="Status" :options="$statusOptions"
                       :value="$currentStatus" required :placeholder="false"
                       help="Mengaktifkan periode ini akan otomatis menyelesaikan periode aktif lainnya." />
    </div>

    <div class="col-12">
        <x-form.textarea name="keterangan" label="Keterangan" :value="$periode?->keterangan" rows="2" />
    </div>
</div>
