<?php
namespace app\admin\event;
use think\facade\Log;
use think\facade\Cache;
use app\common\model\system\ConfigModel;

/**
 * 会员实名事件
 * @version 1.0.0
 */
class ConfigChange
{
    public function handle()
    {
        triggerAsync([__CLASS__, 'handleCache'], []);
    }

    public function handleCache($argv = [])
    {
        $settings = app()->make(ConfigModel::class)->select()->toArray();
        foreach($settings as $setting) {
            redis()->tag("config")->set("config.{$setting['parent']}.{$setting['name']}", $setting["value"]);
        }
        Log::info("更新系统配置缓存");
    }
}