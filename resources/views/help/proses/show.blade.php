@extends('layouts.app-sidebar')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Detail Tiket</h2>
            <p class="text-gray-600">{{ $tiket->judul }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('help.proses.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium rounded-lg border border-gray-300 transition-colors">
                <i class="fas fa-arrow-left mr-2.5"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 text-green-500 hover:text-green-700" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Ticket Info & Chat -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Information Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Informasi Tiket</h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-4 items-center">
                        <!-- Status Badge -->
                        @php
                            $statusConfig = [
                                'OPEN' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'icon' => 'circle'],
                                'ON_PROCESS' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'cog'],
                                'WAITING' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'icon' => 'clock'],
                                'DONE' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'check'],
                                'CLOSED' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300', 'icon' => 'lock']
                            ];
                            $config = $statusConfig[$tiket->status] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'icon' => 'question'];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $config['bg'] }} {{ $config['text'] }} border {{ $config['border'] }}">
                            <i class="fas fa-{{ $config['icon'] }} mr-2"></i>
                            {{ $tiket->status }}
                        </span>

                        <!-- Priority Badge -->
                        @php
                            $priorityColors = [
                                'URGENT' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200'],
                                'HIGH' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200'],
                                'MEDIUM' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200'],
                                'LOW' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300']
                            ];
                            $priorityConfig = $priorityColors[$tiket->prioritas] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-200'];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $priorityConfig['bg'] }} {{ $priorityConfig['text'] }} border {{ $priorityConfig['border'] }}">
                            <i class="fas fa-exclamation-circle mr-1.5"></i>
                            {{ $tiket->prioritas }}
                        </span>

                        <!-- Ticket ID -->
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-hashtag mr-1.5"></i> {{ $tiket->nomor_tiket ?? 'TKT-' . $tiket->id }}
                        </div>

                        <!-- Date -->
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-calendar-alt mr-1.5"></i> {{ $tiket->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h4 class="font-medium text-gray-900 mb-2">{{ $tiket->judul }}</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-line">{{ $tiket->deskripsi }}</p>
                        </div>
                    </div>

                    <!-- People -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex flex-wrap gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Pelapor</label>
                                <div class="flex items-center">
                                    @php
                                        // CARI PELANGGAN
                                        $pelanggan = \App\Models\Pelanggan::with('user')->find($tiket->pelapor_id);
                                        
                                        if ($pelanggan) {
                                            // AMBIL NAMA DARI USER JIKA ADA, JIKA TIDAK DARI PELANGGAN
                                            if ($pelanggan->user && $pelanggan->user->name) {
                                                $pelaporName = $pelanggan->user->name;
                                            } else {
                                                $pelaporName = $pelanggan->nama ?? "Pelanggan";
                                            }
                                        } else {
                                            $pelaporName = "ID: {$tiket->pelapor_id}";
                                        }
                                        
                                        $pelaporInitial = substr($pelaporName, 0, 1);
                                        $pelaporEmail = $pelanggan->user->email ?? '';
                                    @endphp
                                    
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center font-medium text-blue-700 mr-3">
                                        {{ $pelaporInitial }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $pelaporName }}</p>
                                        <p class="text-sm text-gray-500">{{ $pelaporEmail }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($tiket->ditugaskanKe)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Ditugaskan ke</label>
                                <div class="flex items-center">
                                    @php
                                        $ditugaskanName = $tiket->ditugaskanKe->user->name ?? 'N/A';
                                        $ditugaskanInitial = substr($ditugaskanName, 0, 1);
                                        $ditugaskanEmail = $tiket->ditugaskanKe->user->email ?? '';
                                    @endphp
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center font-medium text-green-700 mr-3">
                                        {{ $ditugaskanInitial }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $ditugaskanName }}</p>
                                        <p class="text-sm text-gray-500">{{ $ditugaskanEmail }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Style Chat -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Diskusi Tiket</h3>
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-users mr-2"></i>
                            <span>{{ $tiket->komentar->count() }} pesan</span>
                        </div>
                    </div>
                </div>
                
                <!-- Chat Container -->
                <div class="p-4 bg-gray-50" style="height: 500px; overflow-y: auto;" id="chatContainer">
                    @forelse($tiket->komentar as $komentar)
                        <!-- System Message -->
                        @if($komentar->pesan_sistem)
                            <div class="text-center my-4">
                                <div class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                    <i class="fas fa-robot mr-2"></i>
                                    @php
                                        $systemUserName = $komentar->pengguna && $komentar->pengguna->user 
                                            ? $komentar->pengguna->user->name 
                                            : 'System';
                                    @endphp
                                    {{ $systemUserName }} • {{ $komentar->created_at->format('H:i') }}
                                </div>
                                <div class="mt-2 text-sm text-gray-600 bg-white p-3 rounded-lg border border-gray-200 max-w-md mx-auto">
                                    {{ $komentar->komentar }}
                                </div>
                            </div>
                        @else
                            <!-- User Message -->
                            @php
                                $currentUserId = auth()->user()->pelanggan->id_pelanggan;
                                $penggunaName = $komentar->pengguna && $komentar->pengguna->user 
                                    ? $komentar->pengguna->user->name 
                                    : 'User';
                                $penggunaInitial = substr($penggunaName, 0, 1);
                                $isOwnMessage = $komentar->pengguna_id === $currentUserId;
                            @endphp
                            <div class="flex items-start mb-4 {{ $isOwnMessage ? 'justify-end' : '' }}">
                                @if(!$isOwnMessage)
                                    <!-- Other user's message (left) -->
                                    <div class="flex-shrink-0 mr-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center font-medium text-blue-700">
                                            {{ $penggunaInitial }}
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="{{ $isOwnMessage ? 'max-w-[70%]' : 'max-w-[70%]' }}">
                                    <div class="flex items-center mb-1 {{ $isOwnMessage ? 'justify-end' : '' }}">
                                        <span class="text-xs text-gray-500">
                                            {{ $penggunaName }}
                                        </span>
                                        <span class="text-xs text-gray-400 mx-2">•</span>
                                        <span class="text-xs text-gray-400">
                                            {{ $komentar->created_at->format('H:i') }}
                                        </span>
                                    </div>
                                    
                                    <div class="{{ $isOwnMessage ? 'bg-green-100' : 'bg-white' }} p-3 rounded-2xl {{ $isOwnMessage ? 'rounded-tr-none' : 'rounded-tl-none' }} border {{ $isOwnMessage ? 'border-green-200' : 'border-gray-200' }}">
                                        <p class="text-gray-800 whitespace-pre-line">{{ $komentar->komentar }}</p>
                                        
                                        <!-- Lampiran dari komentar -->
                                        @php
                                            // Cari lampiran yang terkait dengan komentar ini berdasarkan waktu
                                            $komentarLampiran = $tiket->lampiran->where('pengguna_id', $komentar->pengguna_id)
                                                ->where('created_at', '>=', $komentar->created_at->subMinute(1))
                                                ->where('created_at', '<=', $komentar->created_at->addMinute(1))
                                                ->first();
                                        @endphp
                                        @if($komentarLampiran)
                                            <div class="mt-2 pt-2 border-t border-gray-200">
                                                <div class="flex items-center">
                                                    <i class="fas fa-paperclip text-xs text-gray-500 mr-2"></i>
                                                    @php
                                                        $iconClass = '';
                                                        if (str_contains($komentarLampiran->tipe_file, 'image')) {
                                                            $iconClass = 'fas fa-image text-blue-600';
                                                        } elseif (str_contains($komentarLampiran->tipe_file, 'pdf')) {
                                                            $iconClass = 'fas fa-file-pdf text-red-600';
                                                        } elseif (str_contains($komentarLampiran->tipe_file, 'word')) {
                                                            $iconClass = 'fas fa-file-word text-blue-600';
                                                        } else {
                                                            $iconClass = 'fas fa-file text-gray-600';
                                                        }
                                                    @endphp
                                                    {{-- GUNAKAN URL LAMA YANG BERFUNGSI --}}
                                                    @if(str_contains($komentarLampiran->tipe_file, 'image'))
                                                        <button onclick="previewFile('{{ $komentarLampiran->id }}')" 
                                                                class="text-sm text-gray-700 hover:text-blue-600 flex items-center mr-2"
                                                                title="View Image">
                                                            <i class="{{ $iconClass }} mr-2"></i>
                                                            {{ $komentarLampiran->nama_file }}
                                                        </button>
                                                        <a href="/help/tiket/lampiran/{{ $komentarLampiran->id }}/download" 
                                                           class="text-xs text-gray-500 hover:text-blue-600"
                                                           title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @else
                                                        <a href="/help/tiket/lampiran/{{ $komentarLampiran->id }}/download" 
                                                           class="text-sm text-gray-700 hover:text-blue-600 flex items-center">
                                                            <i class="{{ $iconClass }} mr-2"></i>
                                                            {{ $komentarLampiran->nama_file }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($isOwnMessage)
                                        <!-- Read status for own messages -->
                                        <div class="text-right mt-1">
                                            <span class="text-xs text-gray-400">
                                                <i class="fas fa-check-double"></i>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($isOwnMessage)
                                    <!-- Own message (right) -->
                                    @php
                                        $currentUser = auth()->user();
                                        $currentUserName = $currentUser->name ?? 'You';
                                        $currentUserInitial = substr($currentUserName, 0, 1);
                                    @endphp
                                    <div class="flex-shrink-0 ml-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center font-medium text-green-700">
                                            {{ $currentUserInitial }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @empty
                        <!-- Empty chat -->
                        <div class="flex flex-col items-center justify-center h-96 text-gray-400">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                <i class="fas fa-comments text-2xl"></i>
                            </div>
                            <p class="text-lg font-medium text-gray-500">Belum ada diskusi</p>
                            <p class="text-sm text-gray-400 mt-2">Mulai percakapan tentang tiket ini</p>
                        </div>
                    @endforelse
                </div>

                <!-- Chat Input -->
                <div class="border-t border-gray-200 p-4">
                    <form action="{{ route('help.proses.add-komentar', $tiket) }}" method="POST" enctype="multipart/form-data" id="chatForm">
                        @csrf
                        <div class="space-y-3">
                            <textarea name="komentar" 
                                    id="chatInput"
                                    rows="2"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                    placeholder="Ketik pesan..."
                                    required></textarea>
                            
                            <div class="flex justify-between items-center">
                                <div class="flex items-center">
                                    <input type="file" 
                                        id="chat-lampiran" 
                                        name="lampiran[]" 
                                        multiple
                                        class="hidden"
                                        accept="image/*,.pdf,.doc,.docx">
                                    <button type="button" 
                                            onclick="document.getElementById('chat-lampiran').click()"
                                            class="inline-flex items-center text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100">
                                        <i class="fas fa-paperclip mr-2"></i> Lampirkan File
                                    </button>
                                    <span id="file-count" class="text-xs text-gray-500 ml-2"></span>
                                    <div id="selected-files" class="hidden ml-2">
                                        <div class="flex flex-wrap gap-2">
                                            <!-- Selected files will be added here -->
                                        </div>
                                    </div>
                                </div>
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                    <i class="fas fa-paper-plane mr-2"></i> Kirim
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
            <!-- Actions Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Aksi Tiket</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @if($tiket->status === 'OPEN')
                            <form action="{{ route('help.proses.take', $tiket) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors"
                                        onclick="return confirm('Ambil tiket ini untuk diproses?')">
                                    <i class="fas fa-hand-paper mr-2.5"></i> Ambil Tiket
                                </button>
                            </form>
                        @endif

                        @if($tiket->status === 'ON_PROCESS')
                            <!-- Minta Info Button - Pindah ke Diskusi Tiket -->
                            <!-- Selesaikan Tiket -->
                            <button type="button" 
                                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#completeModal">
                                <i class="fas fa-check-circle mr-2.5"></i> Selesaikan Tiket
                            </button>
                        @endif

                        @if($tiket->status === 'DONE')
                            <button type="button" 
                                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-800 hover:bg-gray-900 text-white font-medium rounded-lg transition-colors"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#closeModal">
                                <i class="fas fa-times-circle mr-2.5"></i> Tutup Tiket
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Attachments Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Lampiran</h3>
                        <span class="text-sm text-gray-500">{{ $tiket->lampiran->count() }} file</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($tiket->lampiran as $lampiran)
                            @php
                                $lampiranUser = \App\Models\Pelanggan::find($lampiran->pengguna_id);
                                $uploaderName = $lampiranUser && $lampiranUser->user 
                                    ? $lampiranUser->user->name 
                                    : 'Unknown';
                            @endphp
                            <div class="group flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
                                <div class="flex-shrink-0 mr-3">
                                    @if(str_contains($lampiran->tipe_file, 'image'))
                                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                                            <i class="fas fa-image text-blue-600"></i>
                                        </div>
                                    @elseif(str_contains($lampiran->tipe_file, 'pdf'))
                                        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                                            <i class="fas fa-file-pdf text-red-600"></i>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <i class="fas fa-file text-gray-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $lampiran->nama_file }}</p>
                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                        <span class="mr-3">{{ $lampiran->created_at->format('d/m/Y') }}</span>
                                        <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-600">{{ $lampiran->tipe }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">Diunggah oleh: {{ $uploaderName }}</p>
                                </div>
                                <div class="flex-shrink-0 ml-2 flex items-center space-x-3">
                                    {{-- GUNAKAN URL LAMA YANG BERFUNGSI --}}
                                    @if(str_contains($lampiran->tipe_file, 'image'))
                                        <button onclick="previewFile('{{ $lampiran->id }}')" 
                                                class="text-gray-400 hover:text-blue-600"
                                                title="View Image">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    <a href="/help/tiket/lampiran/{{ $lampiran->id }}/download" 
                                       class="text-gray-400 hover:text-gray-600"
                                       title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-full mb-4">
                                    <i class="fas fa-paperclip text-gray-400"></i>
                                </div>
                                <p class="text-gray-500">Tidak ada lampiran</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Status History Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Riwayat Status</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($tiket->logStatus as $log)
                            <div class="relative pl-8 pb-4 last:pb-0">
                                @if(!$loop->last)
                                    <div class="absolute left-3 top-3 bottom-0 w-0.5 bg-gray-200"></div>
                                @endif
                                
                                <div class="absolute left-0 top-1 w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-exchange-alt text-blue-600 text-xs"></i>
                                </div>
                                
                                <div>
                                    <div class="flex justify-between items-start mb-1">
                                        @php
                                            $logUserName = $log->pengguna && $log->pengguna->user 
                                                ? $log->pengguna->user->name 
                                                : 'System';
                                        @endphp
                                        <p class="font-medium text-gray-900">{{ $logUserName }}</p>
                                        <span class="text-xs text-gray-500">{{ $log->created_at->format('H:i') }}</span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-700">{{ $log->status_lama }}</span>
                                        <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                        <span class="px-2 py-0.5 bg-blue-100 rounded text-blue-700">{{ $log->status_baru }}</span>
                                    </div>
                                    @if($log->catatan)
                                        <div class="mt-2 text-sm text-gray-600 bg-gray-50 p-2 rounded">
                                            {{ $log->catatan }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-full mb-4">
                                    <i class="fas fa-history text-gray-400"></i>
                                </div>
                                <p class="text-gray-500">Belum ada riwayat</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Selesaikan Tiket Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('help.proses.complete', $tiket) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-lg font-semibold text-gray-800">Selesaikan Tiket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Penyelesaian</label>
                        <textarea name="catatan" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  rows="4" 
                                  required 
                                  placeholder="Tulis penjelasan penyelesaian tiket..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" 
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg border border-gray-300 transition-colors"
                            data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Selesaikan Tiket
                    </button>
                </div>
            </form>
        </div>
</div>

<!-- Tutup Tiket Modal -->
<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('help.proses.close', $tiket) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-lg font-semibold text-gray-800">Tutup Tiket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                            <div>
                                <p class="text-sm text-blue-800 font-medium">Informasi Penting</p>
                                <p class="text-sm text-blue-700 mt-1">Tiket akan ditutup secara permanen dan tidak dapat diubah lagi.</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-700">Apakah Anda yakin ingin menutup tiket ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" 
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg border border-gray-300 transition-colors"
                            data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2.5 bg-gray-800 hover:bg-gray-900 text-white font-medium rounded-lg transition-colors">
                        Ya, Tutup Tiket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.3s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
        
        // Auto-focus modal input
        const modals = ['completeModal', 'closeModal'];
        modals.forEach(function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('shown.bs.modal', function () {
                    const textarea = this.querySelector('textarea');
                    if (textarea) {
                        textarea.focus();
                        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                    }
                });
            }
        });
        
        // Auto-scroll chat to bottom
        const chatContainer = document.getElementById('chatContainer');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        // Auto-resize textarea
        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            chatInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            
            // Submit on Ctrl+Enter
            chatInput.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    document.getElementById('chatForm').submit();
                }
            });
        }

        // File upload for chat
        const chatFileInput = document.getElementById('chat-lampiran');
        const fileCountSpan = document.getElementById('file-count');
        const selectedFilesDiv = document.getElementById('selected-files');

        if (chatFileInput) {
            chatFileInput.addEventListener('change', function() {
                const files = this.files;
                const count = files.length;
                
                if (fileCountSpan) {
                    fileCountSpan.textContent = count > 0 ? `${count} file dipilih` : '';
                }
                
                if (selectedFilesDiv) {
                    selectedFilesDiv.innerHTML = '';
                    if (count > 0) {
                        selectedFilesDiv.classList.remove('hidden');
                        
                        Array.from(files).forEach((file, index) {
                            const fileDiv = document.createElement('div');
                            fileDiv.className = 'flex items-center bg-blue-50 px-3 py-1 rounded-lg';
                            fileDiv.innerHTML = `
                                <span class="text-xs text-blue-700 truncate max-w-xs">${file.name}</span>
                                <button type="button" onclick="removeFile(${index})" class="ml-2 text-blue-700 hover:text-blue-900">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            `;
                            selectedFilesDiv.querySelector('.flex').appendChild(fileDiv);
                        });
                    } else {
                        selectedFilesDiv.classList.add('hidden');
                    }
                }
            });
        }
    });

    // Remove file from chat input
    function removeFile(index) {
        const dt = new DataTransfer();
        const input = document.getElementById('chat-lampiran');
        const { files } = input;
        
        for (let i = 0; i < files.length; i++) {
            if (index !== i) {
                dt.items.add(files[i]);
            }
        }
        
        input.files = dt.files;
        
        // Trigger change event
        const event = new Event('change');
        input.dispatchEvent(event);
    }

    // Function untuk preview gambar - GUNAKAN URL LAMA
    function previewFile(id) {
        // URL langsung untuk preview gambar (sama seperti di kode lama)
        const previewUrl = "/help/tiket/lampiran/" + id + "/preview";
        console.log('Opening preview:', previewUrl);
        window.open(previewUrl, '_blank', 'noopener,noreferrer');
    }
