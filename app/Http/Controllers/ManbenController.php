<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Symfony\Component\DomCrawler\Crawler;

class ManbenController extends Controller
{
    public static function get()
    {
        echo self::getCurl('http://www.baidu.com');
        echo "我解析了";
        echo "\n";
        new Crawler('<html></html>');
    }
}
