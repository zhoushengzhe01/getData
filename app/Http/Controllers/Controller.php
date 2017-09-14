<?php

namespace App\Http\Controllers;


use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    
    //CURL获取
    public static function getCurl($url, $source='')
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
    public static function postCurl($url, $date,  $source='')
    {
        die;
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
    public static function downloadImage($url, $source)
    {
        $path = 'download/'.date('Y').'/'.date('m').'/'.date('d').'/';

        if(!is_dir('public/'.$path))
        {
            mkdir(iconv("UTF-8", "GBK", 'public/'.$path),0777,true);
        }

        $Con = self::getCurl($url, $source);

        preg_match("#.[a-zA-Z]+$#",$url, $matches);

        $image_name = md5(microtime()).$matches[0];

        file_put_contents('public/'.$path.$image_name , $Con);

        return $path.$image_name;

    }

}
