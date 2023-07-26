<?php
use mashroom\provider\Request;
use mashroom\provider\Response;
use mashroom\provider\Handle;
use mashroom\provider\Validate;
use mashroom\provider\Trace;
// 容器Provider定义文件
return [
    'think\Validate'         => Validate::class,
    'think\Request'          => Request::class,
    'think\Response'         => Response::class,
    'think\exception\Handle' => Handle::class,
    'think\trace\Unable'     => Trace::class
];
