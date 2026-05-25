@extends('layouts.app')

@section('title', 'Audit Trail')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Audit Trail</h1>
        <p class="text-secondary mb-0 small">Riwayat aktivitas pengguna di sistem.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-trail.index') }}" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                           class="form-control form-control-sm"
                           placeholder="Cari deskripsi atau pengguna...">
                </div>
                <div class="col-md-2">
                    <select name="aksi" class="form-select form-select-sm">
                        <option value="">Semua Aksi</option>
                        @foreach ($aksiOptions as $a)
                            <option value="{{ $a->value }}" @selected($filters['aksi'] === $a->value)>
                                {{ ucfirst(str_replace('_', ' ', $a->value)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Semua Pengguna</option>
                        @foreach ($userOptions as $u)
                            <option value="{{ $u->id }}" @selected((int) $filters['user_id'] === $u->id)>
                                {{ $u->name }} ({{ $u->username }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <input type="date" name="from" value="{{ $filters['from'] }}" class="form-control form-control-sm">
                    <input type="date" name="to" value="{{ $filters['to'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.audit-trail.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Deskripsi</th>
                            <th>Subjek</th>
                            <th>IP</th>
                            <th class="text-end pe-3" style="width: 80px;">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audit as $row)
                            <tr>
                                <td class="ps-3 small">{{ $row->created_at->translatedFormat('d M Y H:i') }}</td>
                                <td class="small">
                                    @if ($row->user)
                                        <div>{{ $row->user->name }}</div>
                                        <code class="text-secondary">{{ $row->user->username }}</code>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $row->aksi?->value ?? '')) }}
                                    </span>
                                </td>
                                <td class="small" style="max-width: 320px;">
                                    {{ \Illuminate\Support\Str::limit($row->deskripsi, 100) }}
                                </td>
                                <td class="small text-secondary">
                                    @if ($row->model_type)
                                        {{ class_basename($row->model_type) }} #{{ $row->model_id }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small text-secondary">{{ $row->ip_address ?? '—' }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.audit-trail.show', $row) }}"
                                       class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada catatan aktivitas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($audit->hasPages())
                <div class="mt-3">{{ $audit->links() }}</div>
            @endif
        </div>
    </div>
@endsection
