@extends('layouts.app-sidebar')

@section('content')
<div class="space-y-6 text-sm text-gray-800 font-sans">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Detail Dokumen</h2>
            <p class="text-xs text-gray-500">{{ $document->nomor_dokumen }}</p>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('track-r.index') }}"
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200">
                Kembali
            </a>
            <a href="{{ route('track-r.pdf', $document->id) }}"
               target="_blank"
               class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-semibold hover:bg-red-200">
                <i class="fas fa-file-pdf mr-2"></i> PDF
            </a>
        </div>
    </div>

    {{-- INFO DOKUMEN --}}
    <div class="bg-white rounded-xl border p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="text-xs text-gray-500">Judul Dokumen</label>
                    <p class="text-sm font-medium">{{ $document->judul }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Pengirim</label>
                    <p class="text-sm font-medium">{{ $document->pengirim->name ?? 'Tidak diketahui' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Dikirim</label>
                    <p class="text-sm font-medium">
                        {{ $document->created_at->format('d M Y H:i') }}
                    </p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="text-xs text-gray-500">Penerima</label>
                    <p class="text-sm font-medium">{{ $document->penerima->name ?? 'Tidak diketahui' }}</p>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    @php
                        $badge = match(strtolower($document->status)){
                            'draft' => 'bg-gray-100 text-gray-700',
                            'dikirim' => 'bg-blue-100 text-blue-700',
                            'diterima' => 'bg-green-100 text-green-700',
                            'ditolak' => 'bg-red-100 text-red-700',
                            'diproses' => 'bg-orange-100 text-orange-700',
                            'diteruskan' => 'bg-purple-100 text-purple-700',
                            'selesai' => 'bg-green-100 text-green-700',
                            default => 'bg-gray-100 text-gray-700'
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                        {{ strtoupper($document->status) }}
                    </span>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Keterangan</label>
                    <p class="text-sm font-medium">{{ $document->keterangan ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- FOTO DOKUMEN --}}
    @if($document->fotos && $document->fotos->count() > 0)
    <div class="bg-white rounded-xl border p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Foto Dokumen</h3>
                <p class="text-xs text-gray-500">
                    <i class="fas fa-shield-alt mr-1"></i> Foto tidak dapat dihapus untuk menjaga keaslian dokumen
                </p>
            </div>
            <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                {{ $document->fotos->count() }} file
            </span>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($document->fotos as $foto)
            <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3 mb-3">
                    @php
                        $icon = match(strtolower($foto->tipe)) {
                            'pdf' => 'fas fa-file-pdf',
                            'doc', 'docx' => 'fas fa-file-word',
                            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp' => 'fas fa-file-image',
                            default => 'fas fa-file'
                        };
                        $iconColor = match(strtolower($foto->tipe)) {
                            'pdf' => 'text-red-600',
                            'doc', 'docx' => 'text-blue-600',
                            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp' => 'text-green-600',
                            default => 'text-gray-600'
                        };
                    @endphp
                    <i class="{{ $icon }} text-lg {{ $iconColor }}"></i>
                    <div class="flex-1">
                        <p class="text-sm font-medium truncate">{{ $foto->nama_file }}</p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($foto->ukuran / 1024, 1) }} KB • {{ strtoupper($foto->tipe) }}
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <a href="{{ route('track-r.foto.download', [$document->id, $foto->id]) }}"
                       class="w-full text-center py-2 bg-blue-50 text-blue-700 
                              rounded text-sm font-medium hover:bg-blue-100">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- LOGS --}}
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Tracking</h3>
        
        <div class="space-y-4">
            @foreach($document->logs as $log)
            <div class="flex gap-4 p-4 border rounded-lg hover:bg-gray-50">
                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full
                            flex items-center justify-center">
                    @php
                        $logIcon = match($log->aksi) {
                            'kirim' => 'paper-plane',
                            'terima' => 'check',
                            'tolak' => 'times',
                            'teruskan' => 'share',
                            default => 'history'
                        };
                        $logColor = match($log->aksi) {
                            'kirim' => 'text-blue-600',
                            'terima' => 'text-green-600',
                            'tolak' => 'text-red-600',
                            'teruskan' => 'text-purple-600',
                            default => 'text-gray-600'
                        };
                    @endphp
                    <i class="fas fa-{{ $logIcon }} {{ $logColor }}"></i>
                </div>
                
                <div class="flex-1">
                    <div class="flex justify-between">
                        <p class="font-medium text-gray-800">
                            {{ ucfirst($log->aksi) }} oleh {{ $log->dariUser->name ?? 'System' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $log->created_at->format('d M Y H:i') }}
                        </p>
                    </div>
                    
                    @if($log->catatan)
                        <p class="text-sm text-gray-600 mt-1">{{ $log->catatan }}</p>
                    @endif
                    
                    @if($log->ke_user_id && $log->keUser)
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-arrow-right mr-1"></i> Kepada: {{ $log->keUser->name }}
                        </p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ACTION BUTTONS (UNTUK PENERIMA SAAT DOKUMEN DIKIRIM) --}}
    @if(auth()->id() == $document->penerima_id && $document->status == 'dikirim')
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi</h3>
        
        <div class="flex flex-col sm:flex-row gap-4">
            {{-- TERIMA --}}
            <form action="{{ route('track-r.terima', $document->id) }}" 
                  method="POST" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full py-3 bg-green-600 text-white rounded-lg
                               font-semibold hover:bg-green-700 transition">
                    <i class="fas fa-check mr-2"></i> Terima Dokumen
                </button>
            </form>
            
            {{-- TOLAK --}}
            <button type="button" 
                    onclick="toggleTolakForm()"
                    class="flex-1 py-3 bg-red-600 text-white rounded-lg
                           font-semibold hover:bg-red-700 transition">
                <i class="fas fa-times mr-2"></i> Tolak Dokumen
            </button>
            
            {{-- TERUSKAN --}}
            <button type="button" 
                    onclick="toggleTeruskanForm()"
                    class="flex-1 py-3 bg-blue-600 text-white rounded-lg
                           font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-share mr-2"></i> Teruskan
            </button>
        </div>
        
        {{-- FORM TOLAK --}}
        <div id="tolakForm" class="hidden mt-6 p-4 border rounded-lg bg-red-50">
            <form action="{{ route('track-r.tolak', $document->id) }}" method="POST">
                @csrf
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea name="catatan" rows="3" required
                          class="w-full border border-red-300 rounded-lg px-4 py-2
                                 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                          placeholder="Berikan alasan penolakan..."></textarea>
                
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" 
                            onclick="toggleTolakForm()"
                            class="px-4 py-2 text-gray-700 hover:text-gray-900">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg
                                   text-sm font-semibold hover:bg-red-700">
                        <i class="fas fa-times mr-2"></i> Tolak Dokumen
                    </button>
                </div>
            </form>
        </div>
        
        {{-- FORM TERUSKAN --}}
        <div id="teruskanForm" class="hidden mt-6 p-4 border rounded-lg bg-blue-50">
            <form action="{{ route('track-r.teruskan', $document->id) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Teruskan ke <span class="text-red-500">*</span>
                        </label>
                        <select name="penerima_id" required
                                class="w-full border border-blue-300 rounded-lg px-4 py-2
                                       text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Penerima --</option>
                            @php
                                $users = App\Models\User::where('id', '!=', auth()->id())
                                                       ->where('id', '!=', $document->pengirim_id)
                                                       ->orderBy('name')->get();
                            @endphp
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan (Opsional)
                        </label>
                        <textarea name="catatan" rows="3"
                                  class="w-full border border-blue-300 rounded-lg px-4 py-2
                                         text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Tambahkan catatan..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" 
                            onclick="toggleTeruskanForm()"
                            class="px-4 py-2 text-gray-700 hover:text-gray-900">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg
                                   text-sm font-semibold hover:bg-blue-700">
                        <i class="fas fa-share mr-2"></i> Teruskan Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- NOTIFICATION --}}
    @if(auth()->id() != $document->pengirim_id && auth()->id() != $document->penerima_id)
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400 mt-0.5"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Akses Ditolak</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>Anda tidak memiliki akses untuk melihat dokumen ini. Hanya pengirim dan penerima yang berhak melihat.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<script>
function toggleTolakForm() {
    const tolakForm = document.getElementById('tolakForm');
    const teruskanForm = document.getElementById('teruskanForm');
    
    tolakForm.classList.toggle('hidden');
    if (!teruskanForm.classList.contains('hidden')) {
        teruskanForm.classList.add('hidden');
    }
}

function toggleTeruskanForm() {
    const tolakForm = document.getElementById('tolakForm');
    const teruskanForm = document.getElementById('teruskanForm');
    
    teruskanForm.classList.toggle('hidden');
    if (!tolakForm.classList.contains('hidden')) {
        tolakForm.classList.add('hidden');
    }
}

// Close forms when clicking outside
document.addEventListener('click', function(event) {
    const tolakForm = document.getElementById('tolakForm');
    const teruskanForm = document.getElementById('teruskanForm');
    const tolakBtn = document.querySelector('[onclick="toggleTolakForm()"]');
    const teruskanBtn = document.querySelector('[onclick="toggleTeruskanForm()"]');
    
    if (!tolakForm.classList.contains('hidden') && 
        !tolakForm.contains(event.target) && 
        event.target !== tolakBtn && 
        !tolakBtn.contains(event.target)) {
        tolakForm.classList.add('hidden');
    }
    
    if (!teruskanForm.classList.contains('hidden') && 
        !teruskanForm.contains(event.target) && 
        event.target !== teruskanBtn && 
        !teruskanBtn.contains(event.target)) {
        teruskanForm.classList.add('hidden');
    }
});
</script>
@endsection