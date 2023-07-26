<?php
/**
 * 消息记录
 * 
 * @package common.model.Wechat
 * @version 1.0.0
 */
namespace app\common\model;
use app\Model;
use think\facade\Log;

class Message extends Model 
{
    protected $name = "message_log";

    /**
     * 消息接口
     *
     * @var array
     */
    protected $protocol = [ 'sms' ];

    /**
     * 发送消息
     *
     * @param integer $type
     * @param string $destination
     * @param string $message
     * @return void
     */
    public function sendMessage($type, $destination, $message = '')
    {
        if (!isset($this->protocol[$type])) {
            Log::error("消息发送失败 => [{$type}]接口不存在");
            return false;
        }

        $method = __FUNCTION__ . ucfirst($this->protocol[$type]);
        
        return self::create([
            "type" => (int)$type,
            "destination" => $destination,
            "content" => $message,
            "status" => $this->$method($destination, $message),
            "create_time" => TIMESTAMP
        ]);
    }

    /**
     * 发送短信
     *
     * @param string $mobile
     * @param string $content
     * @return integer
     */
    private function sendMessageSms($mobile = '', $content = '')
    {
        $url = "http://service.winic.org:8009/sys_port/gateway/index.asp?";
        $data = "id=%s&pwd=%s&to=%s&content=%s&time=";
        $id = conf("register.winic_id", "");
        $pwd = conf("register.winic_secret", "");
        $to = $mobile; 
        $content = iconv("UTF-8", "GB2312", $content);
        $rdata = sprintf($data, $id, $pwd, $to, $content);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$rdata);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        $ret = 1;
        if (!curl_errno($ch)) {
            $ret = 0;
            Log::error("短信发送失败 => {$result}");
        }
        curl_close($ch);
        return $ret;
    }
}