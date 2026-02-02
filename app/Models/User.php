<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use Notifiable;
    
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'sso_uid',
        'employee_no', // Kolom penting untuk matching
        'first_name',
        'last_name',
        'company_name',
        'office_city',
        'office_mobile',
        'login_type'
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    // Relationship ke tb_pelanggan via id_login
    public function pelanggan()
    {
        return $this->hasOne(Pelanggan::class, 'id_login', 'id');
    }
    
    // Method untuk mencari pelanggan berdasarkan employee_no atau username
    public function findMatchingPelanggan()
    {
        // Cari berdasarkan employee_no
        if ($this->employee_no) {
            $pelanggan = Pelanggan::where('employee_no', $this->employee_no)->first();
            if ($pelanggan) {
                return $pelanggan;
            }
        }
        
        // Cari berdasarkan username_pelanggan (bandingkan dengan username atau email)
        if ($this->username) {
            $pelanggan = Pelanggan::where('username_pelanggan', $this->username)->first();
            if ($pelanggan) {
                return $pelanggan;
            }
        }
        
        // Cari berdasarkan email
        $pelanggan = Pelanggan::where('email_pelanggan', $this->email)->first();
        if ($pelanggan) {
            return $pelanggan;
        }
        
        return null;
    }
    
    // Method untuk link user dengan pelanggan
    public function linkWithPelanggan(Pelanggan $pelanggan)
    {
        $pelanggan->id_login = $this->id;
        $pelanggan->save();
        return $pelanggan;
    }
}