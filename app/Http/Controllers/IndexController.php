<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Symfony\Component\DomCrawler\Crawler;

class IndexController extends Controller
{
    public function index()
    {
        $standard_time = date("D M d Y H:i:s ").'GMT'.date("O").' (中国标准时间)';
        
        $url = "http://www.manben.com/mh-updated/pagerdata.ashx?t=8&pageindex=83&sc=1&d=".$standard_time;

        $result = $this->getCurl($url);

        //print_r($result);

        // //$user = User::findOrFail(1);
        
        // Log::error('send email to start: '.date('H:i:s') );
        
        //     dispatch( (new SendReminderEmail())->delay(10) );

        // Log::error('send email to stop: '.date('H:i:s') );

        
        // echo date("D M d Y H:i:s ").'GMT'.date("O").' (中国标准时间)';
        

        // //http://www.manben.com/mh-updated/pagerdata.ashx?t=8&pageindex=83&sc=1&d=Tue Sep 12 2017 16:24:42 GMT+0800 (中国标准时间)


        // //队列
        //dispatch( (new GetManbenData($request))->delay(60) );


        // echo self::getCurl('http://www.baidu.com');
        
        // echo "我解析了";
        
        // echo "\n";

        new Crawler('<html></html>');
    }
}
