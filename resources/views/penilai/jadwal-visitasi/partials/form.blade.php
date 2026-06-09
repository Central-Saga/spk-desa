@php
    /** @var \App\Models\JadwalVisitasi|null $jadwal */
    $jadwal = $jadwal ?? null;

    $desaOptions = $desaList->mapWithKeys(fn ($d) => [$d->id => $d->nama])->all();
    $periodeOptions = $periodeList->mapWithKeys(fn ($p) => [$p->id => $p->nama . ' (' . $p->tahun . ')'])->all();
    $petugasOptions = $petugasList->mapWithKeys(fn ($u) => [$u->id => $u->name])->all();
    $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();

    $currentDesa = old('desa_id', $jadwal?->desa_id ?? ($defaultDesaId ?? null));
    $currentPeriode = old('periode_id', $jadwal?->periode_id);
    $currentPetugas = old('petugas_id', $jadwal?->petugas_id);
    $currentStatus = old('status', $jadwal?->status?->value ?? 'terjadwal');
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <x-form.select name="desa_id" label="Desa" :options="$desaOptions"
                       :value="$currentDesa" required />
    </div>
    <div class="col-md-6">
        <x-form.select name="periode_id" label="Periode" :options="$periodeOptions"
                       :value="$currentPeriode" required />
    </div>

    <div class="col-md-4">
        <x-form.input name="tanggal_visitasi" type="date" label="Tanggal Visitasi"
                      :value="$jadwal?->tanggal_visitasi?->format('Y-m-d')" required />
    </div>
    <div class="col-md-4">
        <x-form.input name="waktu_mulai" type="time" label="Waktu Mulai"
                      :value="$jadwal?->waktu_mulai ? \Illuminate\Support\Str::of($jadwal->waktu_mulai)->limit(5, '')->value() : null"
                      required />
    </div>
    <div class="col-md-4">
        <x-form.input name="waktu_selesai" type="time" label="Waktu Selesai"
                      :value="$jadwal?->waktu_selesai ? \Illuminate\Support\Str::of($jadwal->waktu_selesai)->limit(5, '')->value() : null" />
    </div>

    <div class="col-md-6">
        <x-form.select name="petugas_id" label="Petugas Penilai" :options="$petugasOptions"
                       :value="$currentPetugas" required
                       help="Hanya pengguna dengan role Staff Penilaian yang ditampilkan." />
    </div>
    <div class="col-md-6">
        <x-form.select name="status" label="Status" :options="$statusOptions"
                       :value="$currentStatus" required :placeholder="false" />
    </div>

    <div class="col-12">
        <x-form.input name="lokasi" label="Lokasi"
                      :value="$jadwal?->lokasi"
                      placeholder="Contoh: Kantor Desa Bedugul, Jl. Raya Bedugul No. 1" required />
    </div>

    <div class="col-12">
        <x-form.textarea name="catatan" label="Catatan" :value="$jadwal?->catatan" rows="2" />
    </div>
</div>
