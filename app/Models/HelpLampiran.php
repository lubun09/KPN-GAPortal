<?php
// app/Models/HelpLampiran.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpLampiran extends Model
{
    protected $table = 'db_help_lampiran';
    protected $fillable = [
        'tiket_id', 'pengguna_id', 'path_file', 'nama_file',
        'tipe_file', 'ukuran_file', 'deskripsi', 'tipe'
    ];
    
    protected $appends = ['url_file'];
    
    public function tiket()
    {
        return $this->belongsTo(HelpTiket::class, 'tiket_id');
    }
    
    public function pengguna()
    {
        return $this->belongsTo(Pelanggan::class, 'pengguna_id');
    }
    
    public function getUrlFileAttribute()
    {
        return asset('storage/' . $this->path_file);
    }
}