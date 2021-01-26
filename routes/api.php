<?php

use Illuminate\Http\Request;

Route::post('/responseLog', 'ResponseController@responseLog');
Route::get('/responseLog/test', 'ResponseController@test');

Route::post('/attemp', 'AttempController@createAttemp');
Route::post('/attemp/log', 'AttempController@createAttempLog');

Route::get('/printData', 'PrintDataController@index');
// Route::post('/mapper', 'MapperController@index');
// Route::get('/mapper/test', 'MapperController@test');
Route::get('/mapper', 'MapperController@index');