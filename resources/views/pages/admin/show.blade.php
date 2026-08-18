@extends('layouts.user.user')

@section('title', 'Detail Admin – ' . $admin->nama)

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

    .profile-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; }
    .cover-banner { height: 90px; background: linear-gradient(135deg, #1a73e8 0%, #4f8ef7 100%); }
    .profile-avatar {
        width: 110px; height: 110px; border-radius: 18px; object-fit: cover;
        border: 4px solid #fff; margin-top: -60px; box-shadow: 0 4px 14px rgba(0,0,0,.12);
    }
    .avatar-placeholder-lg {
        width: 110px; height: 110px; border-radius: 18px; border: 4px solid #fff;
        margin-top: -60px; box-shadow: 0 4px 14px rgba(0,0,0,.12);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem; font-weight: 800; background: #dbeafe; color: #1a73e8;
    }
    .profile-name { font-size: 1.2rem; font-weight: 800; color: #1e293b; }
    .profile-role { font-size: .8rem; color: #64748b; }

    .badge-aktif { background: #dcfce7; color: #166534; font-weight: 600; font-size: .74rem; padding: 5px 12px; border-radius: 20px; }
    .badge-nonaktif { background: #fee2e2; color: #991b1b; font-weight: 600; font-size: .74rem; padding: 5px 12px; border-radius: 20px; }

    .info-card { border: none; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
    .info-card .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 16px 20px; font-weight: 700; font-size: .9rem; color: #1e293b; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-bottom: 1px solid #f4f6f8; font-size: .85rem; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #64748b; display: flex; align-items: center; gap: 8px; }
    .info-value { color: #1e293b; font-weight: 600; text-align: right; }
</style>
@endsection

@section('content')
<div class="container">

    {{-- ── Page Header ── --}}
    <div class="ph-card">
        <div class="ph-left">
            <div class="ph-icon"><i class="fas fa-user-shield"></i></div>
            <div>
                <h5 class="ph-title">Detail Admin</h5>
                <ol class="ph-breadcrumb mb-0">
                    <li><a href="{{ route('pemilik.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('pemilik.admin.index') }}">Admin</a></li>
                    <li><span class="bc-active">{{ $admin->nama }}</span></li>
                </ol>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pemilik.admin.edit', $admin->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-pencil-alt me-1"></i> Edit
            </a>
            <button type="button" class="btn btn-danger btn-sm" id="btn-hapus-detail">
                <i class="fas fa-trash-alt me-1"></i> Hapus
            </button>
            <form id="form-hapus-detail" action="{{ route('pemilik.admin.destroy', $admin->id) }}" method="POST" class="d-none">
                @csrf @method('DELETE')
            </form>
            <a href="{{ route('pemilik.admin.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3" style="border-radius:12px;border:none;font-size:.85rem;">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">

        {{-- ── Kolom Kiri: Profil ── --}}
        <div class="col-lg-4">
            <div class="card profile-card">
                <div class="cover-banner"></div>
                <div class="card-body text-center pt-0">
                    @if ($admin->user->foto_profil)
                        <img src="{{ asset('storage/' . $admin->user->foto_profil) }}"
                            class="profile-avatar" alt="{{ $admin->nama }}">
                    @else
                        <div class="avatar-placeholder-lg mx-auto">
                            {{ strtoupper(substr($admin->nama, 0, 1)) }}
                        </div>
                    @endif

                    <div class="profile-name mt-3">{{ $admin->nama }}</div>
                    <div class="profile-role mb-2">Administrator</div>

                    @if ($admin->user->is_active)
                        <span class="badge-aktif"><i class="fas fa-check-circle me-1"></i>Aktif</span>
                    @else
                        <span class="badge-nonaktif"><i class="fas fa-times-circle me-1"></i>Nonaktif</span>
                    @endif

                    <hr class="my-3">

                    <div class="text-start">
                        <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.85rem;">
                            <i class="fas fa-envelope text-muted"></i>
                            <span>{{ $admin->user->email }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2" style="font-size:.85rem;">
                            <i class="fas fa-phone text-muted"></i>
                            <span>{{ $admin->no_telp ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Kolom Kanan: Detail ── --}}
        <div class="col-lg-8">
            <div class="card info-card mb-4">
                <div class="card-header"><i class="fas fa-id-card text-primary me-2"></i>Data Admin</div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-user"></i> Nama</div>
                    <div class="info-value">{{ $admin->nama }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-hashtag"></i> NIP</div>
                    <div class="info-value">{{ $admin->nip ?: '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-phone"></i> No. Telp</div>
                    <div class="info-value">{{ $admin->no_telp ?: '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-map-marker-alt"></i> Alamat</div>
                    <div class="info-value">{{ $admin->alamat ?: '-' }}</div>
                </div>
            </div>

            <div class="card info-card mb-4">
                <div class="card-header"><i class="fas fa-user text-primary me-2"></i>Data Akun</div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-user-circle"></i> Username</div>
                    <div class="info-value">{{ $admin->user->username }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                    <div class="info-value">{{ $admin->user->email }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-user-tag"></i> Role</div>
                    <div class="info-value text-capitalize">{{ $admin->user->role }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-toggle-on"></i> Status</div>
                    <div class="info-value">
                        @if ($admin->user->is_active)
                            <span class="badge-aktif">Aktif</span>
                        @else
                            <span class="badge-nonaktif">Nonaktif</span>
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-calendar-plus"></i> Terdaftar Sejak</div>
                    <div class="info-value">{{ $admin->created_at?->translatedFormat('d F Y, H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    document.getElementById('btn-hapus-detail').addEventListener('click', function () {
        swal({
            title: 'Hapus Data?',
            text: 'Data admin "{{ $admin->nama }}" akan dihapus permanen.',
            icon: 'warning',
            buttons: {
                cancel: 'Batal',
                confirm: { text: 'Ya, Hapus!', className: 'btn-danger' }
            },
            dangerMode: true,
        }).then(confirmed => {
            if (confirmed) document.getElementById('form-hapus-detail').submit();
        });
    });
</script>
@endsection
