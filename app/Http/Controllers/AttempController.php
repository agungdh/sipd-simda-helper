<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class AttempController extends Controller
{
    public function createAttemp(Request $request)
    {
        return DB::table('attemp')->insertGetId([
            'created_at' => date('Y-m-d H:i:s'),
            'keterangan' => $request->keterangan,
        ]);
    }

    public function createAttempLog(Request $request)
    {
        return DB::table('attemp_log')->insertGetId([
            'created_at' => date('Y-m-d H:i:s'),
            'id_attemp' => $request->id_attemp,
            'value' => $request->value,
        ]);
    }
}
