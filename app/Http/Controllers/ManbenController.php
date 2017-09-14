<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Symfony\Component\DomCrawler\Crawler;

use App\Model\UserManben;
use App\Model\UserManbenStep;

use App\Model\Cartoon;
use App\Model\CartoonsCatalog;
use App\Model\CartoonsImage;


class ManbenController extends Controller
{

    protected static $domain = 'http://www.manben.com';

    protected static $cartoon_id;
    
    protected static $catalog_id;

    public static function getBookData()
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

        // goto start;
    }
    
    //获得详细信息数据
    public static function getDetailData($data)
    {
 
        $url = self::$domain . $data['Url'];

        $crawler = new Crawler(self::getCurl($url));
      
        self::$cartoon_id = md5(microtime());

        $user_id = 1;

        $area_id = '';

        $title = $crawler->filter('.comicInfo .img img')->attr('title');
      
        $letter = implode('',pinyin($title));

        $image = self::downloadImage($crawler->filter('.comicInfo .img img')->attr('src'), $url);
        
        $status = trim(str_replace("状  态：", "", $crawler->filter('.comicInfo .info .ib')->eq(9)->text()))=='连载中' ? 1 : 2;
        
        $energy = trim(str_replace("万","", str_replace("漫画战力：","", $crawler->filter('.comicInfo .info .ib')->eq(6)->text())));
        
        $author = trim(str_replace("作  者：","", $crawler->filter('.comicInfo .info .ib')->eq(3)->text()));

        $source = $url;

        $view = trim(str_replace("阅读人次：","", $crawler->filter('.comicInfo .info .ib')->eq(5)->text()));

        $collect = trim(str_replace("收藏数：","", $crawler->filter('.comicInfo .info .ib')->eq(4)->text()));

        $intro = trim($crawler->filter('.comicInfo .content')->text());

        $catalogCount = $crawler->filter('#chapterlistload .ib')->count();

        //插入数据
        $cartoon = new Cartoon;
            $cartoon->cartoon_id = self::$cartoon_id;
            $cartoon->user_id = $user_id;
            $cartoon->area_id = $area_id;
            $cartoon->title = $title;
            $cartoon->letter = $letter;
            $cartoon->image = $image;
            $cartoon->status = $status;
            $cartoon->energy = $energy;
            $cartoon->author = $author;
            $cartoon->source = $source;
            $cartoon->view = $view;
            $cartoon->collect = $collect;
            $cartoon->is_recommend = 1;
            $cartoon->is_publish = 0;
            $cartoon->intro = $intro;
            $cartoon->save();


        //章节获取
        for($i=0 ; $i<$catalogCount ; $i++)
        {
            $item = $crawler->filter('#chapterlistload .ib')->eq($i);

            $cid = str_replace("/","", str_replace("m","", $item->attr('href')));
           
            $title = $item->text();
           
            self::getCatalogImage($cid, $title);
            echo "一个章节完成\n";
            
        }

        echo "一片漫画完成\n";

    }

    //获取章节图片
    public static function getCatalogImage($cid, $title)
    {   
        $href = self::$domain . "/m".$cid."/";

        $titleArr = explode(" ",$title);

        $title = !empty($titleArr[0]) ? $titleArr[0] : '';

        $subtitle = !empty($titleArr[1]) ? $titleArr[1] : '';
        
        $standard_time = date("D M d Y H:i:s ").'GMT'.date("O").' (CST)';

        self::$catalog_id = md5(microtime());


        $cartoonsCatalog = new CartoonsCatalog;

            $cartoonsCatalog->cartoon_id = self::$cartoon_id;
            $cartoonsCatalog->catalog_id = self::$catalog_id;
            $cartoonsCatalog->title = $title;
            $cartoonsCatalog->subtitle = $subtitle;
            $cartoonsCatalog->sort = 50;
            $cartoonsCatalog->type = 1;
            $cartoonsCatalog->source = $href;
            $cartoonsCatalog->is_delete = 0;
            $cartoonsCatalog->save();

            


        //new CartoonsCatalog

        $catalog = new Crawler(self::getCurl($href));

        $imageCount = $catalog->filter('.pagelist a')->count();
        
        for($i=1 ; $i<=$imageCount ; $i++)
        {
            $url = "http://www.manben.com/imageshow.ashx?d=".$standard_time."&cid=".$cid."&page=".($i)."&showtype=1&ispre=1";

            $result = json_decode(str_replace(";","", str_replace("var chapterimage=","", self::getCurl($url))),true);
            
       
            $images = $result['Images'];

            $imagePix = $result['ImagePix'];
            echo $i."\t";
            echo count($images);
            foreach($images as $k=>$v)
            {
                $arr = parse_url($v);
            
                $imagePath = $imagePix.$arr["path"];
                
                $path = self::downloadImage($imagePath, $href);

                $cartoonsImage = new CartoonsImage;

                    $cartoonsImage->cartoon_id = self::$cartoon_id;
                    $cartoonsImage->catalog_id = self::$catalog_id;
                    $cartoonsImage->path = $path;
                    $cartoonsImage->size = '';
                    $cartoonsImage->height = '';
                    $cartoonsImage->width = '';
                    $cartoonsImage->sort = 50;
                    $cartoonsImage->is_delete = 0;
                    $cartoonsImage->source = $imagePath;
                    $cartoonsImage->save();
                
                echo "一张图片完成\n";
            }

        }
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
