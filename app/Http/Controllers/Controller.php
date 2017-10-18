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
    public static function postCurl($url, $data,  $source='')
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $date);
        //结果
        $output = curl_exec($ch);
        //释放CURL
        curl_close($ch);
        return $output;
    }

    //curl qq号验证
    public static function postCurlQQ($url, $date,  $source='')
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

        curl_setopt($ch, CURLOPT_COOKIE, 'pt2gguin=o2712143540; RK=LDH6rGO6ss; ptcz=06093d145cde79ea99f23fd5b8f294d4d30918d02c5476ca64641789eea15587; _qpsvr_localtk=tk553; pgv_pvid=280793616; uin=o2712143540; skey=Zplfkj8Q3K; itkn=2062170726');
        //结果
        $output = curl_exec($ch);
        //释放CURL
        curl_close($ch);
        return $output;
    }


    //图片下载
    public static function downloadImage($url, $source, $path1='', $path2='')
    {
        $path = 'download/2016/';

        //默认图片
        $default = 'download/header/header.jpg';

        if($path1)
            $path .= $path1.'/';

        if($path2)
            $path .= $path2.'/';

        //没有目录创建
        if(!is_dir('public/'.$path))
        {
            mkdir(iconv("UTF-8", "GBK", 'public/'.$path),0777,true);
        }
        //匹配文件名
        preg_match("#[0-9a-zA-Z]+.[a-zA-Z]+$#",$url, $file_name);
        if(empty($file_name[0]) || $file_name[0]=='toux3.jpg')
        {
            return $default;
        }

        //匹配后缀
        preg_match("#.[a-zA-Z]+$#",$file_name[0], $matches);
        //图片名称
        $image_name = md5(microtime()).$matches[0];

        //读取 保存
        $Con = self::getCurl($url, $source);
        file_put_contents('public/'.$path.$image_name , $Con);

        return $path.$image_name;

    }

    //删除图片
    public static function deleteImage($path)
    {
        $path = "public/".$path;
 
        if(is_file($path))
        {
            //存在删除
            unlink($path);
        }
        
        return true;

    }

}
