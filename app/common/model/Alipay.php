<?php
/**
 * 支付宝支付
 * 
 * @package common.model.Alipay
 * @version 1.0.0
 */
namespace app\common\model;
use app\Model;
use app\common\model\auth\OAuth;
use app\common\model\crebo\Users;
use EasyWeChat\Factory;
use think\facade\Log;

class Alipay extends Model 
{
    /**
     * 表名
     *
     * @var string
     */
    protected $name = 'wechat_log';

	private $alipay_config;
	private $sign_type;
	private $keys;
	private $input_charset;
	private $partner;

	/**
	 *支付宝网关地址（新）
	 */
	public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
	//public $alipay_gateway_new = 'https://openapi.alipay.com/gateway.do';
	
    /**
     * HTTPS形式消息验证地址
     */
	var $https_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
     * HTTP形式消息验证地址
     */
	var $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

    /**
     * 支付
     *
     * @param app\common\model\Payment $payment
     * @return void
     */
    public function pay($payment)
    {

    }
}