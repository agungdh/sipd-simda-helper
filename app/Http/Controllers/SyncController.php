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

        $dumpSqlBelanjaRincSub = [];
        $dumpSqlBelanjaRinc = [];
        $dumpSqlBelanja = [];

        $tahun_anggaran = $request->tahun_anggaran;

        $SIPD_SubKegiatan = DB::connection('mysql_simda_sipd')->table('data_sub_keg_bl')->where([
            'active' => 1,
            'tahun_anggaran' => $request->tahun_anggaran,
            'kode_sub_giat' => $request->kode_sub_giat,
        ])->first();

        if (!$SIPD_SubKegiatan) {
            return response()->json(['msg' => 'SIPD_SubKegiatan Not Found'], 400);
        }

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

        $sumber_dana = $kd_sumber_dana;

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

        $belanjaQuerys = [];

        $akun_all = array();
        $rinc_all = array();
        foreach ($SIPD_RKAs as $kk => $rk) {
            // return response()->json(compact(['rk', 'nama_sub_giat']), 400);
            $rk = (array) $rk;

            if(empty($akun_all[$rk['kode_akun']])){
                $akun_all[$rk['kode_akun']] = array();  
            }
            if(empty($akun_all[$rk['kode_akun']][$rk['subs_bl_teks'].' | '.$rk['ket_bl_teks']])){
                $akun_all[$rk['kode_akun']][$rk['subs_bl_teks'].' | '.$rk['ket_bl_teks']] = array();    
            }
            $akun_all[$rk['kode_akun']][$rk['subs_bl_teks'].' | '.$rk['ket_bl_teks']][] = $rk;
        }

        foreach ($akun_all as $kk => $rk) {
            $akun = explode('.', $kk);

             $SIMDA_KegiatanCheck = DB::connection('sqlsrv_simda')->table('ta_kegiatan')->where([
                'ket_kegiatan' => $SIMDA_KegiatanMapping->nm_sub_kegiatan,
            ])->first();

            if (!$SIMDA_KegiatanCheck) {
                return response()->json(['msg' => 'SIMDA_KegiatanCheck Not Found', 'ket_kegiatan' => $SIMDA_KegiatanMapping->nm_sub_kegiatan], 400);
            }

            $rekMapping = DB::connection('sqlsrv_simda')
                        ->table('ref_rek_mapping')
                        ->where([
                            'kd_rek90_1' => ((int)$akun[0]),
                            'kd_rek90_2' => ((int)$akun[1]),
                            'kd_rek90_3' => ((int)$akun[2]),
                            'kd_rek90_4' => ((int)$akun[3]),
                            'kd_rek90_5' => ((int)$akun[4]),
                            'kd_rek90_6' => ((int)$akun[5]),
                        ])
                        ->first();

            $kd_prog = $SIMDA_KegiatanCheck->kd_prog;
            $id_prog = $SIMDA_KegiatanCheck->id_prog;
            $kd_keg = $SIMDA_KegiatanCheck->kd_keg;

            $options = array(
                'query' => "
                DELETE from ta_belanja_rinc_sub
                where 
                    tahun=".$tahun_anggaran."
                    and kd_urusan=".$_kd_urusan."
                    and kd_bidang=".$_kd_bidang."
                    and kd_unit=".$kd_unit."
                    and kd_sub=".$kd_sub_unit."
                    and kd_prog=".$kd_prog."
                    and id_prog=".$id_prog."
                    and kd_keg=".$kd_keg
            );
            // print_r($options); die();
            // $this->CurlSimda($options);

            $options = array(
                'query' => "
                DELETE from ta_belanja_rinc
                where 
                    tahun=".$tahun_anggaran."
                    and kd_urusan=".$_kd_urusan."
                    and kd_bidang=".$_kd_bidang."
                    and kd_unit=".$kd_unit."
                    and kd_sub=".$kd_sub_unit."
                    and kd_prog=".$kd_prog."
                    and id_prog=".$id_prog."
                    and kd_keg=".$kd_keg
            );
            // print_r($options); die();
            // $this->CurlSimda($options);

            $options = array(
                'query' => "
                DELETE from ta_belanja
                where 
                    tahun=".$tahun_anggaran."
                    and kd_urusan=".$_kd_urusan."
                    and kd_bidang=".$_kd_bidang."
                    and kd_unit=".$kd_unit."
                    and kd_sub=".$kd_sub_unit."
                    and kd_prog=".$kd_prog."
                    and id_prog=".$id_prog."
                    and kd_keg=".$kd_keg
            );
            // print_r($options); die();
            // $this->CurlSimda($options);

            $mapping_rek = $this->CurlSimdaSelect(array(
                'query' => "
                    SELECT 
                        * 
                    from ref_rek_mapping
                    where kd_rek90_1=".((int)$akun[0])
                        .' and kd_rek90_2='.((int)$akun[1])
                        .' and kd_rek90_3='.((int)$akun[2])
                        .' and kd_rek90_4='.((int)$akun[3])
                        .' and kd_rek90_5='.((int)$akun[4])
                        .' and kd_rek90_6='.((int)$akun[5])
            ));
            
            if(!empty($mapping_rek)){
                $options = array(
                    'query' => "
                        INSERT INTO ta_belanja (
                            tahun,
                            kd_urusan,
                            kd_bidang,
                            kd_unit,
                            kd_sub,
                            kd_prog,
                            id_prog,
                            kd_keg,
                            kd_rek_1,
                            kd_rek_2,
                            kd_rek_3,
                            kd_rek_4,
                            kd_rek_5,
                            kd_sumber
                        ) VALUES (
                            ".$tahun_anggaran.",
                            ".$_kd_urusan.",
                            ".$_kd_bidang.",
                            ".$kd_unit.",
                            ".$kd_sub_unit.",
                            ".$kd_prog.",
                            ".$id_prog.",
                            ".$kd_keg.",
                            ".$rekMapping->kd_rek_1.",
                            ".$rekMapping->kd_rek_2.",
                            ".$rekMapping->kd_rek_3.",
                            ".$rekMapping->kd_rek_4.",
                            ".$rekMapping->kd_rek_5.",
                            ".$sumber_dana."
                        )"
                );
                // print_r($options); die();
                $this->CurlSimda($options);

                $dumpSqlBelanja[] = $options['query'];

                $belanjaQuerys[] = DB::connection('sqlsrv_simda')->table('ta_belanja')->where([
                    'tahun' => $tahun_anggaran,
                    'kd_urusan' => $_kd_urusan,
                    'kd_bidang' => $_kd_bidang,
                    'kd_unit' => $kd_unit,
                    'kd_sub' => $kd_sub_unit,
                    'kd_prog' => $kd_prog,
                    'id_prog' => $id_prog,
                    'kd_keg' => $kd_keg,
                    'kd_rek_1' => $rekMapping->kd_rek_1,
                    'kd_rek_2' => $rekMapping->kd_rek_2,
                    'kd_rek_3' => $rekMapping->kd_rek_3,
                    'kd_rek_4' => $rekMapping->kd_rek_4,
                    'kd_rek_5' => $rekMapping->kd_rek_5,
                    'kd_sumber' => $sumber_dana,
                ])->first();
                
                $no_rinc = 0;
                foreach ($rk as $kkk => $rkk) {
                    $no_rinc++;
                    $options = array(
                        'query' => "
                            INSERT INTO ta_belanja_rinc (
                                tahun,
                                kd_urusan,
                                kd_bidang,
                                kd_unit,
                                kd_sub,
                                kd_prog,
                                id_prog,
                                kd_keg,
                                kd_rek_1,
                                kd_rek_2,
                                kd_rek_3,
                                kd_rek_4,
                                kd_rek_5,
                                no_rinc,
                                keterangan,
                                kd_sumber
                            ) VALUES (
                                ".$tahun_anggaran.",
                                ".$_kd_urusan.",
                                ".$_kd_bidang.",
                                ".$kd_unit.",
                                ".$kd_sub_unit.",
                                ".$kd_prog.",
                                ".$id_prog.",
                                ".$kd_keg.",
                                ".$rekMapping->kd_rek_1.",
                                ".$rekMapping->kd_rek_2.",
                                ".$rekMapping->kd_rek_3.",
                                ".$rekMapping->kd_rek_4.",
                                ".$rekMapping->kd_rek_5.",
                                ".$no_rinc.",
                                '".str_replace("'", '`', substr($kkk, 0, 255))."',
                                ".$sumber_dana."
                            )"
                    );
                    // print_r($options); die();
                    $this->CurlSimda($options);
    
                    $dumpSqlBelanjaRinc[] = $options['query'];

                    $no_rinc_sub = 0;
                    foreach ($rkk as $kkkk => $rkkk) {
                        $no_rinc_sub++;

                        $komponen = array($rkkk['nama_komponen'], $rkkk['spek_komponen']);
                        $nilai1 = 0;
                        $nilai1_t = 1;
                        if(!empty($rkkk['volum1'])){
                            $nilai1 = $rkkk['volum1'];
                            $nilai1_t = $rkkk['volum1'];
                        }else{
                            $jml_satuan_db = explode(' ', $rkkk['koefisien']);
                            if(!empty($jml_satuan_db) && $jml_satuan_db[0] >= 1){
                                $nilai1 = $jml_satuan_db[0];
                            }
                        }
                        $sat1 = $rkkk['satuan'];
                        if(!empty($rkkk['sat1'])){
                            $sat1 = $rkkk['sat1'];
                        }
                        $nilai2 = 0;
                        $nilai2_t = 1;
                        if(!empty($rkkk['volum2'])){
                            $nilai2 = $rkkk['volum2'];
                            $nilai2_t = $rkkk['volum2'];
                        }
                        $nilai3 = 0;
                        $nilai3_t = 1;
                        if(!empty($rkkk['volum3'])){
                            $nilai3 = $rkkk['volum3'];
                            $nilai3_t = $rkkk['volum3'];
                        }
                        $nilai4_t = 1;
                        if(!empty($rkkk['volum4'])){
                            $nilai4_t = $rkkk['volum4'];
                        }
                        $jml_satuan = $nilai1_t*$nilai2_t*$nilai3_t*$nilai4_t;
                        $options = array(
                            'query' => "
                                INSERT INTO ta_belanja_rinc_sub (
                                    tahun,
                                    kd_urusan,
                                    kd_bidang,
                                    kd_unit,
                                    kd_sub,
                                    kd_prog,
                                    id_prog,
                                    kd_keg,
                                    kd_rek_1,
                                    kd_rek_2,
                                    kd_rek_3,
                                    kd_rek_4,
                                    kd_rek_5,
                                    no_rinc,
                                    no_id,
                                    sat_1,
                                    nilai_1,
                                    sat_2,
                                    nilai_2,
                                    sat_3,
                                    nilai_3,
                                    satuan123,
                                    jml_satuan,
                                    nilai_rp,
                                    total,
                                    keterangan
                                ) VALUES (
                                    ".$tahun_anggaran.",
                                    ".$_kd_urusan.",
                                    ".$_kd_bidang.",
                                    ".$kd_unit.",
                                    ".$kd_sub_unit.",
                                    ".$kd_prog.",
                                    ".$id_prog.",
                                    ".$kd_keg.",
                                    ".$rekMapping->kd_rek_1.",
                                    ".$rekMapping->kd_rek_2.",
                                    ".$rekMapping->kd_rek_3.",
                                    ".$rekMapping->kd_rek_4.",
                                    ".$rekMapping->kd_rek_5.",
                                    ".$no_rinc.",
                                    ".$no_rinc_sub.",
                                    '".str_replace("'", '`', substr($sat1, 0, 10))."',
                                    ". str_replace(',', '.', $nilai1) .",
                                    '".str_replace("'", '`', substr($rkkk['sat2'], 0, 10))."',
                                    ". str_replace(',', '.', $nilai2) .",
                                    '".str_replace("'", '`', substr($rkkk['sat3'], 0, 10))."',
                                    ". str_replace(',', '.', $nilai3) .",
                                    '".str_replace("'", '`', substr($rkkk['satuan'], 0, 50))."',
                                    ". str_replace(',', '.', $jml_satuan) .",
                                    ". str_replace(',', '.', $rkkk['harga_satuan']) .",
                                    ". str_replace(',', '.', $rkkk['total_harga']) .",
                                    '".str_replace("'", '`', substr(implode(' | ', $komponen), 0, 255))."'
                                )"
                        );
                        // print_r($options); die();
                        $this->CurlSimda($options);

                        $dumpSqlBelanjaRincSub[] = $options['query'];

                    }
                }

            }else{
                $ret['status'] = 'error';
                $ret['simda_status'] = 'error';
                $ret['simda_msg'] = 'Kode akun '.$rk['kode_akun'].' tidak ditemukan di ref_rek_mapping SIMDA';
            }
        }        

        $dumpSqlBelanjaRincSub = count($dumpSqlBelanjaRincSub);
        $dumpSqlBelanjaRinc = count($dumpSqlBelanjaRinc);
        $dumpSqlBelanja = count($dumpSqlBelanja);

        $allDumpSql = compact([
            'dumpSqlBelanjaRincSub',
            'dumpSqlBelanjaRinc',
            'dumpSqlBelanja',
        ]);

        return response()->json(compact([
            'allDumpSql',
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

    private function CurlSimdaSelect($options, $debug=false)
    {
        return DB::connection('sqlsrv_simda')->select($options['query']);
    }

    private function CurlSimda($options, $debug=false, $error = false)
    {
        if ($error) {
                return DB::connection('sqlsrv_simda')->insert($options['query']);
        } else {
            try {
                return DB::connection('sqlsrv_simda')->insert($options['query']);
            } catch (\Exception $e) {

            }
        }
    }
}
