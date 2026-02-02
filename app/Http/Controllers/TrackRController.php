<?php

namespace App\Http\Controllers;

use App\Models\TrackRDocument;
use App\Models\TrackRLog;
use App\Models\TrackRFoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class TrackRController extends Controller
{
    /* =========================
       LIST - HANYA DOKUMEN USER
    ========================= */
    public function index()
    {
        // Hanya tampilkan dokumen yang user terlibat
        $documents = TrackRDocument::with(['pengirim','penerima'])
            ->where(function($query) {
                $query->where('pengirim_id', auth()->id())
                      ->orWhere('penerima_id', auth()->id());
            })
            ->orderBy('created_at','desc')
            ->paginate(15);

        return view('track_r.index', compact('documents'));
    }

    /* =========================
       FORM CREATE
    ========================= */
    public function create()
    {
        $users = User::where('id', '!=', auth()->id()) // Jangan tampilkan diri sendiri
                    ->orderBy('name')
                    ->get();
        return view('track_r.create', compact('users'));
    }

    /* =========================
       STORE / KIRIM
    ========================= */
public function store(Request $request)
{
    $request->validate([
        'nomor_dokumen' => 'required|unique:track_r_documents',
        'judul' => 'required',
        'penerima_id' => 'required',
        'keterangan' => 'nullable|string',
        'foto_dokumen.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
    ]);

    DB::transaction(function () use ($request) {
        // Parse penerima_id (bisa user ID atau manual)
        $penerimaId = $request->penerima_id;
        $isManualUser = false;
        $manualUserInfo = null;
        
        // Check if it's a manual user (format: manual:[id]:[name]:[email])
        if (strpos($penerimaId, 'manual:') === 0) {
            $isManualUser = true;
            $parts = explode(':', $penerimaId);
            $manualUserInfo = [
                'id' => $parts[1] ?? null,
                'name' => $parts[2] ?? 'Unknown',
                'email' => $parts[3] ?? null,
            ];
            // Use a placeholder user ID (you might want to create a temporary user record)
            $penerimaId = 999999; // Temporary ID for manual users
        }

        // 1. Buat dokumen
        $doc = TrackRDocument::create([
            'nomor_dokumen' => $request->nomor_dokumen,
            'judul' => $request->judul,
            'keterangan' => $request->keterangan,
            'pengirim_id' => auth()->id(),
            'penerima_id' => $penerimaId,
            'status' => 'dikirim',
        ]);

        // If manual user, store the info in keterangan or separate field
        if ($isManualUser && $manualUserInfo) {
            $doc->update([
                'keterangan' => ($doc->keterangan ?? '') . 
                               "\n[Penerima Manual: " . $manualUserInfo['name'] . 
                               ($manualUserInfo['email'] ? " - " . $manualUserInfo['email'] : '') . "]"
            ]);
        }

        // 2. Simpan foto jika ada (kode tetap sama)
        if ($request->hasFile('foto_dokumen')) {
            $directory = storage_path('app/private/Track/' . $doc->id);
            
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            foreach ($request->file('foto_dokumen') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $filename = $originalName . '_' . time() . '_' . uniqid() . '.' . $extension;
                
                $filePath = 'Track/' . $doc->id . '/' . $filename;
                
                Storage::disk('private')->put($filePath, file_get_contents($file));
                
                TrackRFoto::create([
                    'track_r_document_id' => $doc->id,
                    'nama_file' => $filename,
                    'path' => $filePath,
                    'tipe' => $extension,
                    'ukuran' => $file->getSize(),
                ]);
            }
        }

        // 3. Buat log
        TrackRLog::create([
            'track_r_document_id' => $doc->id,
            'aksi' => 'kirim',
            'dari_user_id' => auth()->id(),
            'ke_user_id' => $penerimaId,
            'catatan' => 'Dokumen dikirim',
        ]);
    });

    return redirect()->route('track-r.index')->with('success', 'Dokumen berhasil dikirim');
}

    /* =========================
       DETAIL DENGAN VALIDASI AKSES
    ========================= */
    public function show($id)
    {
        $document = TrackRDocument::with([
            'logs.dariUser',
            'logs.keUser',
            'pengirim',
            'penerima',
            'fotos'
        ])->findOrFail($id);

        // Validasi akses
        $this->authorizeDocumentAccess($document);

        return view('track_r.show', compact('document'));
    }

    /* =========================
       DOWNLOAD FOTO DENGAN VALIDASI AKSES
    ========================= */
    public function downloadFoto($documentId, $fotoId)
    {
        $document = TrackRDocument::findOrFail($documentId);
        
        // Validasi akses
        $this->authorizeDocumentAccess($document);

        $foto = TrackRFoto::where('track_r_document_id', $documentId)
                         ->findOrFail($fotoId);

        $path = storage_path('app/private/' . $foto->path);
        
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $foto->nama_file);
    }

    /* =========================
       DELETE FOTO - DISABLE
       Foto tidak boleh dihapus untuk menjaga keaslian dokumen
    ========================= */
    public function deleteFoto($documentId, $fotoId)
    {
        abort(403, 'Fitur hapus foto tidak diizinkan. Foto dokumen harus tetap tersedia untuk keaslian.');
    }

    /* =========================
       TERIMA DENGAN VALIDASI AKSES
    ========================= */
    public function terima($id)
    {
        DB::transaction(function () use ($id) {
            $doc = TrackRDocument::findOrFail($id);

            // Hanya penerima yang bisa menerima
            if (auth()->id() !== $doc->penerima_id) {
                abort(403, 'Hanya penerima yang dapat menerima dokumen');
            }

            $doc->update([
                'status' => 'diterima',
            ]);

            TrackRLog::create([
                'track_r_document_id' => $doc->id,
                'aksi' => 'terima',
                'dari_user_id' => auth()->id(),
                'ke_user_id' => auth()->id(),
                'catatan' => 'Dokumen diterima',
            ]);
        });

        return back()->with('success', 'Dokumen diterima');
    }

    /* =========================
       TOLAK DENGAN VALIDASI AKSES
    ========================= */
    public function tolak(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $id) {
            $doc = TrackRDocument::findOrFail($id);

            // Hanya penerima yang bisa menolak
            if (auth()->id() !== $doc->penerima_id) {
                abort(403, 'Hanya penerima yang dapat menolak dokumen');
            }

            $doc->update(['status' => 'ditolak']);

            TrackRLog::create([
                'track_r_document_id' => $doc->id,
                'aksi' => 'tolak',
                'dari_user_id' => auth()->id(),
                'catatan' => $request->catatan,
            ]);
        });

        return back()->with('success', 'Dokumen ditolak');
    }

    /* =========================
       TERUSKAN DENGAN VALIDASI AKSES
    ========================= */
    public function teruskan(Request $request, $id)
    {
        $request->validate([
            'penerima_id' => 'required|exists:users,id',
            'catatan' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $id) {
            $doc = TrackRDocument::findOrFail($id);

            // Hanya penerima yang bisa meneruskan
            if (auth()->id() !== $doc->penerima_id) {
                abort(403, 'Hanya penerima yang dapat meneruskan dokumen');
            }

            if ($doc->status === 'selesai') {
                abort(403, 'Dokumen sudah selesai');
            }

            // Pastikan tidak meneruskan ke diri sendiri
            if ($request->penerima_id == auth()->id()) {
                abort(403, 'Tidak dapat meneruskan dokumen ke diri sendiri');
            }

            $doc->update([
                'status' => 'diteruskan',
                'penerima_id' => $request->penerima_id,
            ]);

            TrackRLog::create([
                'track_r_document_id' => $doc->id,
                'aksi' => 'teruskan',
                'dari_user_id' => auth()->id(),
                'ke_user_id' => $request->penerima_id,
                'catatan' => $request->catatan ?? 'Dokumen diteruskan',
            ]);
        });

        return back()->with('success', 'Dokumen diteruskan');
    }

    /* =========================
       PDF DENGAN VALIDASI AKSES
    ========================= */
    public function pdf($id)
    {
        $document = TrackRDocument::with([
            'logs.dariUser',
            'logs.keUser',
            'pengirim',
            'penerima',
            'fotos'
        ])->findOrFail($id);

        // Validasi akses
        $this->authorizeDocumentAccess($document);

        $pdf = Pdf::loadView('track_r.pdf', compact('document'));
        return $pdf->download('track_r_' . $document->nomor_dokumen . '.pdf');
    }

    /* =========================
       PRIVATE METHOD: VALIDASI AKSES DOKUMEN
    ========================= */
    private function authorizeDocumentAccess($document)
    {
        if (auth()->id() !== $document->pengirim_id && auth()->id() !== $document->penerima_id) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini');
        }
    }
}