</script>
@endsection

<style>
    /* WhatsApp Style Chat */
    #chatContainer::-webkit-scrollbar {
        width: 6px;
    }
    
    #chatContainer::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    #chatContainer::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    #chatContainer::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* Message bubbles */
    .bg-green-100 {
        background-color: #d1fae5;
    }
    
    .border-green-200 {
        border-color: #a7f3d0;
    }
    
    /* Darwinbox colors */
    .rounded-xl {
        border-radius: 0.75rem;
    }
    
    .bg-amber-50 { background-color: #fffbeb; }
    .bg-blue-50 { background-color: #eff6ff; }
    .bg-orange-50 { background-color: #fff7ed; }
    .bg-emerald-50 { background-color: #ecfdf5; }
    .bg-gray-100 { background-color: #f3f4f6; }
    .bg-red-50 { background-color: #fef2f2; }
    
    .text-amber-700 { color: #b45309; }
    .text-blue-700 { color: #1d4ed8; }
    .text-orange-700 { color: #c2410c; }
    .text-emerald-700 { color: #047857; }
    .text-gray-700 { color: #374151; }
    .text-red-700 { color: #b91c1c; }
    
    .border-amber-200 { border-color: #fde68a; }
    .border-blue-200 { border-color: #bfdbfe; }
    .border-orange-200 { border-color: #fed7aa; }
    .border-emerald-200 { border-color: #a7f3d0; }
    .border-gray-300 { border-color: #d1d5db; }
    .border-red-200 { border-color: #fecaca; }
    
    /* Avatar gradients */
    .from-blue-100 { --tw-gradient-from: #dbeafe; }
    .to-blue-50 { --tw-gradient-to: #eff6ff; }
    .from-green-100 { --tw-gradient-from: #d1fae5; }
    .to-green-50 { --tw-gradient-to: #ecfdf5; }
</style>