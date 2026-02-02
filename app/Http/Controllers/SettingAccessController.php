<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingAccessController extends Controller
{
    public function index(Request $request)
    {
        $username = $request->username;

        $users = DB::table('tb_pelanggan')
            ->orderBy('nama_pelanggan')
            ->get();

        $dashCols = DB::select("SHOW COLUMNS FROM tb_access_dash");
        $menuCols = DB::select("SHOW COLUMNS FROM tb_access_menu");

        $dashData = null;
        $menuData = null;
        $selectedUserName = '';

        if ($username) {
            $dashData = DB::table('tb_access_dash')
                ->where('username_access', $username)
                ->first();

            $menuData = DB::table('tb_access_menu')
                ->where('username', $username)
                ->first();

            $selected = $users->firstWhere('username_pelanggan', $username);
            if ($selected) {
                $selectedUserName =
                    $selected->nama_pelanggan .
                    ' (' . $selected->username_pelanggan . ')';
            }
        }

        return view('setting-access.index', compact(
            'users',
            'username',
            'dashCols',
            'menuCols',
            'dashData',
            'menuData',
            'selectedUserName'
        ));
    }

    public function store(Request $request)
    {
        $username = $request->username;

        // DASHBOARD
        $dashCols = DB::select("SHOW COLUMNS FROM tb_access_dash");
        $dashUpdate = [];

        foreach ($dashCols as $c) {
            if (in_array($c->Field, ['id_access', 'username_access', 'bu_access'])) continue;
            $dashUpdate[$c->Field] = isset($request->dash[$c->Field]) ? 1 : 0;
        }

        DB::table('tb_access_dash')
            ->updateOrInsert(
                ['username_access' => $username],
                array_merge(['username_access' => $username], $dashUpdate)
            );

        // MENU
        $menuCols = DB::select("SHOW COLUMNS FROM tb_access_menu");
        $menuUpdate = [];

        foreach ($menuCols as $c) {
            if (in_array($c->Field, ['id', 'username'])) continue;
            $menuUpdate[$c->Field] = isset($request->menu[$c->Field]) ? 1 : 0;
        }

        DB::table('tb_access_menu')
            ->updateOrInsert(
                ['username' => $username],
                array_merge(['username' => $username], $menuUpdate)
            );

        return redirect()->back()->with('success', 'Akses berhasil disimpan');
    }
}
