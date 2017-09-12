<?php

namespace App\Jobs;

use App\Manben;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetManbenData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $manbe;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($manben)
    {



        
        //$this->manbe = $Manben;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
     
        Log::error('send email to user: zhoushengzhe');
        
        // file_get_contents('https://www.baidu.com');

        //Manben::where('id', '=' , 1)->delete();

        // 处理抓取数据
        //echo 'okok';
    }
}
