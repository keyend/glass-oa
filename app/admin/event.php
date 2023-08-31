<?php
// 事件定义文件
return [
    'bind'      => [],
    'listen'    => [
        'ConfigChange'          => ['app\admin\event\ConfigChange'],
        'WithdrawChange'        => ['app\admin\event\WithdrawChange'],
        'MemeberActiveChange'   => ['app\admin\event\MemeberActiveChange'],
        'TipoffChange'          => ['app\admin\event\TipoffChange'],
        'PolicyChange'          => ['app\admin\event\PolicyChange'],
        'OpeartorSecurity'      => ['app\admin\event\OpeartorSecurity'],
        'OrderChange'           => ['app\admin\event\OrderChange'],
        'OrderDeliveryChange'   => ['app\admin\event\OrderDeliveryChange'],
    ],
    'subscribe' => [],
];
