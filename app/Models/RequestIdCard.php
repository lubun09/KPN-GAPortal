<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestIdCard extends Model
{
    use HasFactory;

    protected $table = 'request_idcard';

    protected $fillable = [
        'nik',
        'nama',
        'kategori',
        'bisnis_unit_id',
        'tanggal_join',
        'masa_berlaku',
        'sampai_tanggal',
        'nomor_kartu',
        'foto',
        'bukti_bayar',
        'keterangan',
        'status',
        'user_id',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'rejected_by',
        'rejected_at'
    ];

    protected $casts = [
        'tanggal_join' => 'date',
        'masa_berlaku' => 'date',
        'sampai_tanggal' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Aktifkan timestamps karena kolom ada di database
    public $timestamps = true;

    // Relasi ke user yang membuat
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke user yang approve
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Relasi ke user yang reject
    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Relasi ke bisnis unit
    public function bisnisUnit()
    {
        return $this->belongsTo(BisnisUnit::class, 'bisnis_unit_id', 'id_bisnis_unit');
    }

    // Accessor untuk status
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    // Accessor untuk kategori
    public function getKategoriTextAttribute()
    {
        return str_replace('_', ' ', ucfirst($this->kategori));
    }

    // Cek apakah bisa diproses
    public function getCanProcessAttribute()
    {
        return $this->status === 'pending';
    }
}