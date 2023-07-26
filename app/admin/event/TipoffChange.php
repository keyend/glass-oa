<?php
namespace app\admin\event;
use app\cron\job\EntityMQService;
use think\facade\Log;
use think\facade\Cache;

/**
 * 会员实名事件
 * @version 1.0.0
 */
class TipoffChange
{
    public function handle($data)
    {
        EntityMQService::push(__CLASS__, ['handleCache', [$data]]);
    }

    public function handleCache($argv = [])
    {}
}