@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('sidebar')
    @include($sidebarTemplate)
@endsection

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h4 fw-semibold mb-1">Manajemen Pengguna</h1>
            <p class="text-secondary mb-0 small">Kelola akun dan hak akses pengguna sistem.</p>
        </div>
        <a href="{{ route('admin.pengguna.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pengguna
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.pengguna.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                           class="form-control form-control-sm"
                           placeholder="Cari nama, username, atau email...">
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select form-select-sm">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->value }}" @selected($filters['role'] === $r->value)>
                                {{ $r->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.pengguna.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Desa</th>
                            <th>Status</th>
                            <th class="text-end pe-3" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengguna as $row)
                            <tr>
                                <td class="ps-3 text-secondary small">
                                    {{ ($pengguna->currentPage() - 1) * $pengguna->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ $row->name }}</td>
                                <td><code class="small">{{ $row->username }}</code></td>
                                <td class="small">{{ $row->email }}</td>
                                <td>
                                    @foreach ($row->roles as $role)
                                        @php $slug = \App\Enums\RoleSlug::tryFrom($role->name); @endphp
                                        <span class="badge bg-secondary">
                                            {{ $slug?->label() ?? $role->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="text-secondary small">{{ $row->desa?->nama ?? '—' }}</td>
                                <td>
                                    @if ($row->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle me-1"></i>Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle me-1"></i>Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.pengguna.edit', $row) }}"
                                       class="btn btn-sm btn-outline-primary" title="Ubah">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if ($row->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.pengguna.destroy', $row) }}"
                                              class="d-inline-block"
                                              onsubmit="return confirm('Hapus pengguna {{ $row->username }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada data pengguna.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pengguna->hasPages())
                <div class="mt-3">
                    {{ $pengguna->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
