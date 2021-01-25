<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class ResponseController extends Controller
{
    public function responseLog(Request $request)
    {
        DB::table('response_log')->insert([
            'created_at' => date('Y-m-d H:i:s'),
            'response' => json_encode($request->response),
        ]);

        return 'done';
    }

    public function test(){
    	dd(DB::table('response_log')->get());
        // API URL
        $url = url('/') . '/api/responseLog';
        
        // Create a new cURL resource
        $ch = curl_init($url);

        // Setup request to send json via POST
        $data = array(
            'username' => 'test',
            'password' => '123'
        );
        $payload = json_encode(array("response" => $data));

        // Attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the POST request
        $result = curl_exec($ch);

        // Close cURL resource
        curl_close($ch);

        // return $result;
        echo $result;
    }
}
