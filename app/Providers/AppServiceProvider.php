<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        if (isset($_SERVER['HTTP_REQUESTID'])) {
            Config::set('requestId',  $_SERVER['HTTP_REQUESTID']); // 查日志用
        } else {
            Config::set('requestId', uniqid()); // 查日志用
        }

//        DB::listen(function ($query){  //数据库查询时间
//            $tmp = str_replace('%', '', $query->sql);
//            $tmp = str_replace('?', '"'.'%s'.'"', $tmp);
//            $tmp = vsprintf($tmp, $query->bindings);
//            $tmp = str_replace("\\","",$tmp);
//            \Log::info($tmp.' ; time:'.$query->time);
//        });
    }
}
