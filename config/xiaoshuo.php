<?php
return [
    //笔趣阁采集
    'biqudu_com'=>[

        'domain' => 'http://www.biqiuge.com',
        
        'imagePath' => 'download/xs/',

        'url' => '/book/{start}/',

        //小说页
        'fiction' => [
            //采集规则
            'title' => ['item', '#maininfo #info h1>0>text'],

            'author' => ['item', '#maininfo #info p>0>text'],

            'intro' => ['item', '#maininfo #intro>0>text'],

            'image' => ['item', '#fmimg img>0>src'],

            'category' => ['item', '.con_top>0>text'],

            'cataloglist' => ['list', '#list dd', 'a>0>href'],
        ],

        //阅读页面
        'fictionCatalog' => [
            
            'title'=> ['item', '.bookname h1>0>text'],

            'content'=> ['item', '#content>0>html']
        ],

        'category' =>['玄幻小说','修真小说','都市小说','穿越小说','网游小说','科幻小说'],
        
    ]
];
