<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Symfony\Component\DomCrawler\Crawler;

use App\Model\UserManben;
use App\Model\UserManbenStep;

use App\Model\Cartoon;

class ManbenController extends Controller
{

    protected static $domain = 'http://www.manben.com';

    public static function getData()
    {
      
        start:
  
        $standard_time = date("D M d Y H:i:s ").'GMT'.date("O").' (中国标准时间)';

        $pageindex = 83;

        //echo md5(microtime());

        $url = self::$domain . "/mh-updated/pagerdata.ashx?t=8&pageindex=".$pageindex."&sc=1&d=".$standard_time;
 
        $result = json_decode(self::getCurl($url),true);
     
        //倒着过来
        for($i=count($result) ; $i>0 ; $i-- )
        {
          
            self::getDetailData($result[$i-1]);

        }

        //$pageindex--; 

        // goto start;


        // echo self::getCurl('http://www.baidu.com');
        
        // new Crawler('<html></html>');
    }
    
    //获得详细信息数据
    public static function getDetailData($data)
    {
 
        echo $url = self::$domain . $data['Url'];

        $crawler = new Crawler(self::getCurl($url));

        $cartoon_id = md5(microtime());

        $user_id = 1;

        $area_id = '';

        $title = $crawler->filter('.comicInfo .img img')->attr('title');
      
        $letter = implode('',pinyin($title));


        echo iconv("UTF-8", "GB2312//IGNORE", str_replace("world","Shanghai",trim($crawler->filter('.comicInfo .info .ib')->eq(0)->text())));
        echo "\n1";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(1)->text()));
        echo "\n2";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(2)->text()));
        echo "\n3";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(3)->text()));
        echo "\n4";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(4)->text()));
        echo "\n5";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(5)->text()));
        echo "\n6";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(6)->text()));
        echo "\n7";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(7)->text()));
        echo "\n8";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(8)->text()));
        echo "\n9";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(9)->text()));
        echo "\n10";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(10)->text()));
        echo "\n11";
        echo iconv("UTF-8", "GB2312//IGNORE", trim($crawler->filter('.comicInfo .info .ib')->eq(11)->text()));
        die;



        //print_r($letter);

        //$image = self::downloadImage($crawler->filter('.comicInfo .img img')->attr('src'));

        $status = trim($crawler->filter('.comicInfo .info .gray .ib')->eq(3)->text())=='状  态：连载中' ? 1 : 2;

        $energy = '';

        $author = '';

        $source = '';

        $view = '';

        $collect = '';

        $is_publish = 0;
        
        $intro = '';


        
        
        
        
        die;
        
        //$crawler->

    }









    //获取用户数据
    public static function getUser()
    {
        //开始
        start:

        $standard_time = date("D M d Y H:i:s ").'GMT'.date("O").' (中国标准时间)';

        $id = 1;

        //查找区域
        $manbenStep = UserManbenStep::find($id);

        if( $manbenStep->current <= $manbenStep->stop )
        {

            $url = 'http://www.manben.com/checkname.ashx?d='.$standard_time.'&txt_reg_email='.$manbenStep->current.'@qq.com';

            $result = json_decode(self::getCurl($url), true);

            if($result['result']=='error')
            {
                echo date('Y-m-d H:i:s').$manbenStep->current.'@qq.com';
                echo "\n";

                //邮箱存在存起来
                $userManben = new UserManben;
                $userManben->email = $manbenStep->current.'@qq.com';
                $userManben->save();
                
            }

            UserManbenStep::where('id', '=', $id)->increment('current');
            

            echo date('Y-m-d H:i:s').$result['result']."\n";

            usleep(100);

            goto start;

        }

        
    }
}
