<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\AdminUser;
use App\Models\AgentWalletLog;
use App\Models\AgentWithdrawLog;
use App\Models\AlarmVehcle;
use App\Models\ComplainRecord;
use App\Models\CuserAgent;
use App\Models\CuserEnergyLog;
use App\Models\CuserWalletLog;
use App\Models\DataCollect;
use App\Models\DepositLog;
use App\Models\DrivingRecord;
use App\Models\FeedBack;
use App\Models\PlatformParameter;
use App\Models\ProtocolManage;
use App\Models\ReceiverTransmitterConfig;
use App\Models\Vehicle;
use App\Models\WarZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
//         \App\Models\User::factory(10)->create();
//
//         \App\Models\User::factory()->create([
//             'name' => 'Test User',
//             'email' => 'test@example.com',
//         ]);
//        $this->run(UserService::class);
        $data = [
            'username'=>'admin-zzsj-aaxijqe111',
            'password'=>Hash::make('AUNL9T0KhpdwSjIf*qwas'),
            'type'=>1,
        ];
        dd($data);

//        AdminUser::create($data);
          //代理
//            $data = [
//                'uid' => 1,
//                'agent_name'=>'笆斗先生',
//                'level'=>1,
//                'phone_number'=>'1238917281',
//                'venue_quantity'=>0,
//                'create_site_quantity'=>100,
//                'is_support'=>0,
//            ];
//

//             $data = [
//                 'vehicle_id' => 1,
//                 'receiver_id' => '42126194',
//                 'transmitter_id' => '42321112',
//             ];
//            ReceiverTransmitterConfig::create($data);
//            $data = [
//                'uid' => 1,
//                'agent_name'=>'笆斗先生',
//                'level'=>1,
//                'phone_number'=>'1238917281',
//                'venue_quantity'=>0,
//                'create_site_quantity'=>100,
//                'is_support'=>0,
//                'superior_agent_id'=>0,
//                'withdrawal_amount'=>100,
//                'first_handling_fee'=>'0',
//                'company_handling_fee'=>20,
//            ];
////
//	   CuserAgent::create($data);
//             $data = [
//                 'vehicle_id' => 1,
//                 'receiver_id' => '42126194',
//                 'transmitter_id' => '42321112',
//             ];
//            ReceiverTransmitterConfig::create($data);
//        $data = [
//            'agent_id'=>1,
//            'name'=>'飞天专区',
//        ];
//        WarZone::create($data);


//        $data =[
//            'user_name'=>'笆斗',
//            'order_no'=>'asdsad1213123',
//            'phone'=>'12311234441',
//            'image' => 'aaaa.png',
//            'venue_id'=>1,
//            'venue_name'=>'测试',
//            'vehicle_id'=>12,
//            'vehicle_name'=>'测试',
//            'reservation_status'=>'2',
//            'amount'=>10,
//            'time'=>1766747094,
//            'billing_method'=>0,
//            'appeal_status'=>1,
//            'refund_amount'=>2,
//            'refund_type'=>0,
//            'refund_cause'=>'画面黑屏',
//            'platform_reply'=>'画面黑屏'
//
//        ];
//        $data =
//            [
//            'type'=>1,
//            'name'=>'用户驾驶协议',
//            'content'=>'</p>aaaa</p>',
//            ];
//            [
//            'type'=>2,
//            'name'=>'隐私政策',
//            'content'=>'</p>aaaa</p>',
//            ];
//            [
//                'type'=>3,
//                'name'=>'商务合作',
//                'content'=>'</p>aaaa</p>',
//            ];
//            [
//                'type'=>4,
//                'name'=>'注册协议',
//                'content'=>'</p>aaaa</p>',
//            ];

//        ProtocolManage::create($data);//协议
//        $data = [
//            'uid'=>1,
//            'agents_id'=>1,
//            'user_name'=>'测试测试',
//            'phone'=>'13788849821',
//            'Content'=>'黑屏不能玩',
//            'image'=>'aaa.png',
//            'type'=>0,
//            'time'=>1766924591,
//            'remark'=>'',
//        ];
//        FeedBack::create($data);
//        $data = [
//            'vehicle_id'=>12,
//            'agent_id'=>1,
//            'war_zone_name'=>'测试222',
//            'war_id'=>5,
//            'text'=>'摄像头坏了',
//            'status'=>0,
//        ];
//        AlarmVehcle::create($data);

//        $data = [
//            'agent_id'=>1,
//            'type'=>1,
//            'type_name'=>'提现',
//            'amount'=>1000,
//            'balance'=>10,
//            'make_order_no'=>'aaaaacas',
//            'venue'=>'测试',
//            'time'=>1767246730,
//
//        ];
//        AgentWalletLog::create($data);
//        $data = [
//            'title'=>'微信',
//            'value'=>'123338211932'
//        ];
//        PlatformParameter::create($data);
//
//        $data = [
//            'order_no'=>'aaaaaaaa11111',
//            'third_order_no'=>'alipay111111',
//            'uid'=>1,
//            'user_name'=>'aaa',
//            'special_area'=>1,
//            'special_area_name'=>'aaa',
//            'phone_number'=>13728288162,
//            'activity_id'=>1,
//            'amount'=>10,
//            'time'=>1769067211,
//            'type'=>1,
//            'finish_time'=>1769067220,
//            'sendMoney'=>1,
//            'pay_id'=>'1332817',
//            'energy_id'=>1,
//            'pay_type'=>1,
//        ];
//        DepositLog::create($data);
//
//        $data  =[
//            'agent_id'=>1,
//            'agent_name'=>'测试',
//            'withdraw_type'=>1,
//            'withdraw_amount'=>100,
//            'balance'=>50,
//            'status'=>1,
//            'enrolment_time'=>1769067211,
//            'audit_time'=>1769067221,
//            'withdraw_name'=>'大笆斗',
//            'withdraw_account'=>'aa1123112@qq.com',
//            'bank'=>'天地银行',
//            'bank_number'=>'32344785738219111',
//        ];
//        AgentWithdrawLog::create($data);
        $data = [
            'total_sale' => 0,
            'total_make' => 0,
            'total_payment' => 0,
            'total_refund' => 0,
        ];
        DataCollect::create($data);
    }

}
