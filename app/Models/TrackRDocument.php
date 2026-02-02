<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackRDocument extends Model
{
    protected $table = 'track_r_documents';

    protected $fillable = [
        'nomor_dokumen',
        'judul',
        'keterangan',
        'pengirim_id',
        'penerima_id',
        'status',
    ];

    public function logs()
    {
        return $this->hasMany(TrackRLog::class, 'track_r_document_id')
                    ->orderBy('created_at', 'asc');
    }

    public function fotos()
    {
        return $this->hasMany(TrackRFoto::class, 'track_r_document_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }
}