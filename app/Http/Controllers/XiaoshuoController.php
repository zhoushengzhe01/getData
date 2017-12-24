<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Symfony\Component\DomCrawler\Crawler;

use App\Model\Xiaoshuo\Fiction;
use App\Model\Xiaoshuo\FictionCatalog;
use App\Model\Xiaoshuo\FictionCatalogContent;
use App\Model\Xiaoshuo\FictionCategory;

class XiaoshuoController extends Controller
{

    protected static $config;

    public static function getBookData($website)
    {

        date_default_timezone_set('PRC');

        self::$config = config('xiaoshuo.'.$website);
        if(!is_array(self::$config))
            die('未找到配置文件');
        
        $page = 96;

        start:

            $url = self::$config['domain'].str_replace('{start}', $page, self::$config['url']);            
            $html = self::getCurl( $url );

            //获取数据
            $title = self::getResult($html, self::$config['fiction']['title']);

            $fiction = Fiction::where('title', '=', $title)->first();
            if(empty($fiction) || $fiction->type==1)
            {
                $author = self::getResult($html, self::$config['fiction']['author']);
                $intro = self::getResult($html, self::$config['fiction']['intro']);
                $image = self::downloadImg(self::$config['domain'] . self::getResult($html, self::$config['fiction']['image']), self::$config['imagePath']);
                $category = self::getResult($html, self::$config['fiction']['category']);

                if(empty($fiction))
                    $fiction = new Fiction;
                    
                $fiction->user_id = 1;
                $fiction->category_id = self::getCategory($category);
                $fiction->title = $title;
                $fiction->letter = implode('', pinyin($title));
                $fiction->image = $image;
                $fiction->author = $author;
                $fiction->intro = $intro;
                $fiction->state = 1;
                $fiction->type = 1;
                $fiction->work = 0;
                $fiction->source = $url;
                $fiction->is_recommend = 0;
                $fiction->is_index = 0;
                $fiction->is_publish = 1;
                $fiction->publish_at = date('Y-m-d H:i:s');
                $fiction->save();
             
                $cataloglist = self::getResult($html, self::$config['fiction']['cataloglist']);
    
                if(is_array($cataloglist))
                {
                    foreach($cataloglist as $k=>$v){
                        self::getBookCatalog($fiction->id, $url.$v);
                    }
                }
                Fiction::where('id', '=', $fiction->id)->update(['type'=>2]);

                echo date("Y-m-d H:i:s").": ".$title." 完成\n";
                
                sleep(2);
            }

        $page++;
        goto start;
    }

    //获得详细信息数据
    public static function getBookCatalog($fiction_id, $url)
    {
        $html = self::getCurl($url);

        $title = self::getResult($html, self::$config['fictionCatalog']['title']);
        $content = self::getResult($html, self::$config['fictionCatalog']['content']);

        $fiction_catalog = FictionCatalog::where('fiction_id', '=', $fiction_id)->where('title', '=', $title)->first();
        if(empty($fiction_catalog))
        {
            $fiction_catalog = new FictionCatalog();

            $fiction_catalog->fiction_id = $fiction_id;
            $fiction_catalog->title = $title;
            $fiction_catalog->subtitle = '';
            $fiction_catalog->work = mb_strlen($content, 'UTF-8');
            $fiction_catalog->sort = 0;
            $fiction_catalog->type = 2;
            $fiction_catalog->source = $url;
            $fiction_catalog->is_delete = 0;
            $fiction_catalog->save();
    
            $fiction_content = new FictionCatalogContent();
            $fiction_content->fiction_id = $fiction_id;
            $fiction_content->catalog_id = $fiction_catalog->id;
            $fiction_content->content = $content;
            $fiction_content->save();


            //最新章节
            $fiction = Fiction::where('id', '=', $fiction_id)->first();
            $fiction->work = ($fiction->work) + mb_strlen($content, 'UTF-8');
            $fiction->new_catalog_id = $fiction_catalog->id;
            $fiction->new_catalog_title = $fiction_catalog->title;
            $fiction->save();

            echo date("Y-m-d H:i:s").": \t ".$title." 完成\n";
    
        }

    }

    //分类处理
    public static function getCategory($category)
    {
        //没有的分类则添加
        $array = self::$config['category'];
        foreach($array as $key=>$val)
        {
            if(strpos($category, $val))
            {
                $category_title = $val;
                continue;
            }
        }

        if(empty($category_title)){
            echo "找不到分类";
            die($category);
        }
       
        $category = FictionCategory::where('name','=',$category_title)->first();

        if(empty($Category))
        {
            //添加
            $Category = new FictionCategory;
            $Category->name = $category_title;
            $Category->letter = implode('', pinyin($category_title));
            $Category->sort = 10;
            $Category->icon = '';
            $Category->keyword = $category_title;

            $Category->save();

            return $Category->id;
        }
        else
        {
            return $Category->id;
        }
    }
}
