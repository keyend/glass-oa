<?php
// 中间件配置
return [
    // 别名或分组
    'alias'    => [
        'ConsoleAuthorize'  => \app\common\middleware\ConsoleAuthorize::class,
        'Authorize'         => \app\common\middleware\Authorize::class,
        'CheckAccessToken'  => \app\common\middleware\CheckAccessToken::class
    ],
    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [
        'MultiApp',
        'AllowCrossDomain',
        'SessionInit',
        'LoadLangPack'
    ],
];
