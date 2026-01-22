<?php
return [
    'logis' => [
        'title' => '物流公司，具体微信端物流公司编码参见对照表',
        'value' => [
            'shunfeng' => 'SF',                   //系统内部物流公司编码 =》 微信物流公司编码
            'shentong' => 'STO',
            'yuantong' => 'YTO',
            'yunda' => 'YD',
            'zhongtong' => 'ZTO'
        ]        
    ],
    'api' => [
        'title' => '插件对外的接口都放到这里，接口请求的时候，会来此判断是否存在此控制器和方法,类似/config/api/api.php',
        'value' => [
            'api' => [      //控制器名称，可以随便起名，不过此控制器集成的基类一定要注意，不要写错了
                'code'   => 'Api',
                'method' => [
                    'crontab' => [
                        'code'     => 'crontab',    //去推送接口
                        'is_login' => false
                    ],
                ]
            ]
        ]
    ]
];
