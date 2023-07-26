<?php
namespace mashroom\event;
use think\facade\Log;
use app\cron\job\JobManager;
/**
 * 心跳
 * 
 * @version 1.0.0
 */
class HeartBeat
{
    const name = 'heartbeat';
    const length = 3;

    private function push()
    {
        $serialize_id = uniqid();
        JobManager::push([
            'id'      => $serialize_id,
            'require' => JobManager::class,                             // 执行类
            'method'  => "get",                                         // 方法
            "argv"    => conf("basic.domain") . "/cron/index.html",     // 参数
            "delay"   => 1,                                             // 延时，每1秒执行一次
            "loop"    => true                                           // 是否重复
        ]);
        redis()->tag("config")->set("queue.{$serialize_id}", 1);
        redis()->incr(env("cache.prefix", "") . self::name);
    }

    public function handle()
    {
        if (defined('MODULE') && MODULE == "cron") {
            return;
        }
        $length = redis()->get(self::name);
        if (self::length <= $length) {
            return;
        } elseif ($length == 0) {
            $this->push();
            Log::info("添加心跳 => {$length}");
        }
    }
}