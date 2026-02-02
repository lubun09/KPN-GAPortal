@extends('layouts.app-sidebar')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-semibold mb-4 text-left">Detail Request ID Card</h2>
    
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Bagian Foto & Bukti Bayar (Kiri) -->
        <div class="md:w-1/3 flex flex-col items-center space-y-6">
            @if ($data->foto && $data->kategori != 'magang')
                <div class="w-full">
                    <p class="text-sm font-medium text-gray-600 mb-3">Foto</p>
                    <div class="bg-gray-100 rounded-lg p-3 shadow-inner">
                        <div class="relative w-full" style="padding-bottom: 150%;">
                            @php
                                $fotoUrl = route('idcard.photo', $data->foto);
                            @endphp
                            <img src="{{ $fotoUrl }}" 
                                 class="absolute inset-0 w-full h-full object-contain rounded-md shadow"
                                 alt="Foto ID Card"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/400x600?text=Foto+Tidak+Ditemukan';">
                        </div>
                        <div class="mt-3 text-center space-y-2">
                            <a href="{{ $fotoUrl }}" 
                               download="foto_idcard_{{ $data->nama }}.jpg"
                               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download Foto
                            </a>
                            <p class="text-xs text-gray-500">
                                File: {{ $data->foto }}<br>
                                Format: 4x6 (600x400 pixels)
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($data->kategori == 'magang')
                <div class="w-full">
                    <p class="text-sm font-medium text-gray-600 mb-3">Magang</p>
                    <div class="bg-gray-100 rounded-lg p-3 shadow-inner">
                        <div class="flex flex-col items-center justify-center p-6">
                            <svg class="w-16 h-16 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" transform="translate(0 4)"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5" transform="translate(0 8)"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-700">Kategori: Magang</p>
                            <p class="text-xs text-gray-500 mt-1">Tidak memerlukan foto</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if ($data->bukti_bayar && $data->kategori == 'ganti_kartu')
                <div class="w-full">
                    <p class="text-sm font-medium text-gray-600 mb-3">Bukti Bayar/Rusak</p>
                    <div class="bg-gray-100 rounded-lg p-3 shadow-inner">
                        @php
                            $buktiUrl = route('idcard.photo', $data->bukti_bayar);
                            $isPdf = pathinfo($data->bukti_bayar, PATHINFO_EXTENSION) == 'pdf';
                        @endphp
                        
                        <div class="relative w-full" style="padding-bottom: 150%;">
                            @if($isPdf)
                                <div class="absolute inset-0 flex flex-col items-center justify-center p-4 bg-white rounded-md">
                                    <svg class="w-16 h-16 text-red-500 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-700">File PDF</p>
                                </div>
                            @else
                                <img src="{{ $buktiUrl }}" 
                                     class="absolute inset-0 w-full h-full object-contain rounded-md shadow"
                                     alt="Bukti Bayar"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/400x600?text=File+Tidak+Ditemukan';">
                            @endif
                        </div>
                        <div class="mt-3 text-center space-y-2">
                            <a href="{{ $buktiUrl }}" 
                               download="bukti_bayar_{{ $data->nama }}.{{ $isPdf ? 'pdf' : 'jpg' }}"
                               class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download {{ $isPdf ? 'PDF' : 'File' }}
                            </a>
                            <p class="text-xs text-gray-500">
                                File: {{ $data->bukti_bayar }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Bagian Data (Kanan) -->
        <div class="md:w-2/3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- NIK -->
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">NIK</p>
                    <p class="font-medium text-gray-800">{{ $data->nik ?? '-' }}</p>
                </div>
                
                <!-- Nama -->
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">Nama</p>
                    <p class="font-medium text-gray-800">{{ $data->nama ?? '-' }}</p>
                </div>
                
                <!-- Bisnis Unit -->
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">Bisnis Unit</p>
                    <p class="font-medium text-gray-800">{{ $data->bisnis_unit_nama ?? '-' }}</p>
                </div>
                
                <!-- Kategori -->
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">Kategori</p>
                    <p class="font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $data->kategori) ?? '-' }}</p>
                </div>
                
                <!-- Status -->
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">Status</p>
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full 
                        {{ $data->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                           ($data->status == 'approved' ? 'bg-green-100 text-green-800' : 
                           'bg-red-100 text-red-800') }}">
                        {{ ucfirst($data->status) }}
                    </span>
                </div>
                
                <!-- Tanggal Join (jika bukan magang) -->
                @if (!empty($data->tanggal_join) && $data->kategori != 'magang')
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">Tanggal Join</p>
                    <p class="font-medium text-gray-800">{{ date('d-m-Y', strtotime($data->tanggal_join)) }}</p>
                </div>
                @endif
                
                <!-- Untuk Magang -->
                @if ($data->kategori == 'magang')
                    <!-- Nomor Kartu -->
                    @if (!empty($data->nomor_kartu))
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500">Nomor Kartu</p>
                        <p class="font-medium text-gray-800 font-mono">{{ $data->nomor_kartu }}</p>
                    </div>
                    @endif
                    
                    <!-- Masa Berlaku -->
                    @if (!empty($data->masa_berlaku))
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500">Masa Berlaku</p>
                        <p class="font-medium text-gray-800">{{ date('d-m-Y', strtotime($data->masa_berlaku)) }}</p>
                    </div>
                    @endif
                    
                    <!-- Sampai Tanggal -->
                    @if (!empty($data->sampai_tanggal))
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500">Sampai Tanggal</p>
                        <p class="font-medium text-gray-800">{{ date('d-m-Y', strtotime($data->sampai_tanggal)) }}</p>
                    </div>
                    @endif
                    
                    <!-- Durasi Magang -->
                    @if (!empty($data->masa_berlaku) && !empty($data->sampai_tanggal))
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500">Durasi Magang</p>
                        @php
                            $start = new DateTime($data->masa_berlaku);
                            $end = new DateTime($data->sampai_tanggal);
                            $interval = $start->diff($end);
                            $months = $interval->y * 12 + $interval->m;
                            $days = $interval->d;
                        @endphp
                        <p class="font-medium text-gray-800">
                            {{ $months }} bulan {{ $days > 0 ? $days . ' hari' : '' }}
                        </p>
                    </div>
                    @endif
                @endif
                
                <!-- Tanggal Request -->
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">Tanggal Request</p>
                    <p class="font-medium text-gray-800">{{ date('d-m-Y H:i', strtotime($data->created_at)) }}</p>
                </div>
                
                <!-- Diajukan Oleh -->
                @if (!empty($data->user_id))
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">Diajukan Oleh</p>
                    <p class="font-medium text-gray-800">
                        {{ $data->user_name ?? 'User #' . $data->user_id }}
                    </p>
                </div>
                @endif
                
                <!-- Disetujui Oleh -->
                @if (!empty($data->approved_by_name))
                <div class="space-y-1">
                    <p class="text-sm text-gray-500">Disetujui Oleh</p>
                    <p class="font-medium text-gray-800">{{ $data->approved_by_name }}</p>
                    <p class="text-xs text-gray-500">{{ date('d-m-Y H:i', strtotime($data->approved_at)) }}</p>
                </div>
                @endif
                
                <!-- Ditolak Oleh -->
                @if ($data->status == 'rejected' && !empty($data->rejected_by_name))
                <div class="space-y-1 md:col-span-2">
                    <p class="text-sm text-gray-500">Ditolak Oleh</p>
                    <p class="font-medium text-gray-800">{{ $data->rejected_by_name }}</p>
                    <p class="text-xs text-gray-500">
                        @if($data->rejected_at)
                            {{ date('d-m-Y H:i', strtotime($data->rejected_at)) }}
                        @endif
                    </p>
                    
                    @if (!empty($data->rejection_reason))
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 mb-1">Alasan Penolakan:</p>
                        <div class="bg-red-50 border border-red-200 rounded p-3 mt-1">
                            <p class="text-sm text-red-800">{{ $data->rejection_reason }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
                
                <!-- Keterangan -->
                @if (!empty($data->keterangan))
                <div class="md:col-span-2 space-y-1">
                    <p class="text-sm text-gray-500">Lantai Kerja</p>
                    <div class="bg-gray-50 border border-gray-200 rounded p-3">
                        <p class="font-medium text-gray-800">{{ $data->keterangan }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Tombol Action untuk Approval/Reject -->
    @if($isPending && $canProses)
    <div class="mt-8 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Action</h3>
        <div class="bg-gray-50 rounded-lg p-6">
            <!-- Form Approve -->
            <form action="{{ route('idcard.approve', $data->id) }}" method="POST" class="mb-6" id="approveForm">
                @csrf
                @if($data->kategori == 'magang')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor Kartu (wajib untuk Magang) *
                        </label>
                        <input type="text" name="nomor_kartu" 
                               class="w-full md:w-1/2 px-3 py-2 border border-gray-300 rounded-md"
                               value="{{ $data->nomor_kartu ?? '' }}"
                               placeholder="Masukkan nomor kartu"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Contoh: MAG20240115001</p>
                    </div>
                @endif
                
                <button type="button" 
                        onclick="confirmApprove()"
                        class="inline-flex items-center px-5 py-2.5 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approve Request
                </button>
                <p class="text-xs text-gray-500 mt-2">Status akan berubah menjadi "Approved"</p>
            </form>
            
            <!-- Form Reject -->
            <form action="{{ route('idcard.reject', $data->id) }}" method="POST" id="rejectForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Penolakan *
                    </label>
                    <textarea name="rejection_reason" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md"
                              placeholder="Masukkan alasan penolakan (minimal 5 karakter)"
                              required></textarea>
                    <p class="text-xs text-gray-500 mt-1">Wajib diisi minimal 5 karakter</p>
                </div>
                
                <button type="button" 
                        onclick="confirmReject()"
                        class="inline-flex items-center px-5 py-2.5 bg-red-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reject Request
                </button>
                <p class="text-xs text-gray-500 mt-2">Status akan berubah menjadi "Rejected"</p>
            </form>
        </div>
    </div>
    @elseif($isPending && !$canProses)
    <div class="mt-8 pt-6 border-t border-gray-200">
        <!-- <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                
            </div>
        </div> -->
    </div>
    @endif
    
    <!-- Logs Activity -->
    @if(isset($logs) && $logs->count() > 0)
    <div class="mt-8 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Activity Logs</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="space-y-3">
                @foreach($logs as $log)
                <div class="flex items-start gap-3 p-3 bg-white rounded border">
                    <div class="flex-shrink-0">
                        @switch($log->action)
                            @case('created')
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </div>
                                @break
                            @case('approved')
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                @break
                            @case('rejected')
                                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                @break
                            @default
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                        @endswitch
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 capitalize">{{ $log->action }}</p>
                        <p class="text-sm text-gray-600">{{ $log->notes }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <p class="text-xs text-gray-500">by {{ $log->action_by_name ?? 'System' }}</p>
                            <span class="text-xs text-gray-400">•</span>
                            <p class="text-xs text-gray-500">{{ date('d-m-Y H:i', strtotime($log->created_at)) }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    
    <!-- Tombol Kembali -->
    <div class="mt-6 pt-4 border-t">
        <a href="{{ route('idcard') }}" 
           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke List ID Card
        </a>
    </div>
</div>

<script>
function confirmApprove() {
    const kategori = '{{ $data->kategori }}';
    
    if (kategori === 'magang') {
        const nomorKartu = document.querySelector('input[name="nomor_kartu"]')?.value.trim();
        if (!nomorKartu) {
            alert('Nomor kartu wajib diisi untuk kategori Magang!');
            return false;
        }
    }
    
    if (confirm('Apakah Anda yakin ingin menyetujui request ini?')) {
        document.getElementById('approveForm').submit();
    }
}

function confirmReject() {
    const reason = document.querySelector('textarea[name="rejection_reason"]').value.trim();
    if (reason.length < 5) {
        alert('Alasan penolakan minimal 5 karakter!');
        return false;
    }
    
    if (confirm('Apakah Anda yakin ingin menolak request ini?')) {
        document.getElementById('rejectForm').submit();
    }
}
</script>
@endsection