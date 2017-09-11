<?php

namespace App\Jobs;

use App\Manben;
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
        file_get_contents('http://www.manben.com/');
        file_get_contents('https://www.baidu.com');
        file_get_contents('https://www.baidu.com');
        file_get_contents('https://www.baidu.com');
        file_get_contents('https://www.baidu.com');

        //Manben::where('id', '=' , 1)->delete();

        // 处理抓取数据
        //echo 'okok';
    }
}
