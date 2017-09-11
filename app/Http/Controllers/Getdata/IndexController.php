<?php

namespace App\Http\Controllers\Getdata;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Jobs\GetManbenData;

use Symfony\Component\DomCrawler\Crawler;


class IndexController extends Controller
{

    //页面处理
    public function index(Request $request)
    {
        

        dispatch(new GetManbenData($request));

    }
}
