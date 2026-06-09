<div class="modal fade" id="modalSalin" tabindex="-1" aria-labelledby="modalSalinLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.visitasi.salin') }}">
                @csrf
                <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                <input type="hidden" name="desa_tujuan_id" value="{{ $desa->id }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalSalinLabel">
                        <i class="bi bi-clipboard-plus me-1"></i> Salin Indikator ke {{ $desa->nama }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small">
                        Pilih desa sumber. Seluruh indikator aktif desa tersebut akan disalin ke
                        <strong>{{ $desa->nama }}</strong> dengan kode otomatis. Indikator yang membuat total bobot melebihi 100 akan dilewati.
                    </p>
                    <label for="desa_sumber_id" class="form-label small fw-medium">Desa Sumber <span class="text-danger">*</span></label>
                    <select id="desa_sumber_id" name="desa_sumber_id" class="form-select" required>
                        <option value="">— Pilih desa sumber —</option>
                        @foreach ($desaOptions as $opt)
                            @if ($opt->id !== $desa->id)
                                <option value="{{ $opt->id }}">{{ $opt->nama }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-clipboard-check me-1"></i> Salin Indikator</button>
                </div>
            </form>
        </div>
    </div>
</div>
