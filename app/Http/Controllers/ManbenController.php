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

    protected static $_cartoon_id;
    
    protected static $catalog_id;

    protected static $_catalog_id;

    public static function getBookData()
    {
        
        start:
  
        $standard_time = date("D M d Y H:i:s ").'GMT'.date("O").' (中国标准时间)';

        $pageindex = 84;

        //echo md5(microtime());

        $url = self::$domain . "/mh-updated/pagerdata.ashx?t=8&pageindex=".$pageindex."&sc=1&d=".$standard_time;
        
        $result = json_decode(self::getCurl($url),true);
        
    
        //倒着过来
        for( $i=count($result) ; $i>0 ; $i-- )
        {
            $data = $result[$i-1];

            $cartoon_id = self::getCartoon($data);

            //设置同步完成
            Cartoon::where('cartoon_id', '=', $cartoon_id)->update(['state'=>2]);
          
            echo date("Y-m-d H:i:s").": Ready for the next one\n";

            sleep(2);
        }

        // goto start;
    }
    
    //获得详细信息数据
    public static function getCartoon($data)
    {
 
        $url = self::$domain . $data['Url'];

        $crawler = new Crawler(self::getCurl($url));

        //获得标题
        $title = $crawler->filter('.comicInfo .img img')->attr('title');

        //进行验证 是否同步完成
        $cartoon = Cartoon::where('title', '=', $title)->first();

        if(empty($cartoon))
        {
            self::$cartoon_id = md5(microtime());

            $user_id = 1;
            
            $area_id = '';
            
            $letter = implode('',pinyin($title));
    
            $image = self::downloadImage($crawler->filter('.comicInfo .img img')->attr('src'), $url, 'cover');
            
            $status = trim(str_replace("状  态：", "", $crawler->filter('.comicInfo .info .ib')->eq(9)->text()))=='连载中' ? 1 : 2;
            
            $energy = trim(str_replace("万","", str_replace("漫画战力：","", $crawler->filter('.comicInfo .info .ib')->eq(6)->text())));
            
            $author = trim(str_replace("作  者：","", $crawler->filter('.comicInfo .info .ib')->eq(3)->text()));
    
            $source = $url;
    
            $view = trim(str_replace("阅读人次：","", $crawler->filter('.comicInfo .info .ib')->eq(5)->text()));
    
            $collect = trim(str_replace("收藏数：","", $crawler->filter('.comicInfo .info .ib')->eq(4)->text()));
    
            $intro = trim($crawler->filter('.comicInfo .content')->text());
    
            //插入数据
            $cartoon = new Cartoon;
            $cartoon->cartoon_id = self::$cartoon_id;
            $cartoon->user_id = $user_id;
            $cartoon->area_id = $area_id;
            $cartoon->title = trim($title);
            $cartoon->letter = trim($letter);
            $cartoon->image = $image;
            $cartoon->status = trim($status);
            $cartoon->energy = trim($energy);
            $cartoon->author = trim($author);
            $cartoon->source = trim($source);
            $cartoon->view = trim($view);
            $cartoon->collect = trim($collect);
            $cartoon->is_recommend = 1;
            $cartoon->is_publish = 0;
            $cartoon->intro = $intro;
            $cartoon->save();

            $cartoon = Cartoon::where('cartoon_id', '=', self::$cartoon_id)->first();
            self::$_cartoon_id = $cartoon['id'];


        }
        else
        {
            self::$cartoon_id = $cartoon['cartoon_id'];
            self::$_cartoon_id = $cartoon['id'];

            //同步完成直接返回true 
            if($cartoon['state']==2)
            {
                return true;
            }

        }

        //获取多少章节
        $catalogCount = $crawler->filter('#chapterlistload .ib')->count();

        //章节获取
        for($i=$catalogCount ; $i>0 ; $i--)
        {
            $item = $crawler->filter('#chapterlistload .ib')->eq($i-1);

            $cid = str_replace("/","", str_replace("m","", $item->attr('href')));
           
            $title = $item->text();

            //查找数据库是否存在
            
            $catalog_id = self::getCartoonsCatalog($cid, $title);
            
            //处理状态完成
            CartoonsCatalog::where('catalog_id', '=', $catalog_id)->update(['state'=>2]);

            echo date("Y-m-d H:i:s").": \tOne chapter is complete\n";
        }

        echo date("Y-m-d H:i:s").": \tA cartoon finished\n";


        //返回标识ID
        return self::$cartoon_id;

    }

    //获取章节图片
    public static function getCartoonsCatalog($cid, $title)
    {   
        $href = self::$domain . "/m".$cid."/";

        $titleArr = explode(" ",$title);

        $title = !empty($titleArr[0]) ? $titleArr[0] : '';

        $subtitle = !empty($titleArr[1]) ? $titleArr[1] : '';
      

        //验证章节是否完成
        $cartoonsCatalog = CartoonsCatalog::where('cartoon_id', '=', self::$cartoon_id)
                ->where('title', '=', $title)
                ->where('subtitle', '=', $subtitle)
                ->first();

        if(empty($cartoonsCatalog))
        {
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

            $cartoonsCatalog = Cartoon::where('cartoon_id', '=', self::$cartoon_id)->where('catalog_id', '=', self::$catalog_id)->first();
            self::$_catalog_id = $cartoonsCatalog['id'];

        }
        else
        {
            self::$catalog_id = $cartoonsCatalog['catalog_id'];
            self::$_catalog_id = $cartoonsCatalog['id'];

            if($cartoonsCatalog['state']==2)
            {
                return self::$catalog_id;
            }
            else
            {
                //如果没有同步完成则删除图片
                $images = CartoonsImage::where('cartoon_id', '=', self::$cartoon_id)
                    ->where('catalog_id', '=', self::$catalog_id)
                    ->get();

                foreach($images as $key=>$val)
                {
                    //删除本地图片
                    self::deleteImage($val['path']);

                    CartoonsImage::where('id', '=', $val['id'])->delete();

                }
            }
        }


        $catalog = new Crawler(self::getCurl($href));

        $imageCount = $catalog->filter('.pagelist a')->count();

        //获取图片
        for($i=$imageCount ; $i>0 ; $i--)
        {
            $standard_time = date("D M d Y H:i:s ").'GMT'.date("O").' (CST)';

            $url = "http://www.manben.com/imageshow.ashx?d=".$standard_time."&cid=".$cid."&page=".($i)."&showtype=1&ispre=1";

            $result = json_decode(str_replace(";","", str_replace("var chapterimage=","", self::getCurl($url))),true);
            
       
            $images = $result['Images'];

            $imagePix = $result['ImagePix'];

            //两个两个图片现在
            foreach($images as $k=>$v)
            {
                $arr = parse_url($v);
            
                $imagePath = $imagePix.$arr["path"];
                
                $path = self::downloadImage($imagePath, $href, self::$_cartoon_id, self::$_catalog_id);

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
                
                    echo date("Y-m-d H:i:s").": ".$path."\tOne picture is complete\n";
            }

        }
        //echo "catalog_id: ".self::$catalog_id."\n";

        return self::$catalog_id;
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
