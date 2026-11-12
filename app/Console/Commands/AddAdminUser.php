<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use App\Models\CuserAgent;
use Illuminate\Console\Command;

class AddAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $agent = CuserAgent::where('level',1)->where('superior_agent_id',0)->get();
        if($agent){
            foreach ($agent as $v){
                $admin = AdminUser::where('agent_id',$v['id'])->first();
                if($admin){
                    $this->info('用户已存在');
                    continue;
                }
                $addAdmin = [
                    'agent_id'=>$v['id'],
                    'username' => $v['phone_number'],
                    'password' => $v['password'],
                    'type' => 1,
                ];
                AdminUser::create($addAdmin);
                $this->info($addAdmin['username'].'用户增加成功');
            }
        }
    }
}
