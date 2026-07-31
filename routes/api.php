<?php

use App\Http\Controllers\Home\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Home\VehicleController;
use App\Http\Controllers\Home\VenueController;
use App\Http\Controllers\Home\UserController;
use App\Http\Controllers\Home\AgentController;
use App\Http\Controllers\Home\IndexController;
use App\Http\Controllers\Home\NoticeController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'checkAesEntry'], function () { //所有接口走加解密
    Route::post('/login/loginIn', [LoginController::class, 'login']);//前台登陆
    Route::post('/login/save', [LoginController::class, 'register']);//前台注册
    Route::post('/get/login/code', [LoginController::class, 'getLoginCode']);//获取验证码
    Route::post('/one/key/login', [LoginController::class, 'oneKeyLogin']);
    Route::post('/app/version', [IndexController::class, 'appVersion']);
    Route::post('/user/app/version', [IndexController::class, 'appVersion']);

    Route::group(['middleware'=>'CheckToken'], function () { //登陆后的接口走token校验
        //代理商端
        Route::post('/login/logout', [LoginController::class, 'logout']);//前台退出
        Route::post('/startup/page', [IndexController::class, 'startupPage']); //用户端启动页
        Route::post('/notice', [NoticeController::class, 'notice']);


        //代理->前台->车辆管理页面
        Route::post('/vehicle/list', [VehicleController::class, 'vehicleList']);//车辆列表
        Route::post('/delete/vehicle', [VehicleController::class, 'deleteVehicle']);//删除车辆
        Route::post('/binding/venue', [VehicleController::class, 'bindingVenue']);//车辆绑定场地
        Route::post('/down/vehicle', [VehicleController::class, 'downVehicle']);//车辆下架
//        Route::post('/unbinding/venue', [VehicleController::class, 'unBindingVenue']);//车辆解绑场地
        Route::post('/add/vehicle', [VehicleController::class, 'addVehicle']); //添加车辆
        Route::post('/vehicle/detail', [VehicleController::class, 'vehicleDetail']); //车辆详情
        Route::post('/vehicle/detail/save', [VehicleController::class, 'vehicleDetailSave']); //车辆保存
        Route::post('/update/vehicle', [VehicleController::class, 'updateVehicle']); //车辆编辑
        Route::post('/reset/default/channel', [VehicleController::class, 'vehicleDetailReset']); //车辆配置重置

        //代理->前台->车辆告警页面
        Route::post('/processing/alarm', [VehicleController::class, 'processingAlarm']); //处理完成
        Route::post('/processing/alarm/list', [VehicleController::class, 'processingAlarmList']); //告警列表
        Route::post('/processing/alarm/delete', [VehicleController::class, 'processingAlarmDelete']); //处理完成

        //代理->前台->我的
        Route::post('/agent/mine', [AgentController::class, 'agentMine']); //我的
        Route::post('/set/agent/wallet/password', [AgentController::class, 'setAgentWalletPassword']); //设置钱包密码
        Route::post('/check/agent/wallet/password', [AgentController::class, 'checkAgentWalletPassword']); //验证密码

        Route::post('/agent/driving/record', [AgentController::class, 'agentDrivingRecord']);//我的-驾驶记录
        Route::post('/agent/driving', [AgentController::class, 'agentDriving']);//我的-正在驾驶
        Route::post('/agent/change/password', [LoginController::class, 'agentChangePassword']);//代理前台-设置-修改密码
        Route::post('/agent/wallet/log', [AgentController::class, 'agentWalletLog']);//代理前台-钱包

        //提现晚点做
        Route::post('/agent/withdraw', [AgentController::class, 'agentWithdraw']);//代理商提现
        Route::post('/agent/withdraw/record', [AgentController::class, 'agentWithdrawLog']);//代理商提现


        //代理->前台->我的场地
        Route::post('/venue/list', [VenueController::class, 'venueList']);//场地列表
        Route::post('/create/venue', [VenueController::class, 'createVenue']);//场地新增
        Route::post('/venue/detail', [VenueController::class, 'venueDetail']);//场地详情
        Route::post('/update/venue', [VenueController::class, 'updateVenue']);//场地编辑
        Route::post('/venue/business',[VenueController::class, 'venueBusiness']);
        Route::post('/venue/count', [VenueController::class, 'venueCount']); //车辆统计
        Route::post('/delete/venue', [VenueController::class, 'venueDelete']); //车辆编辑
        Route::post('/chack/start/driving', [IndexController::class, 'chackStartDriving']); //查询预约


        Route::post('/start/driving',[IndexController::class,'startDriving']); //开始驾驶
        Route::post('/v2/start/driving',[IndexController::class,'startDriving']); //开始驾驶v2

        Route::post('/update/vehicle/battery',[VehicleController::class,'updateVehicleBattery']);

        Route::post('/agent/change/head/shot', [LoginController::class, 'changeHeadShot']);//代理商-设置-修改头像

        //用户R
        Route::prefix('user')->group(function () {
            Route::post('/start/driving',[IndexController::class,'startDriving']); //开始驾驶
            Route::post('/lock/driving',[IndexController::class,'lockDriving']); //开始驾驶
            Route::post('/v2/start/driving',[IndexController::class,'startDriving']); //开始驾驶v2

            Route::post('/reservation', [IndexController::class, 'reservation']); //预约
            Route::post('/v2/reservation', [IndexController::class, 'reservation']); //预约

            Route::post('/chack/unusual/reservation', [IndexController::class, 'chackUnusualReservation']); //查询预约
            Route::post('/chack/start/driving', [IndexController::class, 'chackStartDriving']); //查询状态

            Route::post('/cancel/reservation', [IndexController::class, 'cancelReservation']); //预约

            Route::post('/login/logout', [LoginController::class, 'logout']);//前台退出
            Route::post('/index', [IndexController::class, 'index']);//首页
            Route::post('/get/title', [IndexController::class, 'getTitle']);//首页
//            Route::post('/start/driving',[UserController::class,'startDriving']); //开始驾驶/**/
            Route::post('/venue/detail', [IndexController::class, 'venueDetail']); //场地详情
            Route::post('/driving/protocol',[IndexController::class, 'drivingProtocol']);
//            Route::post('/driving/protocol',[IndexController::class, 'drivingProtocol']);
            Route::post('/mine', [IndexController::class, 'mine']); //我的
            Route::post('/change/special', [IndexController::class, 'changeSpecial']);//变更专区
            Route::post('/special/list', [IndexController::class, 'specialList']);
            Route::post('/reservation/list', [IndexController::class, 'reservationList']); //预约记录
            Route::post('/complain/list', [IndexController::class, 'complainList']); //申诉记录
            Route::post('/driving/record', [IndexController::class, 'drivingRecord']); //驾驶记录
            Route::post('/wallet/list', [IndexController::class, 'walletList']);
            Route::post('/change/name', [IndexController::class, 'changeName']);//用户前台-设置-修改手机号
            Route::post('/account/cancel', [IndexController::class, 'accountCancel']);//用户前台-设置-注销
            Route::post('/vehicle/detail', [VehicleController::class, 'vehicleDetail']); //车辆详情

            Route::post('/wechat/deposit', [IndexController::class, 'wechatDeposit']); //微信支付
            Route::post('/alipay/deposit', [IndexController::class, 'alipayDeposit']); //阿里
            Route::post('/change/password', [LoginController::class, 'changePassword']);//用户前台-设置-修改密码
            Route::post('/change/phone', [LoginController::class, 'changePhone']);//用户前台-设置-修改手机号
            Route::post('/change/head/shot', [LoginController::class, 'changeHeadShot']);//用户前台-设置-修改头像

            Route::post('/feedback', [IndexController::class, 'feedBack']);//用户前台-设置-意见
            Route::post('/deactivate', [IndexController::class, 'deactivate']);//用户前台-设置-注销
            Route::post('/complain', [IndexController::class, 'complain']);//用户前台-设置-申诉
            Route::post('/deposit/list', [IndexController::class, 'depositList']);
            Route::post('/deposit/activity/list', [IndexController::class, 'depositActivityList']);
            Route::post('/banner/', [IndexController::class, 'Banner']);

            //报修
            Route::post('/processing/alarm/create', [VehicleController::class, 'processingAlarmCreate']); //上报告警

            Route::post('/notice', [NoticeController::class, 'notice']);


        });
    });
    Route::post('/user/one/key/login', [LoginController::class, 'oneKeyLogin']);

    Route::post('/user/login/loginIn', [LoginController::class, 'login']);//前台登陆
    Route::post('/user/login/save', [LoginController::class, 'register']);//前台注册
    Route::post('/user/get/login/code', [LoginController::class, 'getLoginCode']);//获取验证码
    Route::post('/user/startup/page', [IndexController::class, 'startupPage']); //用户端启动页

    Route::post('/test/udp', [LoginController::class, 'udp']);
});
//微信支付回调
Route::post('/wechat/notify', [IndexController::class, 'wechatNotify']);
Route::post('/alipay/notify', [IndexController::class, 'alipayNotify']);
Route::post('/upload/picture', [LoginController::class, 'uploadPicture']);//上传图片
Route::post('/user/upload/picture', [LoginController::class, 'uploadPicture']);//上传图片

