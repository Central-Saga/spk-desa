@extends('layouts.app')

@section('title', 'Profil Desa')

@section('sidebar')
    @include('desa.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Profil Desa</h1>
        <p class="text-secondary mb-0 small">Perbarui data identitas {{ $desa->nama }}.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('desa.profil.update') }}" novalidate>
                @csrf
                @method('PUT')
                @include('admin.desa.partials.form', ['mode' => 'edit'])
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Perbarui</button>
                </div>
            </form>
        </div>
    </div>
@endsection
