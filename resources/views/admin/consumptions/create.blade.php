@extends('layouts.app')

@section('title', 'Pakai Material')
@section('page-pretitle', 'Produksi')
@section('page-title', 'Pakai Material')

@section('content')
<div class="row">
  <div class="col-12">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title">Catat Pemakaian Material</h3>
        <a href="{{ route('admin.stocks.index', ['view' => 'produksi']) }}" class="btn btn-secondary btn-sm">
          <i class="ti ti-arrow-left"></i> Kembali
        </a>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <h4>{{ $material->name }}</h4>
          <p class="text-muted mb-1">Kategori: {{ $material->category?->name ?? 'Tanpa Kategori' }}</p>
          <p class="text-muted">Stok Produksi Tersedia: <strong>{{ $available }} {{ $material->unit }}</strong></p>
        </div>

        <form action="{{ route('admin.consumptions.store') }}" method="POST">
          @csrf
          <input type="hidden" name="material_id" value="{{ $material->id }}">

          <div class="mb-3">
            <label class="form-label">Jumlah Pemakaian</label>
            <input type="number" name="quantity" min="1" step="1" class="form-control @error('quantity') is-invalid @enderror"
              value="{{ old('quantity') }}" placeholder="Masukkan jumlah yang dipakai" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value < 1) this.value = '';">
            @error('quantity')
              <div class="invalid-feedback">{{ $message }}</div>
              
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Catatan (opsional)</label>
            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
            @error('notes')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.stocks.index', ['view' => 'produksi']) }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Pemakaian</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
