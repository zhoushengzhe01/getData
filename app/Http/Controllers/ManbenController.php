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
use App\Model\CartoonsComment;
use App\Model\CartoonsUser;
use App\Model\CartoonsCategory;
use App\Model\CartoonsCategoryCartoon;


class ManbenController extends Controller
{

    protected static $domain = 'http://www.manben.com';

    protected static $cartoon_id;

    protected static $_cartoon_id;
    
    protected static $catalog_id;

    protected static $_catalog_id;


    public static function getBookData()
    {   
        date_default_timezone_set('PRC');
        
        $pageindex = 90;

        start:
        
        $standard_time = date("D M d Y H:i:s ").'GMT'.date("O").' (中国标准时间)';

        $url = self::$domain . "/mh-updated/pagerdata.ashx?t=8&pageindex=".$pageindex."&sc=1&d=".$standard_time;
        
        $result = json_decode(self::getCurl($url),true);
        
        //倒着过来
        for($i=count($result) ; $i>0 ; $i--)
        {
            $data = $result[$i-1];

            $cartoon_id = self::getCartoon($data);

            //设置同步完成
            Cartoon::where('cartoon_id', '=', $cartoon_id)->update(['state'=>2]);

            echo date("Y-m-d H:i:s").": Ready for the next one\n";

            sleep(2);
        }
        $pageindex--;
        goto start;
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
            
            $area_id = 3;   //选默认三
            
            $letter = implode('',pinyin($title));

            $image = self::downloadImage($crawler->filter('.comicInfo .img img')->attr('src'), $url, 'cover');

            $status = trim(str_replace("状  态：", "", $crawler->filter('.comicInfo .info .ib')->eq(9)->text()))=='连载中' ? 1 : 2;
            
            $energy = trim(str_replace("万","", str_replace("漫画战力：","", $crawler->filter('.comicInfo .info .ib')->eq(6)->text())));
            
            $author = trim(str_replace("作  者：","", $crawler->filter('.comicInfo .info .ib')->eq(3)->text()));
    
            $source = $url;
    
            $view = trim(str_replace("阅读人次：","", $crawler->filter('.comicInfo .info .ib')->eq(5)->text()));
    
            $collect = trim(str_replace("收藏数：","", $crawler->filter('.comicInfo .info .ib')->eq(4)->text()));

            $category = trim(str_replace("类  别：","", $crawler->filter('.comicInfo .info .ib')->eq(7)->text()));

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

            //分类设置
            self::updateCategory(trim($category));

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

        //章节获取 1:话  2:卷  3:番外
        for($n=0 ; $n<3 ; $n++)
        {
            $catalogCount = $crawler->filter('#chapterlistload .list')->eq($n)->filter('.ib')->count();
           
            for($i=$catalogCount ; $i>0 ; $i--)
            {
                $item = $crawler->filter('#chapterlistload .list')->eq($n)->filter('.ib')->eq($i-1);
    
                $cid = str_replace("/","", str_replace("m","", $item->attr('href')));
                
                $title = $item->text();
    
                //查找数据库是否存在
                $catalog_id = self::getCartoonsCatalog($cid, $n+1, $title);
                
                //处理状态完成
                CartoonsCatalog::where('catalog_id', '=', $catalog_id)->update(['state'=>2]);
    
                echo date("Y-m-d H:i:s").": \tOne chapter is complete\n";
            }
        }

        echo date("Y-m-d H:i:s").": \tA cartoon finished\n";

        //返回标识ID
        return self::$cartoon_id;

    }

    //类别处理
    public static function updateCategory($category)
    {

        $cartoonsCategory = CartoonsCategory::orderBy('sort', 'asc')->get();

        if($category)
        {
            foreach(explode('/',$category) as $v)
            {
                foreach($cartoonsCategory as $key=>$val)
                {
                    if(strpos($val['keyword'], $v) !== false)
                    {
                        //查看是否已经处理过
                        $count = CartoonsCategoryCartoon::where('cartoon_id', '=', self::$cartoon_id)
                                ->where('category_id', '=', $val['id'])
                                ->count();
    
                        if($count<1)
                        {
                            $categoryCartoon = new CartoonsCategoryCartoon();
    
                            $categoryCartoon->cartoon_id = self::$cartoon_id;
                            $categoryCartoon->category_id = $val['id'];
                            $categoryCartoon->save();
                        }
                    }
                }
            }
        }
        else
        {
            $categoryCartoon = new CartoonsCategoryCartoon();
            
            $categoryCartoon->cartoon_id = self::$cartoon_id;
            $categoryCartoon->category_id = 16;
            $categoryCartoon->save();
        }
        
    }

