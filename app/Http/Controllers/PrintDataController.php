<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class PrintDataController extends Controller
{
    public function indexGagal(Request $request)
    {

    }

    public function indexGagal3(Request $request)
    {
        $dataRKASimdas = DB::connection('sqlsrv_simda')
                            ->table('ta_belanja_rinc_sub as tbrs')
                            ->select('rrm.*', 'tbrs.*')
                            ->join('ref_rek_mapping as rrm', function($join)
                             {
                                 $join->on('tbrs.kd_rek_1', '=', 'rrm.kd_rek_1');
                                 $join->on('tbrs.kd_rek_2', '=', 'rrm.kd_rek_2');
                                 $join->on('tbrs.kd_rek_3', '=', 'rrm.kd_rek_3');
                                 $join->on('tbrs.kd_rek_4', '=', 'rrm.kd_rek_4');
                                 $join->on('tbrs.kd_rek_5', '=', 'rrm.kd_rek_5');
                             })
                            ->get();

        $id_data_rkas = [];
        foreach ($dataRKASimdas as $dataRKASimda) {
            dd($dataRKASimda);
            $checks = DB::connection('mysql_simda_sipd')->select("
                SELECT 1 AgungDH
                ,id
                ,kode_akun
                ,REPLACE(SUBSTRING_INDEX(kode_akun, '.', 1), '0', '') as kd_1
                ,REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 2), '.', -1)
                , '0', '') as kd_2
                ,REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 3), '.', -1)
                , '0', '') as kd_3
                ,REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 4), '.', -1)
                , '0', '') as kd_4
                ,REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 5), '.', -1)
                , '0', '') as kd_5
                ,REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 6), '.', -1)
                , '0', '') as kd_6
                FROM `data_rka`
                where 1 = 1
                AND REPLACE(SUBSTRING_INDEX(kode_akun, '.', 1), '0', '') = ?
                AND REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 2), '.', -1)
                , '0', '') = ?
                AND REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 3), '.', -1)
                , '0', '') = ?
                AND REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 4), '.', -1)
                , '0', '') = ?
                AND REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 5), '.', -1)
                , '0', '') = ?
                AND REPLACE(
                    SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 6), '.', -1)
                , '0', '') = ?
            ", [
                $dataRKASimda->kd_rek90_1,
                $dataRKASimda->kd_rek90_2,
                $dataRKASimda->kd_rek90_3,
                $dataRKASimda->kd_rek90_4,
                $dataRKASimda->kd_rek90_5,
                $dataRKASimda->kd_rek90_6,
            ]);

            foreach ($checks as $check) {
                $id_data_rkas[] = $check->id;
            }
        }

        dd($id_data_rkas);
    }

    public function indexGagal2(Request $request)
    {
        $attemps = DB::table('attemp as a')->select(
            'a.*',
            DB::raw('SUBSTRING(keterangan, 24) as kode_sbl'),
        )->get();

        $total = 0;
        $countTotal = 0;

        foreach ($attemps as $attemp) {
            $data_rkas = DB::connection('mysql_simda_sipd')->table('data_rka')->where('kode_sbl', $attemp->kode_sbl)->get();

            foreach ($data_rkas as $data_rka) {
                $total += $data_rka->total_harga;

                $countTotal++;
            }
        }

        dd(compact(['total', 'countTotal']));
    }

    public function indexGagal(Request $request)
    {
        $attemps = DB::table('attemp as a')->select(
            'a.*',
            DB::raw('SUBSTRING(keterangan, 24) as kode_sbl'),
        )->get();

        $totalPagu = 0;
        $countPagu = 0;

        foreach ($attemps as $attemp) {
            $attemp_logs = DB::table('attemp_log as al')->select(
                'al.*',
            )->where('id_attemp', $attemp->id)->get();

            foreach ($attemp_logs as $attemp_log) {
                $parsedData = json_decode($attemp_log->value);
                $parsedDataAdded = $parsedData->addeddData;
                
                $whereTemp = [];
                
                $whereTemp['tahun'] = $parsedDataAdded->tahun;
                $whereTemp['kd_urusan'] = $parsedDataAdded->kd_urusan;
                $whereTemp['kd_bidang'] = $parsedDataAdded->kd_bidang;
                $whereTemp['kd_unit'] = $parsedDataAdded->kd_unit;
                $whereTemp['kd_sub'] = $parsedDataAdded->kd_sub;
                $whereTemp['kd_prog'] = $parsedDataAdded->kd_prog;
                $whereTemp['id_prog'] = $parsedDataAdded->id_prog;
                $whereTemp['kd_keg'] = $parsedDataAdded->kd_keg;
                $whereTemp['kd_rek_1'] = $parsedDataAdded->kd_rek_1;
                $whereTemp['kd_rek_2'] = $parsedDataAdded->kd_rek_2;
                $whereTemp['kd_rek_3'] = $parsedDataAdded->kd_rek_3;
                $whereTemp['kd_rek_4'] = $parsedDataAdded->kd_rek_4;
                $whereTemp['kd_rek_5'] = $parsedDataAdded->kd_rek_5;
                $whereTemp['no_rinc'] = $parsedDataAdded->no_rinc;
                // $whereTemp['no_id'] = $parsedDataAdded->no_id;
                // dd($whereTemp);
                $dataRKASimdas = DB::connection('sqlsrv_simda')->table('ta_belanja_rinc_sub')->where($whereTemp)->get();

                foreach ($dataRKASimdas as $dataRKASimda) {
                    $totalPagu += $dataRKASimda->total;
                    $countPagu++;
                }
            }
        }

        dd(compact(['totalPagu', 'countPagu']));
    }
}
