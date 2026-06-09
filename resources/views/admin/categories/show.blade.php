@extends('layouts.app')

@section('title', 'Detail Kategori')

@section('page-pretitle', 'Inventory')
@section('page-title', 'Detail Kategori')

@section('page-actions')
  <div class="btn-list">
    @can('edit-inventory')
    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning d-none d-sm-inline-block">
      <i class="ti ti-edit"></i> Edit
    </a>
    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning d-sm-none">
      <i class="ti ti-edit"></i>
    </a>
    @endcan
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
      <i class="ti ti-arrow-left"></i> Kembali
    </a>
  </div>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Informasi Kategori</h3>
      </div>
      <div class="card-body">
        <div class="datagrid">
          <div class="datagrid-item">
            <div class="datagrid-title">Nama</div>
            <div class="datagrid-content">{{ $category->name }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Slug</div>
            <div class="datagrid-content">{{ $category->slug }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Total Material</div>
            <div class="datagrid-content">
              <span class="badge bg-blue-lt">{{ $category->materials_count }} material</span>
            </div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Tanggal Dibuat</div>
            <div class="datagrid-content">{{ $category->created_at->format('d M Y, H:i') }}</div>
          </div>
          <div class="datagrid-item">
            <div class="datagrid-title">Terakhir Diupdate</div>
            <div class="datagrid-content">{{ $category->updated_at->format('d M Y, H:i') }}</div>
          </div>
          @if($category->description)
          <div class="datagrid-item">
            <div class="datagrid-title">Deskripsi</div>
            <div class="datagrid-content">{{ $category->description }}</div>
          </div>
          @endif
        </div>

        @can('delete-inventory')
        @if($category->materials->isEmpty())
        <div class="mt-4">
          <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class="ti ti-trash me-1"></i> Hapus Kategori
          </button>
        </div>
        @endif
        @endcan
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Material dalam Kategori Ini</h3>
      </div>
      <div class="table-responsive">
        <table class="table card-table table-vcenter">
          <thead>
            <tr>
              <th>Nama Material</th>
              <th>Satuan</th>
              <th>Stok</th>
              <th class="w-1">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($materials as $material)
            <tr>
              <td>{{ $material->name }}</td>
              <td>{{ $material->unit }}</td>
              <td>
                @if($material->stock)
                  @if($material->stock->quantity <= 10)
                    <span class="badge bg-red-lt">{{ $material->stock->quantity }}</span>
                  @else
                    <span class="badge bg-green-lt">{{ $material->stock->quantity }}</span>
                  @endif
                @else
                  <span class="badge bg-gray-lt">0</span>
                @endif
              </td>
              <td>
                <a href="{{ route('admin.materials.show', $material) }}" class="btn btn-sm btn-primary">
                  <i class="ti ti-eye"></i>
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center py-4">
                <div class="empty">
                  <div class="empty-icon">
                    <i class="ti ti-package text-muted" style="font-size: 3rem"></i>
                  </div>
                  <p class="empty-title">Tidak ada material</p>
                  <p class="empty-subtitle text-muted">
                    Belum ada material yang menggunakan kategori ini.
                  </p>
                  @can('create-inventory')
                  <div class="empty-action">
                    <a href="{{ route('admin.materials.create') }}" class="btn btn-primary">
                      <i class="ti ti-plus"></i> Tambah Material Baru
                    </a>
                  </div>
                  @endcan
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($materials->hasPages())
      <div class="card-footer d-flex align-items-center">
        {{ $materials->links('pagination.tabler') }}
      </div>
      @endif
    </div>
  </div>
</div>

@can('delete-inventory')
@if($category->materials->isEmpty())
<div class="modal modal-blur fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-title">Konfirmasi Hapus</div>
        <div>Apakah Anda yakin ingin menghapus kategori <strong>{{ $category->name }}</strong>? Tindakan ini tidak dapat dibatalkan.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endif
@endcan
@endsection