<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;


class Commmont extends Controller
{
    public function __construct()
    {
        //parent::__construct();

    }

    //CURL获取
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

    //CURL提交
    public function postCurl($url, $date,  $source='')
    {
        //初始化CURL
        $ch = curl_init();
        //请求地址
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //POST请求
        curl_setopt($ch, CURLOPT_POST, 1);
        //来源地址设置
        curl_setopt ($ch,CURLOPT_REFERER, $source);
        //提交参数
        curl_setopt($ch, CURLOPT_POSTFIELDS, $date);
        //结果
        $output = curl_exec($ch);
        //释放CURL
        curl_close($ch);

        return $output;
    }

    //图片下载
    public function downloadImage($url, $path)
    {
        
        // $downloaded_file = fopen($save_to, 'w');
        // fwrite($downloaded_file, $file_content);
        // fclose($downloaded_file);

    }
}
