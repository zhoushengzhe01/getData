<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use App\Http\Requests;

use App\Model\TenxunQq;
use App\Model\TenxunQqPosition;


class TenxunController extends Controller
{

    //获取真实邮箱数据
    public static function getQQData()
    {

        $url = "http://cgi.find.qq.com/qqfind/buddy/search_v3";

        $params = "num=20&page=0&sessionid=0&keyword=100046&agerg=0&sex=0&firston=0&video=0&country=0&province=0&city=0&district=0&hcountry=0&hprovince=0&hcity=0&hdistrict=0&online=0&ldw=392047444";

        $res = self::postCurlQQ($url, $params);


        print_r(json_decode($res, true));
        //echo $res;
        die;
        //开始
        start:

        $id = 1;

        //查找区域
        $emailPosition = TenxunEmailPosition::find($id);

        if( $emailPosition->currently <= $emailPosition->stop )
        {

            $url = 'https://user.qzone.qq.com/793616951';

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
