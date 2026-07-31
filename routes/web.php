<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/app/apple-app-site-association', function () {
    return '{
    "applinks": {
        "apps": [],
        "details": [
            {
                "appID": "6XHSTK44S3.com.from.starwisdomctew",
                "paths": ["*"]
            }
        ]
    }
}';
});

Route::get('/clear/service',function (Request $request){
    $requestKey = $request->get('key');
    $key = env('CLEAR_KEY');
    if(!isset($requestKey)){
        return response('key is not valid');

    }
    if($key != $requestKey){
        return response('key is not valid');
    }

    $script_path = "/www/wwwroot/clear.sh";
    if (file_exists($script_path)) {
        // 执行并捕获标准输出和错误输出
        $result = shell_exec("sudo $script_path 2>&1");

        echo "<h3>Execution Report:</h3>";
        echo "<pre>$result</pre>";
    } else {
        echo "Error: Reset script not found at $script_path";
    }

    return response('success');
});
