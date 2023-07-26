<?php
// 事件定义文件
return [
    'bind'      => [],
    'listen'    => [
        'SendMobileCode'          => ['app\api\event\SendMobileCode'],
        'ScanResult'              => ['app\api\event\ScanResult'],
        'ChangePayment'           => ['app\api\event\ChangePayment'],
    ],
    'subscribe' => [],
];
