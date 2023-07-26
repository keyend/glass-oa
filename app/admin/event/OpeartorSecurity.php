<?php
namespace app\admin\event;
use app\cron\job\EntityMQService;
use think\facade\Log;
use think\facade\Cache;
use app\common\model\system\ConfigModel;

/**
 * 管理密码变更
 * 
 * @version 1.0.0
 */
class OpeartorSecurity
{
    public function handle($user = [])
    {
        if (!empty($user)) {
            redis()->delete("usr.{$user['SESSION_ID']}");
        }
    }
}