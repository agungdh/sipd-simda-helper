<?php

use Illuminate\Http\Request;

Route::post('/sync/kegiatan', 'SyncController@kegiatan');
Route::post('/sync/subKegiatan', 'SyncController@subKegiatan');
Route::post('/sync/subKegiatan/all', 'SyncController@gasLurrr');