@extends('layouts.app')

@section('title', 'Detail Audit Trail')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Detail Audit Trail</h1>
            <p class="text-secondary mb-0 small">{{ $audit->created_at->translatedFormat('d F Y H:i:s') }}</p>
        </div>
        <a href="{{ route('admin.audit-trail.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3 small text-secondary">Pengguna</dt>
                <dd class="col-sm-9">
                    @if ($audit->user)
                        {{ $audit->user->name }} ({{ $audit->user->username }})
                    @else
                        <span class="text-secondary">— sistem —</span>
                    @endif
                </dd>

                <dt class="col-sm-3 small text-secondary">Aksi</dt>
                <dd class="col-sm-9">
                    <span class="badge bg-secondary-subtle text-secondary">
                        {{ ucfirst(str_replace('_', ' ', $audit->aksi?->value ?? '')) }}
                    </span>
                </dd>

                <dt class="col-sm-3 small text-secondary">Deskripsi</dt>
                <dd class="col-sm-9">{{ $audit->deskripsi }}</dd>

                <dt class="col-sm-3 small text-secondary">Subjek</dt>
                <dd class="col-sm-9 small text-secondary">
                    @if ($audit->model_type)
                        {{ $audit->model_type }} #{{ $audit->model_id }}
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-sm-3 small text-secondary">IP Address</dt>
                <dd class="col-sm-9 small">{{ $audit->ip_address ?? '—' }}</dd>

                <dt class="col-sm-3 small text-secondary">User Agent</dt>
                <dd class="col-sm-9 small">{{ $audit->user_agent ?? '—' }}</dd>

                <dt class="col-sm-3 small text-secondary">Payload</dt>
                <dd class="col-sm-9">
                    @if ($audit->payload)
                        <pre class="mb-0 p-3 bg-light border rounded small"
                             style="max-height: 320px; overflow: auto;">{{ json_encode($audit->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @else
                        <span class="text-secondary small">—</span>
                    @endif
                </dd>
            </dl>
        </div>
    </div>
@endsection
