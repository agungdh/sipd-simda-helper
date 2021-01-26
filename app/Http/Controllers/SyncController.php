<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class SyncController extends Controller
{
    public function subKegiatan(Request $request)
    {
        if (!$request->kode_sub_giat || !$request->tahun_anggaran) {
            return response()->json([], 400);
        }

        $SIPD_SubKegiatan = DB::connection('mysql_simda_sipd')->table('data_sub_keg_bl')->where([
            'active' => 1,
            'tahun_anggaran' => $request->tahun_anggaran,
            'kode_sub_giat' => $request->kode_sub_giat,
        ])->first();

        if (!$SIPD_SubKegiatan) {
            return response()->json(['msg' => 'SIPD_SubKegiatan Not Found'], 400);
        }

        // $SIPD_LokasiSubKegiatan = DB::connection('mysql_simda_sipd')->table('data_lokasi_sub_keg')->where([
        //     'active' => 1,
        //     'tahun_anggaran' => $request->tahun_anggaran,
        //     'kode_sbl' => $SIPD_SubKegiatan->kode_sbl,
        // ])->first();

        // if (!$SIPD_LokasiSubKegiatan) {
        //     return response()->json(['msg' => 'SIPD_LokasiSubKegiatan Not Found'], 400);
        // }

        $SIPD_DanaSubKegiatan = DB::connection('mysql_simda_sipd')->table('data_dana_sub_keg')->where([
            'active' => 1,
            'tahun_anggaran' => $request->tahun_anggaran,
            'kode_sbl' => $SIPD_SubKegiatan->kode_sbl,
        ])->first();

        if (!$SIPD_DanaSubKegiatan) {
            return response()->json(['msg' => 'SIPD_DanaSubKegiatan Not Found'], 400);
        }

        $SIMDA_SumberDana = DB::connection('sqlsrv_simda')->table('ref_sumber_dana')->where([
            'kd_sumber' => $SIPD_DanaSubKegiatan->iddana,
        ])->first();

        if (!$SIMDA_SumberDana) {
            return response()->json(['msg' => 'SIMDA_SumberDana Not Found'], 400);
        }

        if ($SIMDA_SumberDana) {
            $kd_sumber_dana = $SIMDA_SumberDana->kd_sumber;
        } else {
            $kd_sumber_dana = 255;
        }

        $nama_sub_giat = explode(' ', $SIPD_SubKegiatan->nama_sub_giat);
        unset($nama_sub_giat[0]);
        $nama_sub_giat = implode(' ', $nama_sub_giat);

        $SIMDA_KegiatanMapping = DB::connection('sqlsrv_simda')->table('ref_kegiatan_mapping as rkm')->join('ref_sub_kegiatan90 as rsk', function($join) {
            $join->on('rkm.kd_urusan90', '=', 'rsk.kd_urusan');
            $join->on('rkm.kd_bidang90', '=', 'rsk.kd_bidang');
            $join->on('rkm.kd_program90', '=', 'rsk.kd_program');
            $join->on('rkm.kd_kegiatan90', '=', 'rsk.kd_kegiatan');
            $join->on('rkm.kd_sub_kegiatan', '=', 'rsk.kd_sub_kegiatan');
        })->where('rsk.nm_sub_kegiatan', $nama_sub_giat)->first();

        if (!$SIMDA_KegiatanMapping) {
            return response()->json(['msg' => 'SIMDA_KegiatanMapping Not Found'], 400);
        }

        // $SIPD_SubKegiatanIndikator = DB::connection('mysql_simda_sipd')->table('data_sub_keg_indikator')->where([
        //     'active' => 1,
        //     'tahun_anggaran' => $request->tahun_anggaran,
        //     'kode_sbl' => $SIPD_SubKegiatan->kode_sbl,
        // ])->first();

        // if (!$SIPD_SubKegiatanIndikator) {
        //     return response()->json(['msg' => 'SIPD_SubKegiatanIndikator Not Found'], 400);
        // }

        // $SIPD_KegiatanIndikatorHasil = DB::connection('mysql_simda_sipd')->table('data_keg_indikator_hasil')->where([
        //     'active' => 1,
        //     'tahun_anggaran' => $request->tahun_anggaran,
        //     'kode_sbl' => $SIPD_SubKegiatan->kode_sbl,
        // ])->first();

        // if (!$SIPD_KegiatanIndikatorHasil) {
        //     return response()->json(['msg' => 'SIPD_KegiatanIndikatorHasil Not Found'], 400);
        // }

        $SIPD_RKAs = DB::connection('mysql_simda_sipd')->table('data_rka')->where([
            'active' => 1,
            'tahun_anggaran' => $request->tahun_anggaran,
            'kode_sbl' => $SIPD_SubKegiatan->kode_sbl,
        ])->get();

        if (count($SIPD_RKAs) < 1) {
            return response()->json(['msg' => 'SIPD_RKA Not Found'], 400);
        }

        $SIPD_WPOption = DB::connection('mysql_simda_sipd')->table('wp_options')->where([
            'option_name' => '_crb_unit_' . $SIPD_SubKegiatan->id_skpd,
        ])->first();

        if (!$SIPD_WPOption) {
            return response()->json(['msg' => 'SIPD_WPOption Not Found'], 400);
        }
        
        $kd_unit_simda = explode('.', $SIPD_WPOption->option_value);
            
        $_kd_urusan = $kd_unit_simda[0];
        $_kd_bidang = $kd_unit_simda[1];
        $kd_unit = $kd_unit_simda[2];
        $kd_sub_unit = $kd_unit_simda[3];

        $akun_all = array();
        $rinc_all = array();
        foreach ($SIPD_RKAs as $kk => $rk) {
            if(empty($akun_all[$rk->kode_akun])){
                $akun_all[$rk->kode_akun] = array();  
            }
            if(empty($akun_all[$rk->kode_akun][$rk->subs_bl_teks.' | '.$rk->ket_bl_teks])){
                $akun_all[$rk->kode_akun][$rk->subs_bl_teks.' | '.$rk->ket_bl_teks] = array();    
            }
            $akun_all[$rk->kode_akun][$rk->subs_bl_teks.' | '.$rk->ket_bl_teks][] = $rk;
        }

        // $totalHargaKegiatanSIPD = 0;
        // foreach ($SIPD_RKAs as $SIPD_RKA) {
        //     $totalHargaKegiatanSIPD += $SIPD_RKA->total_harga;

        //     $akun = explode('.', $SIPD_RKA->kode_akun);
            
        //     $SIMDA_KegiatanCheck = DB::connection('sqlsrv_simda')->table('ta_kegiatan')->where([
        //         'ket_kegiatan' => $SIMDA_KegiatanMapping->nm_sub_kegiatan,
        //     ])->first();

        //     if (!$SIMDA_KegiatanCheck) {
        //         return response()->json(['msg' => 'SIMDA_KegiatanCheck Not Found', 'ket_kegiatan' => $SIPD_RKA->nama_komponen], 400);
        //     }

        //     $rekMapping = DB::connection('sqlsrv_simda')
        //                     ->table('ref_rek_mapping')
        //                     ->where([
        //                         'kd_rek90_1' => ((int)$akun[0]),
        //                         'kd_rek90_2' => ((int)$akun[1]),
        //                         'kd_rek90_3' => ((int)$akun[2]),
        //                         'kd_rek90_4' => ((int)$akun[3]),
        //                         'kd_rek90_5' => ((int)$akun[4]),
        //                         'kd_rek90_6' => ((int)$akun[5]),
        //                     ])
        //                     ->first();

        //     $params = [
        //         'tahun' => $request->tahun_anggaran,
        //         'kd_urusan' => $_kd_urusan,
        //         'kd_bidang' => $_kd_bidang,
        //         'kd_unit' => $kd_unit,
        //         'kd_sub' => $kd_sub_unit,
        //         'kd_prog' => $SIMDA_KegiatanCheck->kd_prog,
        //         'id_prog' => $SIMDA_KegiatanCheck->id_prog,
        //         'kd_keg' => $SIMDA_KegiatanCheck->kd_keg,
        //         'kd_rek_1' => $rekMapping->kd_rek_1,
        //         'kd_rek_2' => $rekMapping->kd_rek_2,
        //         'kd_rek_3' => $rekMapping->kd_rek_3,
        //         'kd_rek_4' => $rekMapping->kd_rek_4,
        //         'kd_rek_5' => $rekMapping->kd_rek_5,
        //         'kd_sumber' => $kd_sumber_dana,
        //     ];

        //     $dataRka = DB::connection('sqlsrv_simda')
        //                     ->table('ta_belanja')
        //                     ->where($params)
        //                     ->first();           

        //     if (!$dataRka) {
        //         DB::connection('sqlsrv_simda')
        //                     ->table('ta_belanja')
        //                     ->insert($params);
        //     } 
        // }

        // $parsedTotalHargaKegiatanSIPD = number_format($totalHargaKegiatanSIPD,0,',','.');

        return response()->json(compact([
            'SIMDA_KegiatanCheck',
        ]));
    }

    private function CekNull($number, $length=2){
        $l = strlen($number);
        $ret = '';
        for($i=0; $i<$length; $i++){
            if($i+1 > $l){
                $ret .= '0';
            }
        }
        $ret .= $number;
        return $ret;
    }
}
