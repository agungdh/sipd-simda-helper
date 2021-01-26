<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class MapperController extends Controller
{
    public function index(Request $request)
    {
        $ref_kegiatans = DB::connection('sqlsrv_simda')
                            ->table('ref_kegiatan')
                            ->get();

        $adaSimda = [];
        $gakAdaSimda = [];

        foreach ($ref_kegiatans as $ref_kegiatan) {
            $whereData = [
                'kd_urusan' => $ref_kegiatan->kd_urusan,
                'kd_bidang' => $ref_kegiatan->kd_bidang,
                'kd_prog' => $ref_kegiatan->kd_prog,
                'kd_keg' => $ref_kegiatan->kd_keg,
            ];

            $ref_rek_mapping = DB::connection('sqlsrv_simda')
                    ->table('ref_kegiatan_mapping')
                    ->where($whereData)
                    ->get();

            if ($ref_rek_mapping) {
                $adaSimda[] = $ref_rek_mapping;
            } else {
                $gakAdaSimda[] = $whereData;        
            }
        }

        $ref_sub_kegiatan90s = DB::connection('sqlsrv_simda')
                        ->table('ref_sub_kegiatan90')
                        ->get();

        $adaSIPD = [];
        $gakAdaSIPD = [];

        foreach ($ref_sub_kegiatan90s as $ref_sub_kegiatan90) {
            $whereData = [
                'kd_urusan90' => $ref_sub_kegiatan90->kd_urusan,
                'kd_bidang90' => $ref_sub_kegiatan90->kd_bidang,
                'kd_program90' => $ref_sub_kegiatan90->kd_program,
                'kd_kegiatan90' => $ref_sub_kegiatan90->kd_kegiatan,
                'kd_sub_kegiatan' => $ref_sub_kegiatan90->kd_sub_kegiatan,
            ];

            $ref_rek_mapping = DB::connection('sqlsrv_simda')
                    ->table('ref_kegiatan_mapping')
                    ->where($whereData)
                    ->get();

            if ($ref_rek_mapping) {
                $adaSIPD[] = $ref_rek_mapping;
            } else {
                $gakAdaSIPD[] = $whereData;        
            }
        }

        return compact([
            'gakAdaSimda',
            'gakAdaSIPD',
            'adaSimda',
            'adaSIPD',
        ]);
    }

    public function indexNabilGendut(Request $request)
    {
        $ref_rek_mappings = DB::connection('sqlsrv_simda')
                    ->table('ref_kegiatan_mapping')
                    ->get();

        $adaSimda = [];
        $gakAdaSimda = [];

        $adaSIPD = [];
        $gakAdaSIPD = [];

        foreach ($ref_rek_mappings as $ref_rek_mapping) {
            $check = DB::connection('sqlsrv_simda')
                    ->table('ref_kegiatan')
                    ->where([
                        'kd_urusan' => $ref_rek_mapping->kd_urusan,
                        'kd_bidang' => $ref_rek_mapping->kd_bidang,
                        'kd_prog' => $ref_rek_mapping->kd_prog,
                        'kd_keg' => $ref_rek_mapping->kd_keg,
                    ])
                    ->first();

            if ($check) {
                $adaSimda[] = $check;
            } else {
                $gakAdaSimda[] = [
                    'kd_urusan' => $ref_rek_mapping->kd_urusan,
                    'kd_bidang' => $ref_rek_mapping->kd_bidang,
                    'kd_prog' => $ref_rek_mapping->kd_prog,
                    'kd_keg' => $ref_rek_mapping->kd_keg,
                ];
            }

            $check = DB::connection('sqlsrv_simda')
                    ->table('ref_sub_kegiatan90')
                    ->where([
                        'kd_urusan' => $ref_rek_mapping->kd_urusan90,
                        'kd_bidang' => $ref_rek_mapping->kd_bidang90,
                        'kd_program' => $ref_rek_mapping->kd_program90,
                        'kd_kegiatan' => $ref_rek_mapping->kd_kegiatan90,
                        'kd_sub_kegiatan' => $ref_rek_mapping->kd_sub_kegiatan,
                    ])
                    ->first();

            if ($check) {
                $adaSIPD[] = $check;
            } else {
                $gakAdaSIPD[] = [
                    'kd_urusan' => $ref_rek_mapping->kd_urusan90,
                    'kd_bidang' => $ref_rek_mapping->kd_bidang90,
                    'kd_program' => $ref_rek_mapping->kd_program90,
                    'kd_kegiatan' => $ref_rek_mapping->kd_kegiatan90,
                    'kd_sub_kegiatan' => $ref_rek_mapping->kd_sub_kegiatan,
                ];
            }
        }

        dd(compact(['adaSimda', 'gakAdaSimda', 'adaSIPD', 'gakAdaSIPD']));
    }

    public function testGALLLLLLL(Request $request)
    {
        // Test Mapping
        // API URL
        $ccc_url = 'http://ip.bpkadlampungtengah.com/sipd-simda-helper/public/api/mapper';

        // Create a new cURL resource
        $ccc_ch = curl_init($ccc_url);

        // Setup request to send json via POST
        $ccc_payload = json_encode(array("data" => 
            // 124
            // [123, 122]
   //       json_encode(
            [
            'kd_rek90_1' => 5,
            'kd_rek90_2' => 2,
            'kd_rek90_3' => 2,
            'kd_rek90_4' => 88,
            'kd_rek90_5' => 88,
            'kd_rek90_6' => 8888,
        ]
    // )
        ));

        // Attach encoded JSON string to the POST fields
        curl_setopt($ccc_ch, CURLOPT_POSTFIELDS, $ccc_payload);

        // Set the content type to application/json
        curl_setopt($ccc_ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        // Return response instead of outputting
        curl_setopt($ccc_ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the POST request
        $ccc_result = curl_exec($ccc_ch);

        // Close cURL resource
        curl_close($ccc_ch);
        // End Test Mapping

        echo $ccc_result;
    }

    public function indexGAGALLLLLL(Request $request)
    {
        // dd($request->all());
        $check = DB::connection('sqlsrv_simda')
                        ->table('ref_rek_mapping')
                        ->where([
                            'kd_rek90_1' => $request->data['kd_rek90_1'],
                            'kd_rek90_2' => $request->data['kd_rek90_2'],
                            'kd_rek90_3' => $request->data['kd_rek90_3'],
                            'kd_rek90_4' => $request->data['kd_rek90_4'],
                            'kd_rek90_5' => $request->data['kd_rek90_5'],
                            'kd_rek90_6' => $request->data['kd_rek90_6'],
                        ])
                        ->first();
        if (!$check) {
            $checkInSIPD = DB::connection('mysql_simda_sipd')->select("
                SELECT 1 AgungDH
                ,id
                ,kode_akun
                ,nama_akun
                ,TRIM(LEADING '0' FROM SUBSTRING_INDEX(kode_akun, '.', 1)) as kd_1
                ,TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 2), '.', -1)) as kd_2
                ,TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 3), '.', -1)) as kd_3
                ,TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 4), '.', -1)) as kd_4
                ,TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 5), '.', -1)) as kd_5
                ,TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 6), '.', -1)) as kd_6
                FROM `data_rka`
                where 1 = 1
                AND TRIM(LEADING '0' FROM SUBSTRING_INDEX(kode_akun, '.', 1)) = ?
                AND TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 2), '.', -1)) = ?
                AND TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 3), '.', -1)) = ?
                AND TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 4), '.', -1)) = ?
                AND TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 5), '.', -1)) = ?
                AND TRIM(LEADING '0' FROM SUBSTRING_INDEX(SUBSTRING_INDEX(kode_akun, '.', 6), '.', -1)) = ?
            ", [
                $request->data['kd_rek90_1'],
                $request->data['kd_rek90_2'],
                $request->data['kd_rek90_3'],
                $request->data['kd_rek90_4'],
                $request->data['kd_rek90_5'],
                $request->data['kd_rek90_6'],
            ]);

            $lastKdRek5 = DB::connection('sqlsrv_simda')
            ->table('ref_rek_5')
            ->where([
                'kd_rek_1' => 99,
                'kd_rek_2' => 1,
                'kd_rek_3' => 1,
                'kd_rek_4' => 1,
            ])
            ->orderBy('kd_rek_5')
            ->first();

            if ($lastKdRek5) {
                $lastKdRek5 = $lastKdRek5->kd_rek_5 - 1;
            } else {
                $lastKdRek5 = 0;
            }

            $checkChild = DB::connection('sqlsrv_simda')
            ->table('ref_rek_5')
            ->where([
                'nm_rek_5' => $checkInSIPD[0]->nama_akun,
            ])->first();

            if (!$checkChild) {
                DB::connection('sqlsrv_simda')
                ->table('ref_rek_5')
                ->insert([
                    'kd_rek_1' => 99,
                    'kd_rek_2' => 1,
                    'kd_rek_3' => 1,
                    'kd_rek_4' => 1,
                    'kd_rek_5' => $lastKdRek5 + 1,
                    'nm_rek_5' => $checkInSIPD[0]->nama_akun,
                ]);
                
                DB::connection('sqlsrv_simda')
                ->table('ref_rek90_6')
                ->insert([
                    'kd_rek90_1' => 99,
                    'kd_rek90_2' => 1,
                    'kd_rek90_3' => 1,
                    'kd_rek90_4' => 1,
                    'kd_rek90_5' => 1,
                    'kd_rek90_6' => $lastKdRek5 + 1,
                    'nm_rek90_6' => $checkInSIPD[0]->nama_akun,
                ]);

                DB::connection('sqlsrv_simda')
                ->table('ref_rek_mapping')
                ->insert([
                    'kd_rek90_1' => 99,
                    'kd_rek90_2' => 1,
                    'kd_rek90_3' => 1,
                    'kd_rek90_4' => 1,
                    'kd_rek90_5' => 1,
                    'kd_rek90_6' => $lastKdRek5 + 1,
                    'kd_rek_1' => 99,
                    'kd_rek_2' => 1,
                    'kd_rek_3' => 1,
                    'kd_rek_4' => 1,
                    'kd_rek_5' => $lastKdRek5 + 1,
                ]);

                return $lastKdRek5 + 1;
            }

        } else {
            return json_encode($check);
        }
    }

    public function testingOnly(Request $request)
    {
        // $status = 'success';
        // $kd_rek90_1 = $request->data['kd_rek90_1'];
        // $kd_rek90_2 = $request->data['kd_rek90_2'];
        // $kd_rek90_3 = $request->data['kd_rek90_3'];
        // $kd_rek90_4 = $request->data['kd_rek90_4'];
        // $kd_rek90_5 = $request->data['kd_rek90_5'];
        // $kd_rek90_6 = $request->data['kd_rek90_6'];

        // DB::table('response_log')->insertGetId([
        //     'created_at' => date('Y-m-d H:i:s'),
        //     'response' => json_encode(compact([
        //         'status',
        //         'kd_rek90_1',
        //         'kd_rek90_2',
        //         'kd_rek90_3',
        //         'kd_rek90_4',
        //         'kd_rek90_5',
        //         'kd_rek90_6',
        //     ])),
        // ]);
    }
}
