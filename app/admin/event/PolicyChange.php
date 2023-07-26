<?php
namespace app\admin\event;
use app\cron\job\EntityMQService;
use think\facade\Log;
use think\facade\Cache;
use app\common\model\crebo\Policys;

/**
 * 储存策略事件
 * 
 * @version 1.0.0
 */
class PolicyChange
{
    public function handle()
    {
        EntityMQService::push(__CLASS__, ['handleCache', []]);
    }

    public function handleCache($argv = [])
    {
        $policys = app()->make(Policys::class)->select()->toArray();
        foreach($policys as $policy) {
            redis()->tag("config")->set("policy.{$policy['id']}", $policy);
        }
    }
}