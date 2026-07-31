<?php

namespace App\Console\Commands;

use App\Models\DrivingRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateAgentWeeklyAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate-agent-weekly-amount';

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
        $this->info('开始计算代理商周业绩...');
        $sevenDaysAgo = Carbon::now()->subDays(7)->timestamp;

        // 【架构师写法】：不要用 ORM 循环去查，直接用 DB 门面写底层的聚合 SQL。
        // 这条 SQL 会把所有代理商的近7天金额一次性算出来，极速！
        $amounts = DrivingRecord::
            // 假设订单表 driving_record 通过 venue_id 关联 agent_venues 表，agent_venues 表里有 agent_id
            join('agent_venues', 'driving_record.venue_id', '=', 'agent_venues.id')
            ->where('driving_record.reservation_status', 4)
            ->where('driving_record.order_time', '>=', $sevenDaysAgo)
            ->groupBy('agent_venues.agent_id')
            ->selectRaw('agent_venues.agent_id, SUM(driving_record.payment_amount) as total_amount')
            ->pluck('total_amount', 'agent_id');

        // 第一步：把所有代理商的金额先全部归零（防止上周有业绩，这周没业绩的人数据卡住）
        DrivingRecord::update(['weekly_amount' => 0]);

        // 第二步：批量更新有业绩的代理商
        $count = 0;
        foreach ($amounts as $agentId => $totalAmount) {
            DrivingRecord::
                where('id', $agentId)
                ->update(['weekly_amount' => $totalAmount]);
            $count++;
        }

        $this->info("计算完成！共更新了 {$count} 个代理商的业绩。");
        return 0;
    }
}
