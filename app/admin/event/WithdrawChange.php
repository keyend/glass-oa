<?php
namespace app\admin\event;
use app\cron\job\EntityMQService;
use think\facade\Log;
use think\facade\Cache;

/**
 * 提现事件
 * @version 1.0.0
 */
class WithdrawChange
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
    {}
}