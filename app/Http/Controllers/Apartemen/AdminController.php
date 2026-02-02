<?php

namespace App\Http\Controllers\Apartemen;

use App\Http\Controllers\Controller;
use App\Models\Apartemen\Apartemen;
use App\Models\Apartemen\ApartemenUnit;
use App\Models\Apartemen\ApartemenRequest;
use App\Models\Apartemen\ApartemenAssign;
use App\Models\Apartemen\ApartemenPenghuni;
use App\Models\Apartemen\ApartemenHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    // DASHBOARD ADMIN
    public function dashboard()
    {
        $stats = [
            'total_apartemen' => Apartemen::count(),
            'total_unit' => ApartemenUnit::count(),
            'unit_tersedia' => ApartemenUnit::where('status', 'READY')->count(),
            'unit_terisi' => ApartemenUnit::where('status', 'TERISI')->count(),
            'unit_maintenance' => ApartemenUnit::where('status', 'MAINTENANCE')->count(),
            'permintaan_pending' => ApartemenRequest::where('status', 'PENDING')->count(),
            'permintaan_approved' => ApartemenRequest::where('status', 'APPROVED')->count(),
            'permintaan_rejected' => ApartemenRequest::where('status', 'REJECTED')->count(),
            'penghuni_aktif' => ApartemenPenghuni::where('status', 'AKTIF')->count(),
        ];

        // Permintaan pending terbaru
        $pendingRequests = ApartemenRequest::with(['user', 'penghuni'])
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Penghuni yang akan selesai dalam 7 hari
        $upcomingCheckouts = ApartemenAssign::with(['unit.apartemen', 'penghuni'])
            ->where('status', 'AKTIF')
            ->whereBetween('tanggal_selesai', [now(), now()->addDays(7)])
            ->orderBy('tanggal_selesai')
            ->get();

        // Unit dalam maintenance
        $maintenanceUnits = ApartemenUnit::with('apartemen')
            ->where('status', 'MAINTENANCE')
            ->get();

        return view('apartemen.admin.dashboard', compact(
            'stats', 
            'pendingRequests', 
            'upcomingCheckouts', 
            'maintenanceUnits'
        ));
    }

    // INDEX - TAB PERMINTAAN
    public function index(Request $request)
    {
        $query = ApartemenRequest::with(['user', 'penghuni', 'assign.unit.apartemen'])
            ->orderBy('created_at', 'desc');

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter tanggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal_pengajuan', [
                $request->tanggal_mulai,
                $request->tanggal_selesai
            ]);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhereHas('penghuni', function($q2) use ($search) {
                    $q2->where('nama', 'like', "%{$search}%")
                    ->orWhere('id_karyawan', 'like', "%{$search}%");
                });
            });
        }

        $requests = $query->paginate(10);

        return view('apartemen.admin.index', compact('requests'));
    }

    // APPROVE/REVIEW REQUEST - UNTUK MULTIPLE UNITS
    public function approve($id)
    {
        $request = ApartemenRequest::with(['penghuni', 'user'])
            ->findOrFail($id);

        // Cek unit yang tersedia (status READY)
        $availableUnits = ApartemenUnit::where('status', 'READY')
            ->with('apartemen')
            ->get();

        return view('apartemen.admin.approve', compact('request', 'availableUnits'));
    }

    // PROCESS APPROVAL - VERSI MULTIPLE UNITS
    public function approveProcess(Request $request, $id)
    {
        $apartemenRequest = ApartemenRequest::with(['penghuni'])->findOrFail($id);

        // Validasi action
        $action = $request->action;
        
        DB::beginTransaction();
        try {
            if ($action === 'approve') {
                // Validasi untuk approve dengan multiple units
                $request->validate([
                    'penempatan' => 'required|array|min:1',
                    'penempatan.*.unit_id' => 'required|exists:tb_apartemen_unit,id',
                    'penempatan.*.penghuni_ids' => 'required|array|min:1',
                    'tanggal_mulai' => 'required|date',
                    'tanggal_selesai' => 'required|date|after:tanggal_mulai'
                ]);

                // Debug: Log data yang diterima
                Log::info('Penempatan data received:', $request->penempatan);
                
                // Validasi bahwa semua penghuni tercover
                $penghuniTercover = collect();
                foreach ($request->penempatan as $item) {
                    $penghuniTercover = $penghuniTercover->merge($item['penghuni_ids']);
                }
                
                $semuaPenghuniIds = $apartemenRequest->penghuni->pluck('id')->toArray();
                $selisih = array_diff($semuaPenghuniIds, $penghuniTercover->toArray());
                
                if (!empty($selisih)) {
                    return back()->with('error', 'Ada penghuni yang belum ditetapkan ke unit!');
                }
                
                // Validasi kapasitas unit
                foreach ($request->penempatan as $item) {
                    $unit = ApartemenUnit::find($item['unit_id']);
                    $jumlahPenghuni = count($item['penghuni_ids']);
                    
                    if ($unit->kapasitas < $jumlahPenghuni) {
                        return back()->with('error', "Unit {$unit->nomor_unit} kapasitas tidak mencukupi! (Kapasitas: {$unit->kapasitas}, Ditempatkan: {$jumlahPenghuni})");
                    }
                    
                    // Validasi unit status
                    if ($unit->status != 'READY') {
                        return back()->with('error', "Unit {$unit->nomor_unit} tidak tersedia (Status: {$unit->status})");
                    }
                }
                
                // Update status request
                $apartemenRequest->update([
                    'status' => 'APPROVED',
                    'approved_at' => now(),
                    'approved_by' => auth()->id()
                ]);
                
                // Proses penempatan ke masing-masing unit
                foreach ($request->penempatan as $item) {
                    $unit = ApartemenUnit::find($item['unit_id']);
                    
                    // Update status unit menjadi TERISI
                    $unit->update(['status' => 'TERISI']);
                    
                    // Buat assign untuk unit ini
                    $assign = ApartemenAssign::create([
                        'request_id' => $apartemenRequest->id,
                        'unit_id' => $unit->id,
                        'tanggal_mulai' => $request->tanggal_mulai,
                        'tanggal_selesai' => $request->tanggal_selesai,
                        'status' => 'AKTIF',
                        'assign_by' => auth()->id()
                    ]);
                    
                    // Buat penghuni untuk setiap ID yang dipilih
                    foreach ($item['penghuni_ids'] as $penghuniId) {
                        // Ambil data penghuni dari request
                        $reqPenghuni = $apartemenRequest->penghuni->where('id', $penghuniId)->first();
                        
                        if ($reqPenghuni) {
                            ApartemenPenghuni::create([
                                'assign_id' => $assign->id,
                                'nama' => $reqPenghuni->nama,
                                'id_karyawan' => $reqPenghuni->id_karyawan,
                                'no_hp' => $reqPenghuni->no_hp, // INI YANG PENTING: Ambil no_hp
                                'unit_kerja' => $reqPenghuni->unit_kerja,
                                'gol' => $reqPenghuni->gol,
                                'tanggal_mulai' => $reqPenghuni->tanggal_mulai,
                                'tanggal_selesai' => $reqPenghuni->tanggal_selesai,
                                'status' => 'AKTIF',
                            ]);
                        }
                    }
                }
                
                DB::commit();
                
                return redirect()->route('apartemen.admin.index')
                    ->with('success', 'Permintaan berhasil disetujui dan penghuni telah ditempatkan!');
                
            } elseif ($action === 'reject') {
                // Validasi untuk reject
                $request->validate([
                    'reject_reason' => 'required|string|min:5|max:500'
                ]);

                // Reject request
                $apartemenRequest->update([
                    'status' => 'REJECTED',
                    'reject_reason' => $request->reject_reason,
                    'approved_by' => auth()->user()->name,
                    'approved_at' => now(),
                ]);

                DB::commit();
                return redirect()->route('apartemen.admin.index')
                    ->with('success', 'Permintaan berhasil ditolak.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in approveProcess: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->withInput()
                ->with('error', 'Gagal memproses penempatan: ' . $e->getMessage());
        }
    }

    // MONITORING PENGHUNI
    public function monitoring(Request $request)
    {
        $query = ApartemenPenghuni::with(['assign.unit.apartemen'])
            ->where('status', 'AKTIF');

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('id_karyawan', 'like', "%{$search}%")
                  ->orWhere('unit_kerja', 'like', "%{$search}%");
            });
        }

        // Filter apartemen
        if ($request->filled('apartemen_id')) {
            $query->whereHas('assign.unit', function($q) use ($request) {
                $q->where('apartemen_id', $request->apartemen_id);
            });
        }

        // Filter berdasarkan tanggal selesai
        if ($request->filled('status')) {
            if ($request->status == 'akan_selesai') {
                $query->whereHas('assign', function($q) {
                    $q->where('tanggal_selesai', '>=', now())
                      ->where('tanggal_selesai', '<=', now()->addDays(7));
                });
            } elseif ($request->status == 'telah_selesai') {
                $query->whereHas('assign', function($q) {
                    $q->where('tanggal_selesai', '<', now());
                });
            }
        }

        // Sort
        $sort = $request->filled('sort') ? $request->sort : 'nama_asc';
        switch ($sort) {
            case 'nama_desc':
                $query->orderBy('nama', 'desc');
                break;
            case 'tanggal_mulai':
                $query->orderBy('tanggal_mulai', 'desc');
                break;
            case 'tanggal_selesai':
                $query->orderBy('tanggal_selesai', 'asc');
                break;
            default:
                $query->orderBy('nama', 'asc');
        }

        $penghuni = $query->paginate(10);
        $apartemen = Apartemen::all();

        return view('apartemen.admin.monitoring', compact('penghuni', 'apartemen'));
    }

    // CHECKOUT PER ORANG
    public function checkoutPenghuni($id)
    {
        try {
            Log::info('=== CHECKOUT START === ID: ' . $id);
            
            // Gunakan database transaction dengan retry
            $result = DB::transaction(function () use ($id) {
                Log::info('Transaction started for ID: ' . $id);
                
                // 1. Ambil data dengan LOCK FOR UPDATE
                $penghuni = ApartemenPenghuni::with(['assign.unit.apartemen', 'assign.penghuni'])
                    ->where('status', 'AKTIF')
                    ->lockForUpdate()
                    ->findOrFail($id);
                    
                Log::info('Penghuni found (locked): ' . $penghuni->nama . ' (ID: ' . $penghuni->id . ')');
                
                // 2. Cek assign
                $assign = $penghuni->assign;
                if (!$assign) {
                    throw new \Exception('Penghuni tidak memiliki assignment');
                }
                
                Log::info('Assign ID: ' . $assign->id . ', Unit: ' . ($assign->unit ? $assign->unit->nomor_unit : 'N/A'));
                
                // 3. Cek DUPLIKAT history dalam 1 MENIT terakhir
                $recentHistory = ApartemenHistory::where('id_karyawan', $penghuni->id_karyawan)
                    ->where('apartemen', $assign->unit->apartemen->nama_apartemen ?? '')
                    ->where('unit', $assign->unit->nomor_unit ?? '')
                    ->where('created_at', '>=', now()->subMinute())
                    ->exists();
                    
                if ($recentHistory) {
                    Log::warning('Duplicate checkout detected within 1 minute for: ' . $penghuni->nama);
                    throw new \Exception('Checkout sudah diproses untuk penghuni ini dalam 1 menit terakhir.');
                }
                
                // 4. Hitung penghuni aktif SEBELUM update
                $activePenghuniBefore = $assign->penghuni()
                    ->where('status', 'AKTIF')
                    ->count();
                    
                Log::info('Active penghuni before checkout: ' . $activePenghuniBefore);
                
                // 5. Update status penghuni
                $penghuni->update(['status' => 'SELESAI']);
                Log::info('Updated penghuni status to SELESAI');
                
                // 6. Hitung penghuni aktif SETELAH update
                $activePenghuniAfter = $assign->penghuni()
                    ->where('status', 'AKTIF')
                    ->count();
                    
                Log::info('Active penghuni after checkout: ' . $activePenghuniAfter);
                
                // 7. Jika ini penghuni TERAKHIR, update assign ke SELESAI dan unit ke READY
                if ($activePenghuniAfter == 0) {
                    Log::info('Last penghuni in assign, updating assign and unit...');
                    
                    try {
                        // PERBAIKAN: Tangkap exception jika ada constraint violation
                        try {
                            $assign->update(['status' => 'SELESAI']);
                            Log::info('Updated assign status to SELESAI');
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Jika error karena constraint violation (duplicate entry)
                            if ($e->errorInfo[1] == 1062) {
                                Log::warning('Constraint violation when updating assign to SELESAI, but continuing...');
                                // Tetap lanjutkan, karena yang penting penghuni sudah SELESAI
                            } else {
                                throw $e; // Re-throw error lainnya
                            }
                        }
                        
                        // Update unit status ke READY
                        if ($assign->unit) {
                            $assign->unit->update(['status' => 'READY']);
                            Log::info('Unit updated to READY: ' . $assign->unit->nomor_unit);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error updating assign/unit status: ' . $e->getMessage());
                        // Tetap lanjutkan karena history sudah dibuat
                    }
                } else {
                    Log::info('Masih ada ' . $activePenghuniAfter . ' penghuni aktif di unit ' . ($assign->unit ? $assign->unit->nomor_unit : 'N/A'));
                }
                
                // **PERBAIKAN: HAPUS INSERT HISTORY DI SINI**
                // Biarkan database trigger atau event yang menangani
                Log::info('History akan dibuat oleh database trigger/event');
                
                return [
                    'success' => true,
                    'message' => 'Check-out penghuni ' . $penghuni->nama . ' berhasil dilakukan.'
                ];
            }, 3); // 3 attempts
            
            Log::info('=== CHECKOUT SUCCESS ===');
            
            return back()->with('success', $result['message']);
            
        } catch (\Exception $e) {
            Log::error('=== CHECKOUT ERROR ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // HISTORY
    public function history(Request $request)
    {
        $query = ApartemenHistory::orderBy('created_at', 'desc');

        // Filter tanggal
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('created_at', [
                $request->tanggal_mulai,
                $request->tanggal_selesai
            ]);
        }

        // Filter status
        if ($request->filled('status_selesai')) {
            $query->where('status_selesai', $request->status_selesai);
        }

        // Tambahkan search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('id_karyawan', 'like', "%{$search}%")
                ->orWhere('apartemen', 'like', "%{$search}%")
                ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        $histories = $query->paginate(10);

        return view('apartemen.admin.history', compact('histories'));
    }

    // MANAJEMEN APARTEMEN & UNIT
    public function apartemen(Request $request)
    {
        $query = Apartemen::withCount(['units', 'units as units_ready' => function($q) {
            $q->where('status', 'READY');
        }, 'units as units_terisi' => function($q) {
            $q->where('status', 'TERISI');
        }]);

        if ($request->filled('search')) {
            $query->where('nama_apartemen', 'like', "%{$request->search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$request->search}%");
        }

        $apartemen = $query->paginate(10);

        return view('apartemen.admin.apartemen', compact('apartemen'));
    }

    // APARTEMEN DETAIL
    public function apartemenDetail($id, Request $request)
    {
        // Load apartemen dengan count unit berdasarkan status
        $apartemen = Apartemen::withCount([
            'units as units_count',
            'units as units_ready' => function ($query) {
                $query->where('status', 'READY');
            },
            'units as units_terisi' => function ($query) {
                $query->where('status', 'TERISI');
            },
            'units as units_maintenance' => function ($query) {
                $query->where('status', 'MAINTENANCE');
            }
        ])->findOrFail($id);

        // Query units dengan pencarian
        $unitsQuery = ApartemenUnit::where('apartemen_id', $id)
            ->withCount(['assigns as active_assignments' => function($q) {
                $q->where('status', 'AKTIF');
            }]);

        // Filter search jika ada
        if ($request->filled('search')) {
            $unitsQuery->where('nomor_unit', 'like', '%' . $request->search . '%');
        }

        // Filter status jika ada
        if ($request->filled('status')) {
            $unitsQuery->where('status', $request->status);
        }

        $units = $unitsQuery->orderBy('nomor_unit')->paginate(10);

        return view('apartemen.admin.apartemen-detail', compact('apartemen', 'units'));
    }

    // DETAIL REQUEST
    public function detail($id)
    {
        $request = ApartemenRequest::with([
            'user', 
            'penghuni',
            'assign.unit.apartemen', // GANTI 'assigns' MENJADI 'assign'
            'assign.penghuni' => function($query) {
                $query->where('status', 'AKTIF')->orWhere('status', 'SELESAI')
                    ->select('id', 'assign_id', 'nama', 'id_karyawan', 'no_hp', 'unit_kerja', 'gol', 'status');
            }
        ])->findOrFail($id);

        // Jika sudah di-approve, ambil unit yang ditempati
        // Karena mungkin ada multiple units, query langsung
        $units = collect();
        if ($request->status == 'APPROVED') {
            $units = ApartemenAssign::where('request_id', $request->id)
                ->with(['unit.apartemen', 'penghuni' => function($query) {
                    $query->select('id', 'assign_id', 'nama', 'id_karyawan', 'no_hp', 'unit_kerja', 'gol', 'status');
                }])
                ->get();
        }

        return view('apartemen.admin.detail', compact('request', 'units'));
    }
    
    // STORE APARTEMEN
    public function storeApartemen(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_apartemen' => 'required|string|max:255',
                'alamat' => 'required|string|max:500',
                'penanggung_jawab' => 'nullable|string|max:100',
                'kontak_darurat' => 'nullable|string|max:50',
                'telepon' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
            ]);

            Apartemen::create($validated);

            return redirect()->route('apartemen.admin.apartemen')
                ->with('success', 'Apartemen berhasil ditambahkan');
                
        } catch (\Exception $e) {
            Log::error('Error storing apartemen: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Gagal menambahkan apartemen: ' . $e->getMessage());
        }
    }

    // STORE UNIT
    public function storeUnit(Request $request)
    {
        try {
            $validated = $request->validate([
                'apartemen_id' => 'required|exists:tb_apartemen,id',
                'nomor_unit' => 'required|string|max:20',
                'kapasitas' => 'required|integer|min:1',
                'status' => 'required|in:READY,MAINTENANCE'
            ]);

            // Cek nomor unit duplikat dalam apartemen yang sama
            $existingUnit = ApartemenUnit::where('apartemen_id', $validated['apartemen_id'])
                ->where('nomor_unit', $validated['nomor_unit'])
                ->first();

            if ($existingUnit) {
                return back()->withInput()
                    ->with('error', 'Nomor unit sudah digunakan di apartemen ini');
            }

            ApartemenUnit::create($validated);

            return back()->with('success', 'Unit berhasil ditambahkan');
            
        } catch (\Exception $e) {
            Log::error('Error storing unit: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Gagal menambahkan unit: ' . $e->getMessage());
        }
    }

    // DELETE UNIT
    public function deleteUnit(Request $request)
    {
        try {
            $validated = $request->validate([
                'unit_id' => 'required|exists:tb_apartemen_unit,id'
            ]);

            $unit = ApartemenUnit::find($validated['unit_id']);
            
            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit tidak ditemukan'
                ], 404);
            }
            
            // Cek apakah unit sedang digunakan
            if ($unit->status == 'TERISI') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit sedang terisi, tidak dapat dihapus'
                ], 400);
            }

            $unit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unit berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting unit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // SET MAINTENANCE
    public function setMaintenance(Request $request)
    {
        try {
            $validated = $request->validate([
                'unit_id' => 'required|exists:tb_apartemen_unit,id',
                'status' => 'required|in:READY,MAINTENANCE',
                'catatan' => 'nullable|string|max:500'
            ]);

            $unit = ApartemenUnit::find($validated['unit_id']);

            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit tidak ditemukan'
                ], 404);
            }

            // Cek jika unit sedang terisi, tidak bisa diubah ke maintenance
            if ($validated['status'] == 'MAINTENANCE' && $unit->status == 'TERISI') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit sedang terisi, tidak dapat diubah ke maintenance'
                ], 400);
            }

            $unit->update([
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? $unit->catatan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status unit berhasil diperbarui'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error setting maintenance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // REPORT
    public function report(Request $request)
    {
        $reportData = [];
        $type = $request->get('type', 'occupancy');

        switch ($type) {
            case 'occupancy':
                $reportData = $this->occupancyReport($request);
                break;
            case 'utilization':
                $reportData = $this->utilizationReport($request);
                break;
            case 'maintenance':
                $reportData = $this->maintenanceReport($request);
                break;
        }

        return view('apartemen.admin.report', compact('reportData', 'type'));
    }

    private function occupancyReport($request)
    {
        $apartemen = Apartemen::withCount(['units', 'units as units_ready', 'units as units_terisi'])
            ->get();

        return [
            'title' => 'Laporan Occupancy Apartemen',
            'data' => $apartemen,
            'total_units' => $apartemen->sum('units_count'),
            'total_ready' => $apartemen->sum('units_ready'),
            'total_terisi' => $apartemen->sum('units_terisi'),
        ];
    }

    private function utilizationReport($request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $assignments = ApartemenAssign::with(['unit.apartemen'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'title' => 'Laporan Utilization Apartemen',
            'data' => $assignments,
            'total_assignments' => $assignments->count(),
            'total_days' => $assignments->sum(function($assign) {
                return $assign->tanggal_mulai->diffInDays($assign->tanggal_selesai);
            }),
        ];
    }

    private function maintenanceReport($request)
    {
        $units = ApartemenUnit::with('apartemen')
            ->where('status', 'MAINTENANCE')
            ->get();

        return [
            'title' => 'Laporan Maintenance Unit',
            'data' => $units,
            'total_maintenance' => $units->count(),
        ];
    }
}