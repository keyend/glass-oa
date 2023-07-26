<?php
// 事件定义文件
return [
    'bind'      => [],
    'listen'    => [
        'Console'     => [ 'mashroom\event\Console' ],
        'AppConstant' => [ 'mashroom\event\Constant' ],
        'AppInit'  => [
            'mashroom\event\Security',
            'mashroom\event\Adapter'
        ],
        'HttpRun'  => [],
        'HttpEnd'  => [
            'mashroom\event\HeartBeat'
        ],
        'LogLevel' => [],
        'LogWrite' => []
    ],
    'subscribe' => [],
];
