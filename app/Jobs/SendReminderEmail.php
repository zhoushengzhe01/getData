<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Mail\Mailer;


class SendReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    
    /**
        * 创建一个新的任务实例
        *
        * @param  User  $user
        * @return void
        */
    public function __construct()
    {

    }

    /**
     * 执行任务
     *
     * @param  Mailer  $mailer
     * @return void
     */
     public function handle()
     {

        Log::error('send email to user: 1  '.date('H:i:s'));
        $this->getCurl('http://www.manben.com/mh-updated');

        Log::error('send email to user: 2  '.date('H:i:s'));
        $this->getCurl('http://www.manben.com/mh-yinmouguiji/');

        Log::error('send email to user: 3  '.date('H:i:s'));
        $this->getCurl('http://www.manben.com/mh-boluofannaodongxiaomanhua/');

        Log::error('send email to user: 4  '.date('H:i:s'));
        $this->getCurl('http://www.manben.com/mh-huoguojiazu');


     }

     public function getCurl($url, $source='')
     {
         //初始化URL
         $ch = curl_init();
         //请求地址
         curl_setopt ($ch, CURLOPT_URL, $url);
         curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt ($ch, CURLOPT_HEADER, 0);
         //来源地址设置
         curl_setopt ($ch,CURLOPT_REFERER, $source);
         //结果
         $output = curl_exec ($ch);
         //释放CURL
         curl_close ($ch);
 
         return $output;
     }

}
