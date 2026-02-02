{{-- resources/views/help/proses/index.blade.php --}}
@extends('layouts.app-sidebar')

@section('content')
<div class="space-y-6 text-sm text-gray-800 font-sans">
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Proses Tiket</h2>
            @if($isGAAdmin)
                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full
                             text-xs font-semibold bg-green-100 text-green-800">
                    GA Admin Mode
                </span>
            @endif
        </div>

        <div class="flex gap-2 w-full sm:w-auto">
            <a href="{{ route('help.tiket.index') }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center
                      px-4 py-2 bg-blue-100 text-blue-700 rounded-lg
                      text-sm font-semibold hover:bg-blue-200 transition">
                <i class="fas fa-arrow-left mr-2"></i> Tiket Saya
            </a>

            <button id="toggleFilterBtn"
                class="flex-1 sm:flex-none px-4 py-2 bg-gray-100 text-gray-700
                       rounded-lg text-sm font-semibold hover:bg-gray-200 transition">
                Filters
            </button>
        </div>
    </div>

    {{-- FILTER --}}
    <div id="filterSection" class="bg-white border rounded-xl p-4 hidden">
        <form method="GET" action="{{ route('help.proses.index') }}"
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div>
                <label class="text-sm font-medium text-gray-600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Judul atau nomor tiket"
                       class="mt-1 w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-600">Status</label>
                <select name="status"
                        class="mt-1 w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="OPEN" {{ request('status')=='OPEN'?'selected':'' }}>OPEN</option>
                    <option value="ON_PROCESS" {{ request('status')=='ON_PROCESS'?'selected':'' }}>ON PROCESS</option>
                    <option value="WAITING" {{ request('status')=='WAITING'?'selected':'' }}>WAITING</option>
                    <option value="DONE" {{ request('status')=='DONE'?'selected':'' }}>DONE</option>
                    <option value="CLOSED" {{ request('status')=='CLOSED'?'selected':'' }}>CLOSED</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-600">Prioritas</label>
                <select name="prioritas"
                        class="mt-1 w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="LOW" {{ request('prioritas')=='LOW'?'selected':'' }}>LOW</option>
                    <option value="MEDIUM" {{ request('prioritas')=='MEDIUM'?'selected':'' }}>MEDIUM</option>
                    <option value="HIGH" {{ request('prioritas')=='HIGH'?'selected':'' }}>HIGH</option>
                    <option value="URGENT" {{ request('prioritas')=='URGENT'?'selected':'' }}>URGENT</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-600">Kategori</label>
                <select name="kategori_id"
                        class="mt-1 w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    @foreach($kategori as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_id')==$kat->id?'selected':'' }}>
                            {{ $kat->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-4 flex flex-col sm:flex-row gap-2 justify-end">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                    Apply
                </button>
                <a href="{{ route('help.proses.index') }}"
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center">
        <i class="fas fa-check-circle text-green-500 mr-3"></i>
        <span class="text-sm text-green-700">{{ session('success') }}</span>
        <button class="ml-auto text-green-500 hover:text-green-700" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center">
        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
        <span class="text-sm text-red-700">{{ session('error') }}</span>
        <button class="ml-auto text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- TABLE --}}
    @if($tiket->isEmpty())
    <div class="bg-white border rounded-xl p-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 rounded-full mb-4">
            <i class="fas fa-check-circle text-2xl text-blue-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-700 mb-2">Tidak ada tiket yang perlu diproses</h3>
        <p class="text-gray-500">Semua tiket sudah ditangani atau belum ada tiket yang dibuat.</p>
    </div>
    @else
    <div class="bg-white border rounded-xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">No. Tiket</th>
                    <th class="px-4 py-3 text-left">Judul</th>
                    <th class="px-4 py-3 text-left hidden md:table-cell">Pelapor</th>
                    <th class="px-4 py-3 text-left hidden lg:table-cell">Kategori</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left hidden lg:table-cell">Prioritas</th>
                    <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($tiket as $item)
                <tr class="hover:bg-gray-50">
                    {{-- No. Tiket --}}
                    <td class="px-4 py-3 font-medium">
                        <div class="text-blue-600">{{ $item->nomor_tiket }}</div>
                        <div class="text-xs text-gray-500 sm:hidden">
                            {{ $item->created_at->format('d/m/Y') }}
                        </div>
                    </td>

                    {{-- Judul --}}
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ Str::limit($item->judul, 40) }}</div>
                        @if($item->deskripsi)
                        <div class="text-xs text-gray-500 sm:hidden">
                            {{ Str::limit(strip_tags($item->deskripsi), 40) }}
                        </div>
                        @endif
                    </td>

                    {{-- Pelapor --}}
                    <td class="px-4 py-3 hidden md:table-cell">
                        <div class="flex items-center">
                            @php
                                // CARI PELANGGAN
                                $pelanggan = \App\Models\Pelanggan::with('user')->find($item->pelapor_id);
                                
                                if ($pelanggan) {
                                    // AMBIL NAMA DARI USER JIKA ADA, JIKA TIDAK DARI PELANGGAN
                                    if ($pelanggan->user && $pelanggan->user->name) {
                                        $pelaporName = $pelanggan->user->name;
                                    } else {
                                        $pelaporName = $pelanggan->nama ?? "Pelanggan";
                                    }
                                } else {
                                    $pelaporName = "ID: {$item->pelapor_id}";
                                }
                                
                                $pelaporInitial = substr($pelaporName, 0, 1);
                            @endphp
                            
                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-xs font-medium text-blue-700 mr-2">
                                {{ $pelaporInitial }}
                            </div>
                            <span title="{{ $pelaporName }}">
                                {{ Str::limit($pelaporName, 15) }}
                            </span>
                        </div>
                    </td>

                    {{-- Kategori --}}
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                            {{ $item->kategori->nama ?? '-' }}
                        </span>
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3">
                        @php
                            $statusColors = [
                                'OPEN' => 'bg-yellow-100 text-yellow-800',
                                'ON_PROCESS' => 'bg-blue-100 text-blue-800',
                                'WAITING' => 'bg-orange-100 text-orange-800',
                                'DONE' => 'bg-green-100 text-green-800',
                                'CLOSED' => 'bg-gray-100 text-gray-800'
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$item->status] }}">
                            {{ $item->status }}
                        </span>
                    </td>

                    {{-- Prioritas --}}
                    <td class="px-4 py-3 hidden lg:table-cell">
                        @php
                            $priorityColors = [
                                'URGENT' => 'bg-red-100 text-red-800',
                                'HIGH' => 'bg-orange-100 text-orange-800',
                                'MEDIUM' => 'bg-blue-100 text-blue-800',
                                'LOW' => 'bg-gray-100 text-gray-800'
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $priorityColors[$item->prioritas] }}">
                            {{ $item->prioritas }}
                        </span>
                    </td>

                    {{-- Tanggal --}}
                    <td class="px-4 py-3 hidden sm:table-cell">
                        <div class="text-xs text-gray-600">
                            {{ $item->created_at->format('d M Y') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $item->created_at->format('H:i') }}
                        </div>
                    </td>

                    {{-- Action --}}
                    <td class="px-4 py-3">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('help.proses.show', $item) }}"
                               class="text-blue-600 font-semibold hover:underline text-sm">
                                Detail
                            </a>
                            
                            @if($item->status === 'OPEN')
                            <form action="{{ route('help.proses.take', $item) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Ambil tiket ini untuk diproses?')"
                                        class="text-green-600 font-semibold hover:underline text-sm">
                                    Ambil
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($tiket->hasPages())
        <div class="mt-4">
            {{ $tiket->links() }}
        </div>
    @endif
    @endif
</div>

{{-- SCRIPT --}}
<script>
document.getElementById('toggleFilterBtn')?.addEventListener('click', () => {
    document.getElementById('filterSection').classList.toggle('hidden')
})

// Auto hide alerts after 5 seconds
setTimeout(() => {
    document.querySelectorAll('.bg-green-50, .bg-red-50').forEach(alert => {
        alert.style.transition = 'opacity 0.3s ease-out';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    });
}, 5000);
</script>
@endsection