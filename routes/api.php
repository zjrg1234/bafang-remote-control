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
use \App\Http\Controllers\Home\PersonVehicleController;


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

Route::group(['middleware' => 'checkAesEntry'], function () {
    Route::post('/login/loginIn', [LoginController::class, 'login']);
    Route::post('/login/save', [LoginController::class, 'register']);
    Route::post('/get/login/code', [LoginController::class, 'getLoginCode']);
    Route::post('/one/key/login', [LoginController::class, 'oneKeyLogin']);
    Route::post('/app/version', [IndexController::class, 'appVersion']);
    Route::post('/user/app/version', [IndexController::class, 'appVersion']);
    Route::post('/wechat/applet/login', [LoginController::class, 'wechatAppletLogin']);
    Route::post('/ks/applet/login', [LoginController::class, 'ksLogin']);
    // APP抖音一键登录
    Route::post('/app/dy/login', [LoginController::class, 'appDyLogin']);
    // APP快手一键登录
    Route::post('/app/wechat/login', [LoginController::class, 'appWechatLogin']);
    Route::post('/bind/app/phone',[PersonVehicleController::class,'bindAppLoginPhone']);


    Route::group(['middleware'=>'CheckToken'], function () {
        //代理商端
        Route::post('/login/logout', [LoginController::class, 'logout']);
        Route::post('/startup/page', [IndexController::class, 'startupPage']);
        Route::post('/notice', [NoticeController::class, 'notice']);


        //代理->前台->车辆管理页面
        Route::post('/vehicle/list', [VehicleController::class, 'vehicleList']);
        Route::post('/delete/vehicle', [VehicleController::class, 'deleteVehicle']);
        Route::post('/binding/venue', [VehicleController::class, 'bindingVenue']);
        Route::post('/down/vehicle', [VehicleController::class, 'downVehicle']);
//        Route::post('/unbinding/venue', [VehicleController::class, 'unBindingVenue']);
        Route::post('/add/vehicle', [VehicleController::class, 'addVehicle']);
        Route::post('/vehicle/detail', [VehicleController::class, 'vehicleDetail']);
        Route::post('/vehicle/detail/save', [VehicleController::class, 'vehicleDetailSave']);
        Route::post('/update/vehicle', [VehicleController::class, 'updateVehicle']);
        Route::post('/reset/default/channel', [VehicleController::class, 'vehicleDetailReset']);

        //代理->前台->车辆告警页面
        Route::post('/processing/alarm', [VehicleController::class, 'processingAlarm']);
        Route::post('/processing/alarm/list', [VehicleController::class, 'processingAlarmList']);
        Route::post('/processing/alarm/delete', [VehicleController::class, 'processingAlarmDelete']);

        //代理->前台->我的
        Route::post('/agent/mine', [AgentController::class, 'agentMine']);
        Route::post('/set/agent/wallet/password', [AgentController::class, 'setAgentWalletPassword']);
        Route::post('/check/agent/wallet/password', [AgentController::class, 'checkAgentWalletPassword']);

        Route::post('/agent/driving/record', [AgentController::class, 'agentDrivingRecord']);
        Route::post('/agent/driving', [AgentController::class, 'agentDriving']);
        Route::post('/agent/change/password', [LoginController::class, 'agentChangePassword']);
        Route::post('/agent/wallet/log', [AgentController::class, 'agentWalletLog']);

        //提现晚点做
        Route::post('/agent/withdraw', [AgentController::class, 'agentWithdraw']);
        Route::post('/agent/withdraw/record', [AgentController::class, 'agentWithdrawLog']);
        Route::post('/agent/change/name', [IndexController::class, 'agentChangeName']);


        //代理->前台->我的场地
        Route::post('/venue/list', [VenueController::class, 'venueList']);
        Route::post('/create/venue', [VenueController::class, 'createVenue']);
        Route::post('/venue/detail', [VenueController::class, 'venueDetail']);
        Route::post('/update/venue', [VenueController::class, 'updateVenue']);
        Route::post('/venue/business',[VenueController::class, 'venueBusiness']);
        Route::post('/venue/count', [VenueController::class, 'venueCount']);
        Route::post('/delete/venue', [VenueController::class, 'venueDelete']);
        Route::post('/chack/start/driving', [IndexController::class, 'chackStartDriving']);


        Route::post('/start/driving',[IndexController::class,'startDriving']);
        Route::post('/v2/start/driving',[IndexController::class,'startDriving']);

        Route::post('/update/vehicle/battery',[VehicleController::class,'updateVehicleBattery']);

        Route::post('/agent/change/head/shot', [LoginController::class, 'changeHeadShot']);

        //用户R
        Route::prefix('user')->group(function () {
            Route::post('/start/driving',[IndexController::class,'startDriving']);
            Route::post('/lock/driving',[IndexController::class,'lockDriving']);
            Route::post('/v2/start/driving',[IndexController::class,'startDriving']);

            Route::post('/reservation', [IndexController::class, 'reservation']);
            Route::post('/v2/reservation', [IndexController::class, 'reservation']);

            Route::post('/chack/unusual/reservation', [IndexController::class, 'chackUnusualReservation']);
            Route::post('/chack/start/driving', [IndexController::class, 'chackStartDriving']);

            Route::post('/cancel/reservation', [IndexController::class, 'cancelReservation']);

            Route::post('/login/logout', [LoginController::class, 'logout']);
            Route::post('/index', [IndexController::class, 'index']);
            Route::post('/get/title', [IndexController::class, 'getTitle']);
//            Route::post('/start/driving',[UserController::class,'startDriving']);
            Route::post('/venue/detail', [IndexController::class, 'venueDetail']);
            Route::post('/driving/protocol',[IndexController::class, 'drivingProtocol']);
            Route::post('/check/vehicle/status', [IndexController::class, 'checkVehicleStatus']);

//            Route::post('/driving/protocol',[IndexController::class, 'drivingProtocol']);
            Route::post('/mine', [IndexController::class, 'mine']);
            Route::post('/change/special', [IndexController::class, 'changeSpecial']);
            Route::post('/special/list', [IndexController::class, 'specialList']);
            Route::post('/reservation/list', [IndexController::class, 'reservationList']);
            Route::post('/complain/list', [IndexController::class, 'complainList']);
            Route::post('/driving/record', [IndexController::class, 'drivingRecord']);
            Route::post('/wallet/list', [IndexController::class, 'walletList']);
            Route::post('/change/name', [IndexController::class, 'changeName']);
            Route::post('/account/cancel', [IndexController::class, 'accountCancel']);
            Route::post('/vehicle/detail', [VehicleController::class, 'vehicleDetail']);

            Route::post('/wechat/deposit', [IndexController::class, 'wechatDeposit']);
            Route::post('/alipay/deposit', [IndexController::class, 'alipayDeposit']);
            Route::post('/wechat/jsapi/deposit', [IndexController::class, 'wechatJsapiDeposit']);
            Route::post('/ks/deposit', [IndexController::class, 'ksDeposit']);
            Route::post('/change/password', [LoginController::class, 'changePassword']);
            Route::post('/change/phone', [LoginController::class, 'changePhone']);
            Route::post('/change/head/shot', [LoginController::class, 'changeHeadShot']);

            Route::post('/feedback', [IndexController::class, 'feedBack']);
            Route::post('/deactivate', [IndexController::class, 'deactivate']);
            Route::post('/complain', [IndexController::class, 'complain']);
            Route::post('/deposit/list', [IndexController::class, 'depositList']);
            Route::post('/deposit/activity/list', [IndexController::class, 'depositActivityList']);
            Route::post('/banner/', [IndexController::class, 'Banner']);

            //报修
            Route::post('/processing/alarm/create', [VehicleController::class, 'processingAlarmCreate']);

            Route::post('/notice', [NoticeController::class, 'notice']);
            Route::post('/common/problem', [NoticeController::class, 'problemList']);
            Route::post('/activity/notice', [NoticeController::class, 'activityNotice']);

            //个人
            Route::post('/person/vehicle/list', [PersonVehicleController::class, 'vehicleList']);
            Route::post('/person/delete/vehicle', [PersonVehicleController::class, 'deleteVehicle']);
            Route::post('/person/add/vehicle', [PersonVehicleController::class, 'addVehicle']);
            Route::post('/person/vehicle/detail', [PersonVehicleController::class, 'vehicleDetail']);
            Route::post('/person/vehicle/detail/save', [PersonVehicleController::class, 'vehicleDetailSave']);
            Route::post('/person/update/vehicle', [PersonVehicleController::class, 'updateVehicle']);
            Route::post('/person/reset/default/channel', [PersonVehicleController::class, 'vehicleDetailReset']);
            Route::post('/person/start/driving',[PersonVehicleController::class,'startDriving']);
            Route::post('/person/refresh/close',[PersonVehicleController::class,'refreshClose']);
            Route::post('/person/vehicle/battery',[PersonVehicleController::class,'getVehicleBattery']);
            Route::post('/person/switch/remote/control',[PersonVehicleController::class,'switchRemoteControl']);


        });
    });
    Route::post('/user/one/key/login', [LoginController::class, 'oneKeyLogin']);

    Route::post('/user/login/loginIn', [LoginController::class, 'login']);
    Route::post('/user/login/save', [LoginController::class, 'register']);
    Route::post('/user/get/login/code', [LoginController::class, 'getLoginCode']);
    Route::post('/user/startup/page', [IndexController::class, 'startupPage']);

    Route::post('/test/udp', [LoginController::class, 'udp']);
});
Route::post('/wechat/notify', [IndexController::class, 'wechatNotify']);
Route::post('/alipay/notify', [IndexController::class, 'alipayNotify']);
Route::post('/ks/pay/notify', [IndexController::class, 'ksNotify']);

Route::post('/upload/picture', [LoginController::class, 'uploadPicture']);
Route::post('/user/upload/picture', [LoginController::class, 'uploadPicture']);


Route::post('/set/key',[VehicleController::class, 'setKey']);
Route::post('/del/key',[VehicleController::class, 'delKey']);
Route::post('/query/key',[VehicleController::class, 'queryKey']);

Route::post('/transmitter/bind',[IndexController::class,'transmitterBind']);
Route::post('/transmitter/un/bind',[IndexController::class,'transmitterUnBind']);
Route::post('/transmitter/query/bind',[IndexController::class,'transmitterQueryBind']);

