<?php

namespace App\Console\Commands;

use App\Models\DataCollect;
use App\Models\DrivingRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SumDeveloperAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sum-developer-amount';

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

        $data = DataCollect::select('total_sale', 'total_make', 'total_payment', 'total_refund')->where('id',2)->first();
        $end_time = strtotime(Carbon::yesterday()->endOfDay());

        $total_sale = DrivingRecord::where('reservation_status',4)->where('payment_type',1)->where('order_time','<=',$end_time)->sum('payment_amount');

    }
}
