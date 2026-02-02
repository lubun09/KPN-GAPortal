<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckIDCardAccess
{
    public function handle(Request $request, Closure $next, string $type = 'list'): Response
    {
        $username = Auth::user()->username;
        
        // Super admin selalu memiliki akses
        if ($username == 'admin') {
            return $next($request);
        }
        
        // Cek akses berdasarkan tipe
        $hasAccess = false;
        
        switch ($type) {
            case 'list':
                $hasAccess = DB::table('tb_access_menu')
                    ->where('username', $username)
                    ->where('list_idcard', 1)
                    ->exists();
                break;
                
            case 'detail':
                $hasAccess = DB::table('tb_access_menu')
                    ->where('username', $username)
                    ->where('detail_idcard', 1)
                    ->exists();
                break;
                
            case 'proses':
                $hasAccess = DB::table('tb_access_menu')
                    ->where('username', $username)
                    ->where('proses_idcard', 1)
                    ->exists();
                break;
                
            case 'request':
                // Untuk request, cek apakah punya salah satu akses
                $hasAccess = DB::table('tb_access_menu')
                    ->where('username', $username)
                    ->where(function($query) {
                        $query->where('list_idcard', 1)
                              ->orWhere('detail_idcard', 1)
                              ->orWhere('proses_idcard', 1);
                    })
                    ->exists();
                break;
                
            default:
                $hasAccess = false;
        }
        
        if (!$hasAccess) {
            return redirect()->route('no-access')->with('error', 
                'Anda tidak memiliki akses untuk halaman ini. Username: ' . $username);
        }
        
        return $next($request);
    }
}