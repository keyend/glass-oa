<?php
namespace app\api\event;
use think\facade\Log;
use think\facade\Cache;
use app\common\model\Message;

/**
 * 发送验证码
 * 
 * @version 1.0.0
 */
class SendMobileCode
{
    /**
     * 发送
     *
     * @param array $params
     * @return void
     */
    public function handle($params = [])
    {
        $template = conf("register.sms_template");
        $content = str_replace("[code]", $params["code"], $template);
        app()->make(Message::class)->sendMessage(0, $params["mobile"], $content);
    }
}