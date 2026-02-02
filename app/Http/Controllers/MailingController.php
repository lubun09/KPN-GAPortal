<?php

namespace App\Http\Controllers;

use App\Models\Mailing;
use App\Models\Pelanggan;
use App\Models\Ekspedisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MailingController extends Controller
{
    /**
     * Helper method untuk mendapatkan akses user
     */
    private function getUserAccess()
    {
        $username = Auth::user()->username;
        
        return DB::table('tb_access_menu')
            ->where('username', $username)
            ->first();
    }

    /**
     * Helper method untuk mendapatkan ID Pelanggan dari User yang login
     */
    private function getPelangganId()
    {
        $user = Auth::user();
        $pelanggan = Pelanggan::where('id_login', $user->id)->first();
        
        return $pelanggan ? $pelanggan->id_pelanggan : null;
    }

    /**
     * Helper method untuk filter berdasarkan akses
     */
    private function applyAccessFilter($query)
    {
        $accessMenu = $this->getUserAccess();
        
        if (!$accessMenu || (isset($accessMenu->mailing_proses) && $accessMenu->mailing_proses != 1)) {
            $pelangganId = $this->getPelangganId();
            
            if ($pelangganId) {
                $query->where(function($q) use ($pelangganId) {
                    $q->where('mailing_keterangan', 'LIKE', "Pelanggan ID: {$pelangganId} - %")
                      ->orWhere('mailing_keterangan', 'LIKE', "Pelanggan ID: {$pelangganId} %")
                      ->orWhere('mailing_keterangan', 'LIKE', "%Pelanggan ID: {$pelangganId} - %")
                      ->orWhere('mailing_keterangan', 'LIKE', "%Pelanggan ID: {$pelangganId} %");
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Mailing::where('mailing_status', 'Selesai');
        $query = $this->applyAccessFilter($query);
        
        $accessMenu = $this->getUserAccess();
        $canViewAll = $accessMenu && isset($accessMenu->mailing_proses) && $accessMenu->mailing_proses == 1;
        $pelangganId = $this->getPelangganId();
        
        if (request('search')) {
            $searchTerm = request('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('mailing_resi', 'like', '%' . $searchTerm . '%')
                  ->orWhere('mailing_pengirim', 'like', '%' . $searchTerm . '%')
                  ->orWhere('mailing_penerima', 'like', '%' . $searchTerm . '%')
                  ->orWhere('mailing_penerima_distribusi', 'like', '%' . $searchTerm . '%')
                  ->orWhere('mailing_expedisi', 'like', '%' . $searchTerm . '%')
                  ->orWhere('mailing_keterangan', 'like', '%' . $searchTerm . '%');
            });
        }
        
        if (request('start_date')) {
            $query->whereDate('mailing_tanggal_selesai', '>=', request('start_date'));
        }
        
        if (request('end_date')) {
            $query->whereDate('mailing_tanggal_selesai', '<=', request('end_date'));
        }
        
        $mailings = $query->orderBy('mailing_tanggal_selesai', 'desc')->paginate(50);
        
        return view('mailing.index', compact('mailings', 'canViewAll', 'pelangganId'));
    }

    /**
     * Display halaman proses mailing
     */
/**
 * Display halaman proses mailing
 */
public function proses()
{
    $today = Carbon::now()->format('Y-m-d');
    
    // Query dasar - hanya status tertentu
    $query = Mailing::whereIn('mailing_status', ['Mailing Room', 'Lantai 47']);
    
    // Terapkan filter akses
    $query = $this->applyAccessFilter($query);
    
    $accessMenu = $this->getUserAccess();
    $canViewAll = $accessMenu && isset($accessMenu->mailing_proses) && $accessMenu->mailing_proses == 1;
    $pelangganId = $this->getPelangganId();
    
    // Set default tanggal jika kosong
    $startDate = request('start_date', $today);
    $endDate = request('end_date', $today);
    
    // Debug: Uncomment untuk melihat parameter
    // dd(request()->all(), $startDate, $endDate);
    
    // Filter tanggal input
    $query->whereDate('mailing_tanggal_input', '>=', $startDate)
          ->whereDate('mailing_tanggal_input', '<=', $endDate);
    
    // Filter pencarian
    if (request('search')) {
        $searchTerm = request('search');
        $query->where(function($q) use ($searchTerm) {
            $q->where('mailing_resi', 'like', '%' . $searchTerm . '%')
              ->orWhere('mailing_pengirim', 'like', '%' . $searchTerm . '%')
              ->orWhere('mailing_penerima', 'like', '%' . $searchTerm . '%')
              ->orWhere('mailing_expedisi', 'like', '%' . $searchTerm . '%')
              ->orWhere('mailing_lantai', 'like', '%' . $searchTerm . '%')
              ->orWhere('mailing_keterangan', 'like', '%' . $searchTerm . '%');
        });
    }
    
    // Filter status
    if (request('status') && in_array(request('status'), ['Mailing Room', 'Lantai 47'])) {
        $query->where('mailing_status', request('status'));
    }
    
    // Filter lantai
    if (request('lantai')) {
        $query->where('mailing_lantai', request('lantai'));
    }
    
    // Order dan pagination
    $mailings = $query->orderBy('mailing_tanggal_input', 'desc')
                    ->paginate(50)
                    ->withQueryString();
    
    $pelanggans = Pelanggan::orderBy('nama_pelanggan', 'asc')->get();
    
    return view('mailing.proses', compact('mailings', 'pelanggans', 'today', 'canViewAll', 'pelangganId'));
}

    /**
     * Bulk selesaikan mailing - SIMPLE VERSION (NO JSON)
     */
    public function bulkSelesai(Request $request)
    {
        // Validasi sederhana
        $request->validate([
            'mailing_ids' => 'required|array|min:1',
            'mailing_ids.*' => 'exists:tb_mailing,id_mailing',
            'mailing_foto' => 'required|image|max:5120',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Upload foto
            $file = $request->file('mailing_foto');
            $fileName = 'bulk_' . time() . '_' . auth()->id() . '.' . $file->getClientOriginalExtension();
            
            // Simpan ke storage public
            $path = $file->storeAs('mailing-foto', $fileName, 'public');
            
            // Tentukan penerima
            $penerimaNama = '';
            if ($request->filled('penerima_id')) {
                $pelanggan = Pelanggan::find($request->penerima_id);
                $penerimaNama = $pelanggan ? $pelanggan->nama_pelanggan : '';
            } elseif ($request->filled('mailing_penerima_distribusi')) {
                $penerimaNama = $request->mailing_penerima_distribusi;
            }
            
            // Update database
            $now = now()->format('Y-m-d H:i:s');
            $userId = auth()->id();
            
            $updated = DB::table('tb_mailing')
                ->whereIn('id_mailing', $request->mailing_ids)
                ->where('mailing_status', 'Lantai 47')
                ->update([
                    'mailing_status' => 'Selesai',
                    'mailing_tanggal_selesai' => $now,
                    'mailing_selesai_by' => $userId,
                    'mailing_foto' => $path,
                    'mailing_penerima_distribusi' => $penerimaNama,
                    'mailing_keterangan' => DB::raw("CONCAT(COALESCE(mailing_keterangan, ''), ' | Selesai: {$now} oleh {$penerimaNama}')"),
                    'updated_at' => $now
                ]);
            
            DB::commit();
            
            // ✅ HANYA REDIRECT, TIDAK ADA JSON
            return redirect()->route('mailing.proses')
                ->with('success', "✅ {$updated} mailing berhasil diselesaikan");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // ✅ HANYA REDIRECT, TIDAK ADA JSON
            return back()->with('error', 'Gagal menyelesaikan mailing: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update status ke Selesai dengan foto (single)
     */
    public function selesai(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'mailing_foto' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'mailing_penerima_distribusi' => 'required|string|max:255',
                'penerima_id' => 'nullable|string|max:100',
            ]);
            
            $mailing = Mailing::findOrFail($id);
            
            if ($request->hasFile('mailing_foto')) {
                $file = $request->file('mailing_foto');
                $fileName = 'mailing_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('mailing-foto', $fileName, 'public');
                
                $updateData = [
                    'mailing_status' => 'Selesai',
                    'mailing_tanggal_selesai' => now(),
                    'mailing_selesai_by' => auth()->id(),
                    'mailing_foto' => $path,
                    'mailing_penerima_distribusi' => $validated['mailing_penerima_distribusi'],
                ];
                
                if ($request->filled('penerima_id')) {
                    if (is_numeric($request->penerima_id)) {
                        $pelanggan = Pelanggan::find($request->penerima_id);
                        if ($pelanggan) {
                            $keteranganBaru = "Pelanggan ID: {$request->penerima_id} - {$pelanggan->nama_pelanggan}";
                        } else {
                            $keteranganBaru = "Pelanggan ID: {$request->penerima_id} - Tidak Dikenal";
                        }
                    } else {
                        $keteranganBaru = "Penerima ID: {$request->penerima_id}";
                    }
                    
                    if (!empty($mailing->mailing_keterangan)) {
                        $updateData['mailing_keterangan'] = $mailing->mailing_keterangan . ' | ' . $keteranganBaru;
                    } else {
                        $updateData['mailing_keterangan'] = $keteranganBaru;
                    }
                }
                
                $mailing->update($updateData);
                
                return back()->with('success', 'Mailing berhasil diselesaikan dengan foto!');
            }
            
            return back()->with('error', 'Foto tidak ditemukan!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyelesaikan mailing: ' . $e->getMessage());
        }
    }

    /**
     * Store bulk mailing
     */
    public function storeBulk(Request $request)
    {
        try {
            $validated = $request->validate([
                'mailings' => 'required|array|min:1',
            ], [
                'mailings.required' => 'Tidak ada data mailing',
                'mailings.min' => 'Minimal 1 mailing harus diisi',
            ]);
            
            $count = 0;
            
            foreach ($request->mailings as $mailingData) {
                try {
                    // Dapatkan nama ekspedisi
                    $ekspedisiNama = $mailingData['id_ekspedisi_input'] ?? 'Unknown';
                    $ekspedisiId = $mailingData['id_ekspedisi'] ?? null;
                    
                    if ($ekspedisiId && is_numeric($ekspedisiId)) {
                        $ekspedisi = Ekspedisi::find($ekspedisiId);
                        if ($ekspedisi) {
                            $ekspedisiNama = $ekspedisi->nama_ekspedisi;
                        }
                    }
                    
                    // Dapatkan user yang login
                    $user = Auth::user();
                    $pelanggan = Pelanggan::where('id_login', $user->id)->first();
                    
                    // Buat keterangan awal jika ada pelanggan
                    $keteranganAwal = '';
                    if ($pelanggan) {
                        $keteranganAwal = "Pelanggan ID: {$pelanggan->id_pelanggan} - {$pelanggan->nama_pelanggan}";
                    }
                    
                    // Simpan ke database
                    Mailing::create([
                        'mailing_resi' => $mailingData['mailing_resi'] ?? '',
                        'mailing_pengirim' => $mailingData['mailing_pengirim'] ?? '',
                        'mailing_penerima' => $mailingData['mailing_penerima'] ?? '',
                        'mailing_lantai' => $mailingData['mailing_lantai'] ?? null,
                        'mailing_expedisi' => $ekspedisiNama,
                        'mailing_status' => 'Mailing Room',
                        'mailing_prioritas' => 'Normal',
                        'mailing_tanggal_input' => now(),
                        'mailing_input_by' => $user->id,
                        'mailing_keterangan' => $keteranganAwal,
                    ]);
                    
                    $count++;
                    
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            if ($count > 0) {
                return redirect()->route('mailing.proses')
                    ->with('success', "✅ {$count} mailing berhasil ditambahkan!");
            } else {
                return back()->with('error', 'Tidak ada data yang berhasil disimpan.')
                    ->withInput();
            }
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', 'Validasi gagal: ' . implode(', ', array_merge(...array_values($e->errors()))))
                ->withInput()
                ->withErrors($e->errors());
                
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Create mailing form
     */
    public function create()
    {
        $ekspedisi = Ekspedisi::orderBy('nama_ekspedisi')->get();
        return view('mailing.create', compact('ekspedisi'));
    }
    
    /**
     * View foto
     */
    public function viewFoto($id)
    {
        try {
            $mailing = Mailing::findOrFail($id);
            
            if (!$mailing->mailing_foto) {
                abort(404, 'Foto tidak ditemukan di database');
            }
            
            $path = storage_path('app/public/' . $mailing->mailing_foto);
            
            if (!file_exists($path)) {
                abort(404, 'File foto tidak ditemukan di storage');
            }
            
            return response()->file($path, [
                'Content-Type' => mime_content_type($path),
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
            ]);
            
        } catch (\Exception $e) {
            abort(404, 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk memastikan direktori ada
     */
    private function ensureDirectoryExists()
    {
        $directory = storage_path('app/public/mailing-foto');
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
    
    /**
     * Bulk move ke Lantai 47 - SIMPLE VERSION (NO JSON)
     */
    public function bulkLantai47(Request $request)
    {
        $request->validate([
            'mailing_ids' => 'required|array|min:1',
            'mailing_ids.*' => 'exists:tb_mailing,id_mailing'
        ]);
        
        try {
            $updated = DB::table('tb_mailing')
                ->whereIn('id_mailing', $request->mailing_ids)
                ->where('mailing_status', 'Mailing Room')
                ->update([
                    'mailing_status' => 'Lantai 47',
                    'mailing_tanggal_ob47' => now(),
                    'mailing_ob47_by' => auth()->id(),
                ]);
            
            // ✅ HANYA REDIRECT, TIDAK ADA JSON
            return redirect()->route('mailing.proses')
                ->with('success', "✅ {$updated} mailing berhasil dipindahkan ke Lantai 47");
                
        } catch (\Exception $e) {
            // ✅ HANYA REDIRECT, TIDAK ADA JSON
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
    
    /**
     * Single move to Lantai 47 (GET version)
     */
    public function lantai47Get($id)
    {
        try {
            $mailing = Mailing::findOrFail($id);
            
            if ($mailing->mailing_status !== 'Mailing Room') {
                return back()->with('error', 'Status harus "Mailing Room" untuk dipindahkan ke Lantai 47');
            }
            
            DB::table('tb_mailing')
                ->where('id_mailing', $id)
                ->where('mailing_status', 'Mailing Room')
                ->update([
                    'mailing_status' => 'Lantai 47',
                    'mailing_tanggal_ob47' => now(),
                    'mailing_ob47_by' => auth()->id(),
                ]);
            
            return back()->with('success', 'Status berhasil diperbarui ke Lantai 47');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Complete mailing form (GET version)
     */
    public function selesaiGet($id)
    {
        $mailing = Mailing::findOrFail($id);
        $pelanggans = Pelanggan::orderBy('nama_pelanggan')->get();
        
        return view('mailing.complete-form', compact('mailing', 'pelanggans'));
    }
    // MailingController.php
    public function getPelanggans()
    {
        try {
            $pelanggans = Pelanggan::orderBy('nama_pelanggan', 'asc')
                ->select('id_pelanggan', 'nama_pelanggan')
                ->get();
            
            return response()->json($pelanggans);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }
    /**
     * Single move to Lantai 47 (POST version)
     */
    public function lantai47($id)
    {
        try {
            $mailing = Mailing::findOrFail($id);
            
            if ($mailing->mailing_status !== 'Mailing Room') {
                return back()->with('error', 'Status harus "Mailing Room" untuk dipindahkan ke Lantai 47');
            }
            
            $mailing->update([
                'mailing_status' => 'Lantai 47',
                'mailing_tanggal_ob47' => now(),
                'mailing_ob47_by' => auth()->id(),
            ]);
            
            return back()->with('success', 'Status berhasil diperbarui ke Lantai 47');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }
}