@extends('layouts.user.user')

@section('title', 'Data Produsen')

@section('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body, .card, .table, .btn, h4, h5 {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            border: none;
            border-radius: 16px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0, 0, 0, .12); }
        .stat-card::after {
            content: ''; position: absolute; right: -18px; top: -18px;
            width: 80px; height: 80px; border-radius: 50%; opacity: .12;
        }
        .stat-card.blue   { background: linear-gradient(135deg, #e8f0fe 0%, #dbeafe 100%); }
        .stat-card.blue::after   { background: #1a73e8; }
        .stat-card.green  { background: linear-gradient(135deg, #e6f9f0 0%, #d1fae5 100%); }
        .stat-card.green::after  { background: #16a34a; }
        .stat-card.orange { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); }
        .stat-card.orange::after { background: #ea580c; }
        .stat-card.purple { background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); }
        .stat-card.purple::after { background: #7c3aed; }

        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.25rem; flex-shrink: 0;
        }
        .stat-icon.blue   { background: #1a73e8; color: #fff; }
        .stat-icon.green  { background: #16a34a; color: #fff; }
        .stat-icon.orange { background: #ea580c; color: #fff; }
        .stat-icon.purple { background: #7c3aed; color: #fff; }

        .stat-value { font-size: 1.85rem; font-weight: 800; line-height: 1; color: #1e293b; }
        .stat-label {
            font-size: .78rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .5px; color: #64748b; margin-top: 3px;
        }

        /* ===== PAGE HEADER ===== */
        .ph-card {
            background: #fff; border: 1px solid #e9ecef; border-radius: 14px;
            padding: 16px 20px; display: flex; align-items: center;
            justify-content: space-between; gap: 16px; flex-wrap: wrap;
            margin-bottom: 1.25rem; position: relative; overflow: hidden;
            box-shadow: 0 1px 6px rgba(0,0,0,.05);
        }
        .ph-card::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            border-radius: 14px 0 0 14px; background: #1a73e8;
        }
        .ph-left { display: flex; align-items: center; gap: 12px; }
        .ph-icon {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0; background: #dbeafe; color: #1a73e8;
        }
        .ph-title { font-size: 1.15rem; font-weight: 800; color: #1e293b; margin: 0; }
        .ph-breadcrumb { list-style: none; display: flex; gap: 6px; padding: 0; margin: 2px 0 0; }
        .ph-breadcrumb li:not(:last-child)::after { content: '/'; margin-left: 6px; color: #cbd5e1; }
        .ph-breadcrumb a { color: #1a73e8; text-decoration: none; font-size: .78rem; }
        .ph-breadcrumb .bc-active { font-size: .78rem; color: #94a3b8; }

        /* ===== FILTER & TABLE CARD ===== */
        .filter-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0, 0, 0, .07); overflow: hidden; }
        .filter-card .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 18px 24px; }
        .filter-card .card-header h5 { font-size: .95rem; font-weight: 700; color: #1e293b; }
        .filter-section { background: #fafbfc; border-bottom: 1px solid #f1f5f9; padding: 16px 24px; }

        .form-control, .form-select {
            border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: .83rem;
            padding: 7px 12px; color: #334155; background-color: #f8fafc;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1a73e8; background: #fff; box-shadow: 0 0 0 3px rgba(26, 115, 232, .12);
        }
        .form-control::placeholder { color: #94a3b8; }

        /* ===== TABLE ===== */
        .table { font-size: .83rem; margin-bottom: 0; }
        .table thead th {
            background: #f8fafc; color: #475569; font-weight: 700;
            font-size: .72rem; text-transform: uppercase; letter-spacing: .4px;
            border-bottom: 1.5px solid #eef2f6; padding: 12px 16px; white-space: nowrap;
        }
        .table tbody td { padding: 12px 16px; vertical-align: middle; border-bottom: 1px solid #f4f6f8; }
        .table tbody tr:hover { background: #fafbfc; }

        .avatar-sm { width: 38px; height: 38px; border-radius: 10px; object-fit: cover; }
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 10px; display: flex;
            align-items: center; justify-content: center; font-weight: 700; font-size: .85rem;
        }
        .name-text { font-weight: 700; color: #1e293b; font-size: .85rem; }
        .sub-text { font-size: .74rem; color: #94a3b8; }

        .badge-aktif {
            background: #dcfce7; color: #166534; font-weight: 600;
            font-size: .72rem; padding: 5px 10px; border-radius: 8px;
        }
        .badge-nonaktif {
            background: #fee2e2; color: #991b1b; font-weight: 600;
            font-size: .72rem; padding: 5px 10px; border-radius: 8px;
        }

        .btn-action {
            width: 32px; height: 32px; border-radius: 8px; border: none;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .78rem; transition: all .15s;
        }
        .btn-detail { background: #eff6ff; color: #1d4ed8; }
        .btn-detail:hover { background: #1d4ed8; color: #fff; }
        .btn-edit { background: #fff7ed; color: #c2410c; }
        .btn-edit:hover { background: #c2410c; color: #fff; }
        .btn-hapus { background: #fef2f2; color: #dc2626; }
        .btn-hapus:hover { background: #dc2626; color: #fff; }

        /* ===== EMPTY STATE ===== */
        .empty-state { padding: 48px 24px; }
        .empty-state-icon {
            width: 64px; height: 64px; border-radius: 16px; background: #f1f5f9; color: #94a3b8;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin: 0 auto 16px;
        }

        /* ===== ALERT ===== */
        .alert { border-radius: 12px; border: none; font-size: .85rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
    </style>
@endsection

@section('content')
    <div class="container">

        {{-- ── Page Header ── --}}
        <div class="ph-card mb-4">
            <div class="ph-left">
                <div class="ph-icon"><i class="fas fa-industry"></i></div>
                <div>
                    <h5 class="ph-title">Data Produsen</h5>
                    <ol class="ph-breadcrumb mb-0">
                        <li><a href="{{ route($dashboardRoute) }}">Dashboard</a></li>
                        <li><span class="bc-active">Produsen</span></li>
                    </ol>
                </div>
            </div>

            <a href="{{ route($routePrefix . '.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Tambah Produsen
            </a>
        </div>

        {{-- ── Flash Messages ── --}}
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center mb-3 gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-3 gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- ── Stat Cards ── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card blue">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon blue"><i class="fas fa-industry"></i></div>
                        <div>
                            <div class="stat-value">{{ $totalProdusen }}</div>
                            <div class="stat-label">Total Produsen</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card green">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon green"><i class="fas fa-handshake"></i></div>
                        <div>
                            <div class="stat-value">{{ $produsenAktif }}</div>
                            <div class="stat-label">Mitra Aktif</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card orange">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon orange"><i class="fas fa-user-slash"></i></div>
                        <div>
                            <div class="stat-value">{{ $produsenNonaktif }}</div>
                            <div class="stat-label">Mitra Nonaktif</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card purple">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon purple"><i class="fas fa-calendar-plus"></i></div>
                        <div>
                            <div class="stat-value">{{ $produsenBaruBulan }}</div>
                            <div class="stat-label">Baru Bulan Ini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Main Card ── --}}
        <div class="card filter-card">

            {{-- Header --}}
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i>Daftar Produsen</h5>
                <span class="text-muted" style="font-size:.78rem;">{{ $produsens->total() }} data ditemukan</span>
            </div>

            {{-- Filter --}}
            <div class="filter-section">
                <form method="GET" action="{{ route($routePrefix . '.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-6 col-sm-7">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text border-end-0 bg-white"
                                style="border-radius:10px 0 0 10px;border:1.5px solid #e2e8f0;">
                                <i class="fas fa-search text-muted" style="font-size:.75rem;"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                style="border-radius:0 10px 10px 0;" placeholder="Cari nama produsen, no. telp, alamat, email…"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-5">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                                <i class="fas fa-times me-1"></i> Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Produsen</th>
                            <th>No. Telp</th>
                            <th>Alamat</th>
                            <th>Email</th>
                            <th>Status Mitra</th>
                            <th width="110">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($produsens as $i => $produsen)
                            <tr>
                                <td class="text-muted">{{ $produsens->firstItem() + $i }}</td>

                                {{-- Nama & Avatar --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($produsen->user?->foto_profil)
                                            <img src="{{ asset('storage/' . $produsen->user->foto_profil) }}"
                                                alt="{{ $produsen->nama_produsen }}" class="avatar-sm">
                                        @else
                                            <div class="avatar-placeholder bg-primary text-primary bg-opacity-10">
                                                {{ strtoupper(substr($produsen->nama_produsen ?? 'P', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="name-text">{{ $produsen->nama_produsen ?? '-' }}</div>
                                            <div class="sub-text">&#64;{{ $produsen->user->username ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- No Telp --}}
                                <td>
                                    @if ($produsen->no_telp)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $produsen->no_telp) }}"
                                            target="_blank" class="text-decoration-none text-dark">
                                            <i class="fab fa-whatsapp text-success me-1"></i>{{ $produsen->no_telp }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- Alamat --}}
                                <td style="max-width:220px;">
                                    <span class="text-truncate d-inline-block" style="max-width:220px;" title="{{ $produsen->alamat }}">
                                        {{ $produsen->alamat ?: '-' }}
                                    </span>
                                </td>

                                {{-- Email --}}
                                <td>
                                    @if ($produsen->user?->email)
                                        <a href="mailto:{{ $produsen->user->email }}" class="text-decoration-none text-dark"
                                            style="font-size:.8rem;">
                                            <i class="fas fa-envelope text-muted me-1"></i>{{ $produsen->user->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- Status Mitra --}}
                                <td>
                                    @if ($produsen->status_mitra === 'aktif')
                                        <span class="badge-aktif"><i class="fas fa-check me-1"></i>Aktif</span>
                                    @else
                                        <span class="badge-nonaktif">Nonaktif</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route($routePrefix . '.show', $produsen->id) }}"
                                            class="btn btn-action btn-detail" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route($routePrefix . '.edit', $produsen->id) }}"
                                            class="btn btn-action btn-edit" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <button class="btn btn-action btn-hapus" data-id="{{ $produsen->id }}"
                                            data-nama="{{ $produsen->nama_produsen ?? 'produsen' }}" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <form id="form-hapus-{{ $produsen->id }}"
                                            action="{{ route($routePrefix . '.destroy', $produsen->id) }}" method="POST"
                                            class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-0 text-center">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-industry"></i></div>
                                        <div class="fw-semibold text-secondary mb-1">Belum ada data produsen</div>
                                        <div class="text-muted" style="font-size:.8rem;">
                                            Coba ubah filter atau tambahkan data baru
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($produsens->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan
                        <strong>{{ $produsens->firstItem() }}</strong>–<strong>{{ $produsens->lastItem() }}</strong>
                        dari <strong>{{ $produsens->total() }}</strong> data
                    </small>
                    {{ $produsens->links() }}
                </div>
            @endif

        </div>{{-- end .card --}}

    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.btn-hapus').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const nama = this.dataset.nama;
                swal({
                    title: 'Hapus Data?',
                    text: `Data produsen "${nama}" akan dihapus permanen.`,
                    icon: 'warning',
                    buttons: {
                        cancel: 'Batal',
                        confirm: { text: 'Ya, Hapus!', className: 'btn-danger' }
                    },
                    dangerMode: true,
                }).then(confirmed => {
                    if (confirmed) document.getElementById('form-hapus-' + id).submit();
                });
            });
        });
    </script>
@endsection
