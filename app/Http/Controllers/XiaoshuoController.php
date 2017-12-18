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
            if(empty($fiction) || $fiction->state==1)
            {
                $author = self::getResult($html, self::$config['fiction']['author']);
                $intro = self::getResult($html, self::$config['fiction']['intro']);
                $image = self::downloadImg(self::$config['domain'] . self::getResult($html, self::$config['fiction']['image']), self::$config['imagePath']);
                
                
                if(empty($fiction))
                    $fiction = new Fiction;
                
                $fiction->user_id = 1;
                $fiction->title = $title;
                $fiction->letter = implode('', pinyin($title));
                $fiction->image = $image;
                $fiction->status = 1;
                $fiction->author = $author;
                $fiction->source = $url;
                $fiction->view = 100;
                $fiction->collect = 100;
                $fiction->is_recommend = 0;
                $fiction->is_publish = 1;
                $fiction->intro = $intro;
                $fiction->state = 1;
                $fiction->save();
    
                $cataloglist = self::getResult($html, self::$config['fiction']['cataloglist']);
    
                if(is_array($cataloglist))
                {
                    foreach($cataloglist as $k=>$v){
                        self::getBookCatalog($fiction->id, $url.$v);
                    }
                }
                Fiction::where('id', '=', $fiction->id)->update(['state'=>2]);

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
            $fiction_catalog->sort = 0;
            $fiction_catalog->source = $url;
            $fiction_catalog->is_delete = 0;
            $fiction_catalog->state = 2;
            $fiction_catalog->save();
            $catalog_id = $fiction_catalog->id;
    
    
            $fiction_content = new FictionCatalogContent();
            $fiction_content->fiction_id = $fiction_id;
            $fiction_content->catalog_id = $catalog_id;
            $fiction_content->content = $content;
            $fiction_content->save();

            echo date("Y-m-d H:i:s").": \t ".$title." 完成\n";
    
        }

    }
}
