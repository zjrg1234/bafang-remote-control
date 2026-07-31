<?php

use App\Http\Controllers\Home\NoticeController;
use App\Http\Controllers\Home\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\WarZoneController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\AdminUserController;
use App\Http\Controllers\Backend\AdminAgentController;
use App\Http\Controllers\Backend\ReservationController;
use App\Http\Controllers\Home\LoginController;
use App\Http\Controllers\Backend\PlatformConfigController;
use App\Http\Controllers\Backend\ActivityController;
use App\Http\Controllers\Backend\PaymentController;
use App\Http\Controllers\Backend\DepositActivityController;




Route::post('/login', [AdminController::class,'login']); //后台登陆
//    Route::post('/register', [AdminController::class,'register']);
Route::post('/logout', [AdminController::class,'logout']); //退出
//    Route::get('/user', [AdminController::class,'getAuthUser']);
//Route::post('/reset/google2fa',[AdminController::class,'resetGoogle2fa']); //重制谷歌 暂时先不要


Route::group(['middleware' => 'AuthToken'], function () {
    Route::group(['prefix' => 'special'], function () { //专区
//        Route::post('/create',[WarZoneController::class,'create']);
        Route::post('/list',[WarZoneController::class,'list']);
    });
    Route::group(['prefix' => 'user'], function () { //用户管理
        Route::post('/details',[AdminUserController::class,'details']);
        Route::post('/list',[AdminUserController::class,'list']);//列表
        Route::post('/update/screenshot',[AdminUserController::class,'updateScreenshot']);
        Route::post('/balance/list',[AdminUserController::class,'modifyBalance']);//列表
        Route::post('/energy/list',[AdminUserController::class,'modifyEnergy']);//列表
        Route::post('/change/password', [AdminUserController::class, 'changePassword']);//修改密码
        Route::post('/change/balance', [AdminUserController::class, 'changeBalance']);//修改余额
        Route::post('/change/energy', [AdminUserController::class, 'changeEnergy']);//修改能量
        Route::post('/frozen',[AdminUserController::class,'frozen']);//冻结
        Route::post('/delete',[AdminUserController::class,'delete']);//删除
        Route::post('/change/balance/log',[AdminUserController::class,'changeBalanceLog']);//余额修改记录
        Route::post('/change/energy/log',[AdminUserController::class,'changeEnergyLog']);//能量修改记录
        Route::post('/wallet/list',[AdminUserController::class,'walletList']);//钱包列表
        Route::post('/change/wallet/balance',[AdminUserController::class,'changeWalletBalance']);//钱包余额修改
        Route::post('/wallet/detail',[AdminUserController::class,'walletDetail']);//钱包列表


    });

    Route::group(['prefix' => 'agent'], function () { //代理商管理
        Route::post('/detail',[AdminAgentController::class,'detail']);//详情
        Route::post('/list',[AdminAgentController::class,'list']);//列表
        Route::post('/create',[AdminAgentController::class,'create']);//新增
        Route::post('/update',[AdminAgentController::class,'update']);//更新
        Route::post('/vehicle/delete',[AdminAgentController::class,'delete']);//删除
        Route::post('/vehicle/list',[AdminAgentController::class,'vehicleList']);//车辆列表
        Route::post('/vehicle/detail',[AdminAgentController::class,'vehicleDetail']);//车辆详情
        Route::post('/wallet/log',[AdminAgentController::class,'walletLog']);//余额记录
        Route::post('/change/password',[AdminAgentController::class,'changePassword']);//更改密码
        Route::post('/change/balance', [AdminAgentController::class, 'changeBalance']);//修改余额
        Route::post('/frozen',[AdminAgentController::class,'Frozen']);//冻结
        Route::post('/take/down',[AdminAgentController::class,'takeDown']);//下架
        Route::post('/delete',[AdminAgentController::class,'agentDelete']);//删除
        Route::post('/update/yesterday/turnover',[AdminAgentController::class,'updateYesterdayTurnover']);//更新昨日营业额
        Route::post('/venue/list',[AdminAgentController::class,'venueList']);//场地列表
        Route::post('/venue/take/down',[AdminAgentController::class,'venueTakeDown']);//下架
        Route::post('/venue/delete',[AdminAgentController::class,'venueDelete']);//删除
        Route::post('/venue/change/sort',[AdminAgentController::class,'venueChangeSort']);//修改排序
        Route::post('/venue/vehicle/list',[AdminAgentController::class,'venueVehicleList']);



    });
    //专区
    Route::post('/special/list',[AdminUserController::class,'specialList']);
    Route::post('/type/list',[WarZoneController::class,'typeList']);
    Route::post('/agent/type/list',[WarZoneController::class,'agentTypeList']);


    //预约记录
    Route::post('/reservation/record',[ReservationController::class,'reservationRecord']);
    Route::post('/complaint/record',[ReservationController::class,'complaintRecord']);
    Route::post('/complaint/update',[ReservationController::class,'complaintUpdate']);
    Route::post('/refund/record',[ReservationController::class,'refundRecord']);

    //平台配置
    //常见问题列表
    Route::post('/common/problem/list',[PlatformConfigController::class,'commonProblemList']);
    Route::post('/common/problem/create',[PlatformConfigController::class,'commonProblemCreate']);
    Route::post('/common/problem/update',[PlatformConfigController::class,'commonProblemUpdate']);
    Route::post('/common/problem/delete',[PlatformConfigController::class,'commonProblemDelete']);
    //协议管理
    Route::post('/protocol/manage/list',[PlatformConfigController::class,'protocolManageList']);
//    Route::post('/protocol/manage/create',[PlatformConfigController::class,'protocolManageCreate']);
    Route::post('/protocol/manage/update',[PlatformConfigController::class,'protocolManageUpdate']);
    //意见反馈
    Route::post('/feed/back/list',[PlatformConfigController::class,'feedBackList']);
    Route::post('/feed/back/update',[PlatformConfigController::class,'feedBackUpdate']);

    //广告页图片
    Route::post('/advertising/list',[PlatformConfigController::class,'advertisingList']);
    Route::post('/advertising/create',[PlatformConfigController::class,'advertisingCreate']);
    Route::post('/advertising/update',[PlatformConfigController::class,'advertisingUpdate']);
    Route::post('/advertising/delete',[PlatformConfigController::class,'advertisingDelete']);

    //app管理
    Route::post('/version/list',[PlatformConfigController::class,'versionList']);
    Route::post('/version/create',[PlatformConfigController::class,'versionCreate']);
    Route::post('/version/update',[PlatformConfigController::class,'versionUpdate']);
    Route::post('/version/delete',[PlatformConfigController::class,'versionDelete']);


    //登陆弹窗
    Route::post('/popup/list',[PlatformConfigController::class,'popupList']);
    Route::post('/popup/delete',[PlatformConfigController::class,'popupDelete']);
    Route::post('/popup/create',[PlatformConfigController::class,'popupCreate']);
    Route::post('/popup/update',[PlatformConfigController::class,'popupUpdate']);

    //平台参数
    Route::post('/parameter/list',[PlatformConfigController::class,'parameterList']);
    Route::post('/parameter/update',[PlatformConfigController::class,'parameterUpdate']);

    //车辆默认图片
    Route::post('/vehicle/image/list',[PlatformConfigController::class,'vehicleImageList']);
    Route::post('/vehicle/image/delete',[PlatformConfigController::class,'vehicleImageDelete']);
    Route::post('/vehicle/image/create',[PlatformConfigController::class,'vehicleImageCreate']);
    Route::post('/vehicle/image/update',[PlatformConfigController::class,'vehicleImageUpdate']);
    Route::post('/vehicle/image/change/status',[PlatformConfigController::class,'vehicleImageChangeStatus']);
    Route::post('/vehicle/image/type/list',[PlatformConfigController::class,'vehicleImageTypeList']);

    Route::post('/upload/picture', [LoginController::class, 'uploadPicture']);//上传图片
    Route::post('/upload/file', [LoginController::class, 'uploadFile']);//上传图片


    Route::post('/vehicle/list',[WarZoneController::class,'vehicleList']);//车辆列表
    Route::post('/vehicle/detail',[AdminAgentController::class,'vehicleDetail']);//车辆详情
    Route::post('/driving/record',[ReservationController::class,'drivingRecord']);



    //活动页公告
    Route::post('/activity/list',[ActivityController::class,'List']);
    Route::post('/activity/delete',[ActivityController::class,'Delete']);
    Route::post('/activity/create',[ActivityController::class,'Create']);
    Route::post('/activity/update',[ActivityController::class,'Update']);
    Route::post('/activity/change/status',[ActivityController::class,'ChangeStatus']);

    //充值活动
    Route::post('/deposit/activity/list',[DepositActivityController::class,'List']);
    Route::post('/deposit/activity/create',[DepositActivityController::class,'Create']);
    Route::post('/deposit/activity/update',[DepositActivityController::class,'Update']);
    Route::post('/deposit/activity/delete',[DepositActivityController::class,'Delete']);
    Route::post('/deposit/activity/change/type',[DepositActivityController::class,'ChangeStatus']);

    //充值活动记录
    Route::post('/deposit/activity/record',[DepositActivityController::class,'Record']);


    //支付记录
    Route::post('/payment/list',[PaymentController::class,'paymentList']);
    Route::post('/withdraw/list',[PaymentController::class,'withdrawList']); //提现记录
    Route::post('/withdraw/audit',[PaymentController::class,'withdrawAudit']); //提现记录

    Route::post('/refund/list',[PaymentController::class,'refundList']); //退款记录

    //专区用户数量统计
    Route::post('/special/account/list',[PaymentController::class,'specialAccountList']); //专区用户注册记录
    Route::post('/special/deposit/list',[PaymentController::class,'specialDepositList']); //专区用户余额记录
    //首页
    Route::post('/index',[PaymentController::class,'Index']); //专区用户余额记录

    //banner
    Route::post('/banner/list', [LoginController::class, 'BannerList']);
    Route::post('/banner/create', [LoginController::class, 'BannerCreate']);
    Route::post('/banner/update', [LoginController::class, 'BannerUpdate']);
    Route::post('/banner/delete', [LoginController::class, 'BannerDelete']);

    Route::post('/notice/list',[NoticeController::class,'list']);
    Route::post('/notice/create',[NoticeController::class,'create']);
    Route::post('/notice/update',[NoticeController::class,'update']);
    Route::post('/notice/delete',[NoticeController::class,'delete']);


});
