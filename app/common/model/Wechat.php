<?php
/**
 * 微信
 * 
 * @package common.model.Wechat
 * @version 1.0.0
 */
namespace app\common\model;
use app\Model;
use app\common\model\auth\OAuth;
use app\common\model\crebo\Users;
use EasyWeChat\Factory;
use EasyWeChat\Payment\Order;
use think\facade\Log;

class Wechat extends Model 
{
    /**
     * 表名
     *
     * @var string
     */
    protected $name = 'wechat_log';

    /**
     * object.
     *
     * @var object
     */
    private static $app;

    /**
     * 通信配置
     *
     * @var array
     */
    private static $config = [];

    /**
     * 初始化处理
     * @access protected
     * @return void
     */
    protected static function init()
    {
        self::$config = [
            'app_id'        => conf("register.wx_appid"),
            'secret'        => conf("register.wx_secret"),
            'token'         => conf("register.wx_token"),
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            /**
             * 日志配置
             *
             * level: 日志级别, 可选为：debug/info/notice/warning/error/critical/alert/emergency
             * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
             * file：日志文件位置(绝对路径!!!)，要求可写权限
             */
            'log'           => [
                'level'      => 'debug',
                'permission' => 0777,
                'file'       => app()->getRuntimePath() . 'log/' . date('Ym') . '/' . date('d') . '.log',
            ],
            // payment
            'payment' => [
                'merchant_id'        => conf("pay.weixin_mchid"),
                'key'                => conf("pay.weixin_key"),
                'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
                'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
                // 你也可以在下单时单独设置来想覆盖它
                'notify_url'         => conf("basic.domain") . '/api/payment/notify.html',
                // 'device_info'     => '013467007045764',
                // 'sub_app_id'      => '',
                // 'sub_merchant_id' => '',
                // ...
            ]
        ];

        self::$app = Factory::officialAccount(self::$config);
    }

    /**
     * 返回对象
     *
     * @return void
     */
    private function server()
    {
        return self::$app->server;
    }