    //获取章节图片
    public static function getCartoonsCatalog($cid, $type, $title)
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
            $cartoonsCatalog->type = $type;
            $cartoonsCatalog->source = $href;
            $cartoonsCatalog->is_delete = 0;
            $cartoonsCatalog->save();

            $cartoonsCatalog = CartoonsCatalog::where('cartoon_id', '=', self::$cartoon_id)->where('catalog_id', '=', self::$catalog_id)->first();
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
        
        if($imageCount==0)
        {
            $arrow = ['0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','a'=>'10','b'=>'11','c'=>'12','d'=>'13','e'=>'14','f'=>'15','g'=>'16','h'=>'17','i'=>'18','j'=>'19','k'=>'20','l'=>'21','m'=>'22','n'=>'23','o'=>'24','p'=>'25','q'=>'26','r'=>'27','s'=>'28','t'=>'29','u'=>'30','v'=>'31','w'=>'32','x'=>'33','y'=>'34','z'=>'35'];

            $string = $catalog->filter('script')->eq(2)->text();
            
            preg_match_all("#[0-9a-z]+://[0-9a-z\-\.\/]+\?#", $string, $matches);
            $keyArr = $matches[0];

            preg_match("#[0-9a-zA-Z]+\|[/s]?\|[0-9a-zA-Z\|\_]+#", $string, $matches);
            $imagePath = explode('|' , $matches[0]);

            $images = [];

            foreach($keyArr as $val)
            {
                preg_match_all("#[0-9a-z\:\/\.\-]#", $val, $matches);
                $letterArr = $matches[0];

                $image = '';
                foreach($letterArr as $v)
                {
                    if(preg_match("/^[0-9a-z]$/",$v))
                    {
                        $image .= $imagePath[$arrow[$v]];
                    }
                    else
                    {
                        $image .= $v;
                    }
                }

                $images[] = $image;
            }

            //下载并插入图片
            foreach($images as $k=>$v)
            {
                $arr = parse_url($v);
            
                $imagePath = $v;
                
                $path = self::downloadImage($imagePath, $href, self::$_cartoon_id, self::$_catalog_id);

                $imageInfo = getimagesize(public_path($path));
                
                $cartoonsImage = new CartoonsImage;

                    $cartoonsImage->cartoon_id = self::$cartoon_id;
                    $cartoonsImage->catalog_id = self::$catalog_id;
                    $cartoonsImage->path = $path;
                    $cartoonsImage->size = filesize(public_path($path))?filesize(public_path($path)):0;
                    $cartoonsImage->height = empty($imageInfo[1])?0:$imageInfo[1];
                    $cartoonsImage->width = empty($imageInfo[0])?0:$imageInfo[0];
                    $cartoonsImage->sort = 50;
                    $cartoonsImage->is_delete = 0;
                    $cartoonsImage->source = $imagePath;
                    $cartoonsImage->save();
                
                    echo date("Y-m-d H:i:s").": ".$path."\tOne picture is complete\n";
            }

            //处理本章节阅读方式
            CartoonsCatalog::where('catalog_id', '=', self::$catalog_id)->update(['read'=>2]);

        }
        else
        {
            //获取图片
            for($i=0; $i<=$imageCount; $i++)
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

                    $imageInfo = getimagesize(public_path($path));

                    $cartoonsImage = new CartoonsImage;

                        $cartoonsImage->cartoon_id = self::$cartoon_id;
                        $cartoonsImage->catalog_id = self::$catalog_id;
                        $cartoonsImage->path = $path;
                        $cartoonsImage->size = filesize(public_path($path))?filesize(public_path($path)):0;
                        $cartoonsImage->height = empty($imageInfo[1])?0:$imageInfo[1];
                        $cartoonsImage->width = empty($imageInfo[0])?0:$imageInfo[0];
                        $cartoonsImage->sort = 50;
                        $cartoonsImage->is_delete = 0;
                        $cartoonsImage->source = $imagePath;
                        $cartoonsImage->save();
                    
                        echo date("Y-m-d H:i:s").": ".$path."\tOne picture is complete\n";
                }
            }

            //处理本章节阅读方式
            CartoonsCatalog::where('catalog_id', '=', self::$catalog_id)->update(['read'=>1]);
        }

        echo "catalog_id: ".self::$catalog_id."\n";

        return self::$catalog_id;
    }


    //获取评论 + 评论用户
    public static function getCommentData()
    {
        //查找漫画
        $cartoon = Cartoon::where('state', '=', '2')->orderBy('updated_at', 'asc')->first();

        if(!empty($cartoon))
        {
            self::$cartoon_id = $cartoon['cartoon_id'];
            
            //Cartoon::where('id', '=', $cartoon['id'])->update(['state'=>5]);

            //获取多少页
            $crawler = new Crawler(self::getCurl($cartoon['source']));
            
            $count = $crawler->filter('.comment .pager .pageBtn')->count();
            $count = $crawler->filter('.comment .pager .pageBtn')->eq($count-2)->text();

            for($i=$count ; $i>0 ; $i--)
            {
                if($i==1)
                {
                    $url = $cartoon['source'];
                }
                else
                {
                    $url = $cartoon['source']."p".$i;
                }
                self::getCommentItem($url);
            }

            //结束
            Cartoon::where('id', '=', $cartoon['id'])->update(['state'=>5]);

        }
    
    }
    
    //遍历评论
    public static function getCommentItem($url)
    {

        $crawler = new Crawler(self::getCurl($url));

        $count = $crawler->filter('.commentInfo .list .item')->count();
        

        for($i=$count ; $i>0 ; $i--)
        {

            $itemComment = $crawler->filter('.commentInfo .list .item')->eq($i-1);

            $avatar = $itemComment->filter('.avatar')->attr('src');
            $name = $itemComment->filter('.info span')->eq(0)->text();
            
            //时间
            $time = trim($itemComment->filter('.info span')->eq(1)->text());
            //评论
            $content = trim($itemComment->filter('.content')->text());
            //点赞数量
            $support = str_replace(")","", str_replace("(","", $itemComment->filter('.bottom span span')->text()));
            //点赞数量
            $support = (intval($support) + rand(1, 5)) * rand(1, 20);
            
            $user = self::getUserId($name, $avatar);
           
            //同一个漫画  同一个人 内容一样
            $Comment = CartoonsComment::where('cartoon_id', '=', self::$cartoon_id)
                    ->where('user_id', '=', $user['id'])
                    ->where('content', '=', $content)
                    ->first();
              
            if(empty($Comment))
            {
                
                $cartoonsComment = new CartoonsComment();

                    $cartoonsComment->cartoon_id = self::$cartoon_id;
                    $cartoonsComment->user_id = $user['id'];
                    $cartoonsComment->user_name = $user['name'];
                    $cartoonsComment->user_head = $user['header'];
                    $cartoonsComment->support = $support;
                    $cartoonsComment->reply = 0;
                    $cartoonsComment->content = $content;
                    $cartoonsComment->save();

            }

        }

    }

    public static function getUserId($name, $avatar)
    {
        $name = str_replace("漫本", "桔子会员", trim($name));

        //查找是否有此用户
        $cartoonsUser = CartoonsUser::where('name','=',$name)->first();

        if(empty($cartoonsUser))
        {
          
            //判断用户名是否邮箱
            if(preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/',$name))
            {  
                $email = $name;
            }
            else
            {
                $email = "";
            }

            //判断是否手机号
            if(preg_match("/^1[34578]{1}\d{9}$/",$name))
            {  
                $mobile = $name;
            }
            else
            {  
                $mobile = "";
            }

            $password = md5(rand(100000, 999999));
            
            $header = self::downloadImage($avatar, '', 'header');
   
            $user = new CartoonsUser();

                $user->name = $name;
                $user->password = $password;
                $user->header = $header;
                $user->email = $email;
                $user->mobile = $mobile;
                $user->login_at = date("Y-m-d H:i:s");

                $user->save();

            $cartoonsUser = CartoonsUser::where('name','=',$name)->first();
            
        }

        return $cartoonsUser;

    }

}
