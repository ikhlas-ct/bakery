@extends('layouts.user.user')

@section('title', 'Permintaan Bahan Baku')

@section('styles')
<style>
    .ph-card {
        background: #fff; border: 1px solid #e9ecef; border-radius: 14px;
        padding: 16px 20px; display: flex; align-items: center;
        justify-content: space-between; gap: 16px; flex-wrap: wrap;
        margin-bottom: 1.25rem; position: relative; overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .ph-card::before {
        content: ''; position: absolute;
        left: 0; top: 0; bottom: 0; width: 4px;
        border-radius: 14px 0 0 14px; background: #1a73e8;
    }
    .ph-left { display: flex; align-items: center; gap: 12px; }
    .ph-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
        background: #dbeafe; color: #1a73e8;
    }
    .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
    .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
    .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
    .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
    .ph-breadcrumb a:hover { text-decoration: underline; }
    .ph-breadcrumb .bc-active { font-size: .75rem; color: #94a3b8; }

    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-mixed { background: #dbeafe; color: #1e40af; }
    .badge-clear { background: #dcfce7; color: #166534; }
    table.table thead th { font-size: .78rem; text-transform: uppercase; color: #64748b; }
</style>
@endsection

@section('content')
<div class="container">

    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-file-invoice"></i></div>
            <div>
                <h5 class="ph-title">Permintaan Bahan Baku</h5>
                <ol class="ph-breadcrumb">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span class="bc-active">Permintaan Bahan Baku</span></li>
                </ol>
            </div>
        </div>
        <a href="{{ route('permintaan-bahan-baku.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Permintaan Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group" style="max-width: 320px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari nomor transaksi..."
                        value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nomor Transaksi</th>
                            <th>Tanggal Permintaan</th>
                            <th>Admin</th>
                            <th class="text-center">Jumlah Item</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permintaans as $permintaan)
                            @php
                                $statuses = $permintaan->permintaanBahanBakuDetails->pluck('status_produsen')->unique();
                                $badgeClass = $statuses->count() === 1 && $statuses->first() === 'pending'
                                    ? 'badge-pending'
                                    : ($statuses->every(fn ($s) => $s !== 'pending') ? 'badge-clear' : 'badge-mixed');
                                $badgeLabel = $statuses->count() === 1 && $statuses->first() === 'pending'
                                    ? 'Menunggu Respons'
                                    : ($statuses->every(fn ($s) => $s !== 'pending') ? 'Selesai Direspons' : 'Sebagian Direspons');
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $permintaan->nomor_transaksi }}</td>
                                <td>{{ \Carbon\Carbon::parse($permintaan->tanggal_permintaan)->format('d M Y') }}</td>
                                <td>{{ $permintaan->admin->nama ?? '-' }}</td>
                                <td class="text-center">{{ $permintaan->jumlah_item }}</td>
                                <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('permintaan-bahan-baku.show', $permintaan->id) }}"
                                        class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('permintaan-bahan-baku.edit', $permintaan->id) }}"
                                        class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('permintaan-bahan-baku.destroy', $permintaan->id) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus permintaan {{ $permintaan->nomor_transaksi }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada permintaan bahan baku.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $permintaans->links() }}
        </div>
    </div>
</div>
@endsection
