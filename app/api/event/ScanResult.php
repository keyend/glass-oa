<?php
namespace app\api\event;
use think\facade\Log;
use think\facade\Cache;

/**
 * 扫码回调
 * 
 * @version 1.0.0
 */
class ScanResult
{
    public function handle($oauth)
    {
        $user = redis()->get("usr.{$oauth['access_token']}");
        if (empty($user)) {
            Log::error("登录失败 => 缓存过期");
            return false;
        }

        if (!defined('S0')) {
            define('S0', $oauth['access_token']);
        }

        $user["user_id"] = $oauth['user_id'];

        try {
            request()->login($user);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}