    /**
     * 返回微信相关信息
     *
     * @param string $openid
     * @return void
     */
    public function getInfo($openid = '')
    {
        $info = self::where("openid", $openid)->find();
        if (empty($info)) {
            try {
                $wechat_user = self::$app->user->get($openid);
                if (isset($result['errcode']) && $result['errcode'] != 0) {
                    throw new \Exception($result["errmsg"], 50089);
                }
                $nickname_decode = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $wechat_user['nickname']);
                $headimgurl      = $wechat_user['headimgurl'];
                $sex             = $wechat_user['sex'];
                $language        = $wechat_user['language'];
                $country         = $wechat_user['country'];
                $province        = $wechat_user['province'];
                $city            = $wechat_user['city'];
                $district        = "无";
                $openid          = $wechat_user['openid'];
                $nickname        = $wechat_user['nickname'];
                if (!empty($wechat_user['unionid'])) {
                    $unionid = $wechat_user['unionid'];
                } else {
                    $unionid = '';
                }
                $memo = $wechat_user['remark'];
                $info = self::create([
                    'sex'              => $sex,
                    'nickname'         => $nickname,
                    'nickname_decode'  => $nickname_decode,
                    'headimgurl'       => $headimgurl,
                    'language'         => $language,
                    'country'          => $country,
                    'province'         => $province,
                    'city'             => $city,
                    'openid'           => $openid,
                    'unionid'          => $unionid,
                    'is_subscribe'     => 1,
                    'remark'           => $memo,
                    'create_time'      => TIMESTAMP
                ]);
            } catch(\Exception $e) {
                Log::error("[WECHAT]::{$e->getMessage()}");
            }
        }
        return $info;
    }

    /**
     * 返回微信登录二维码
     *
     * @param array $params
     * @return void
     */
    public function getLoginQrcode($params = [])
    {
        $result = self::$app->qrcode->temporary([ "scene_str" => S0 ], 300);
        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($result["errmsg"], 50021);
        } elseif(isset($result['ticket']) && !empty($result['ticket'])) {
            app()->make(OAuth::class)->where("access_token", S0)->update([ "ticket" => $result["ticket"] ]);
        }

        return $result;
    }

    /**
     * 监听请求
     * 
     * @return array
     */
    public function listener()
    {
        $message = $this->server()->getMessage();
        if (isset($message['MsgType'])) {
            $method = __FUNCTION__ . ucfirst($message['MsgType']);
            if (method_exists($this, $method)) {
                $this->$method($message);
            }
        }
        return $this->server()->serve()->send();
    }

    /**
     * 监听事件
     *
     * @param mixed $message
     * @return void
     */
    private function listenerEvent($message) 
    {
        $this->server()->push(function ($res) {
            $fans = $this->getInfo($res['FromUserName']);
            if ($res['Event'] == 'subscribe') {
                // 关注公众号
                if (preg_match("/^qrscene_/", $res['EventKey'])) {
                    $access_token = substr($res['EventKey'], 8);
                    $this->listenerScan($access_token, $fans);
                }
            } else if ($res['Event'] == 'unsubscribe') {
                //取消关注
                $fans->is_subscribe = 0;
                $fans->update_time = TIMESTAMP;
                $fans->save();
            } else if ($res['Event'] == 'SCAN') {
                // SCAN事件 - 用户已关注时的事件推送 - 扫描带参数二维码事件
                $access_token = $res['EventKey'];
                $this->listenerScan($access_token, $fans);
            } else if ($res['Event'] == 'CLICK') {
                // CLICK事件 - 自定义菜单事件
            }
        });
    }

    /**
     * 扫码回调事件
     *
     * @param string $access_token
     * @param app\common\model\Wechat $fans
     * @return void
     */
    private function listenerScan($access_token, $fans)
    {
        $user_model = app()->make(Users::class);
        $user = null;
        if ($fans->user_id != 0) {
            $user = $user_model->where("id", $fans->user_id)->find();
        }

        if (empty($user)) {
            $default_group = conf( 'register.default_group' );
            $user = $user_model->register(array_keys_filter($fans->toArray(), [
                ["username", "u_" . uniqid()],
                ["nickname", ""],
                ["openid",   $fans->openid],
                ["avatar",   $fans->headimgurl],
                ["desc",     $fans->remark],
                ["group",    $default_group],
                ["status",   1],
                ["device_type", "mp"],
                "create_time"
            ]));
            $fans->user_id = $user["id"];
            $fans->save();
        }

        $oauth = app()->make(OAuth::class)->where("access_token", $access_token)->find();
        if (empty($oauth)) {
            return false;
        }
        $oauth->user_id = $fans->user_id;
        $oauth->save();
        event("ScanResult", $oauth);
        Log::info("[{$user->username}] => 微信扫码登录");
    }

    /**
     * 支付
     *
     * @param app\common\model\Payment $payment
     * @return void
     */
    public function pay($payment) 
    {
        $user = Users::where("user_id", S1)->find();
        if (empty($user->openid)) {
            throw new Exception("用户暂不支持，您可以使用微信扫码登录后，绑定到您的用户即可使用此方式登录!");
        }

        // 获取VIP规则
        $rule = getVipRule();
        // 获取VIP价格
        $vip_config = $rule[ $vip_type ];
        // 支付金额
        $money = $payment->order->money * 100;

        $attributes = [
            'trade_type'       => $payment->trade_type, // JSAPI，NATIVE，APP...
            'body'             => $vip_config[ 'name' ],
            'detail'           => $vip_config[ 'discount_msg' ],
            'out_trade_no'     => $payment->out_trade_no,
            'total_fee'        => $money, // 单位：分
            'openid'           => $user->openid, // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
        ];

        try {
            $order = new Order($attributes);
            $result = self::$app->payment->pay($order);

            if ($result->return_code != "SUCCESS") {
                throw new \Exception($result->return_msg);
            } elseif($result->result_code != "SUCCESS") {
                throw new \Exception($result->result_msg);
            }

            if ($payment->trade_type == "JSAPI") {
                // 生成支付 JS 配置
                $res = self::$app->payment->configForPayment($result->prepay_id, true);
            } elseif($payment->trade_type == "APP") {
                // 返回 APP 配置
                $res = self::$app->payment->configForAppPayment($result->prepay_id);
            } elseif($payment->trade_type == "NATIVE") {
                // 返回二维码地址
                $res = [
                    "prepay_id" => $payment->prepay_id,
                    "imgUrl" => $payment->code_url
                ];
            }

            $payment->transaction_no = $result->prepay_id;
            $payment->pay_info = $res;
            $payment->save();
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $res;
    }
}