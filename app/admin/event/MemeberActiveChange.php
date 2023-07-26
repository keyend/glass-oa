<?php
namespace app\admin\event;
use app\cron\job\EntityMQService;
use think\facade\Log;
use think\facade\Cache;

/**
 * 会员实名事件
 * @version 1.0.0
 */
class MemeberActiveChange
{
    public function handle($data)
    {
        EntityMQService::push(__CLASS__, ['handleCache', [$data]]);
    }

    /**
     * 更新缓存
     *
     * @return void
     */
    public function handleCache($argv = [])
    {
    }
}