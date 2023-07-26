<?php
/**
 * 支付记录
 * 
 * @package common.model.Payment
 * @version 1.0.0
 */
namespace app\common\model;
use app\Model;
use app\common\model\crebo\Order as OrderModel;
use think\facade\Log;

class Payment extends Model 
{
    protected $name = "payment_log";
    protected $types = [
        [
            "id" => 0,
            "name" => "wxpay",
            "title" => '微信支付'
        ],
        [
            "id" => 1,
            "name" => "alipay",
            'title' => '支付宝'
        ]
    ];

    const ORDER_PAYMENT = 1;
    const RECHARGE_PAYMENT = 0;

    CONST PAYMENT_STATUS_COMPLETE = 1;
    CONST PAYMENT_STATUS_CANCEL = -1;
    CONST PAYMENT_STATUS_FAIL = -1;

    /**
     * 设置支付为成功
     *
     * @return void
     */
    public function setComplete()
    {
        $this->setAttr("pay_time", TIMESTAMP);
        $ret = $this->setStatus(self::PAYMENT_STATUS_COMPLETE);
        return $ret;
    }

    /**
     * 更新支付状态
     *
     * @param integer $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->setAttr("status", $status);
        $this->setAttr("update_time", TIMESTAMP);
        $ret = $this->save();
        event("ChangePayment", $this->getData());
        return $ret;
    }

    /**
     * 所属订单
     *
     * @return void
     */
    public function order()
    {
        return $this->hasOne(OrderModel::class, 'trade_no', 'trade_no');
    }

    /**
     * 返回支付类型
     *
     * @return void
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * 支付信息格式化
     *
     * @param string $value
     * @return void
     */
    public function getPayInfoAttr($value)
    {
        return unserialize($value);
    }

    /**
     * 支付信息写入
     *
     * @param array $value
     * @return void
     */
    public function setPayInfoAttr($value)
    {
        return !is_string($value) ? serialize($value) : $value;
    }

    /**
     * 返回一个订单号
     *
     * @return void
     */
    public static function trade_no($prefix = '')
    {
        $id = self::where("1=1")->max("id");
        $serialze_no = date("ymdHis");

        return $prefix . $serialze_no . $id;
    }

    /**
     * 返回订单支付记录
     *
     * @param string $trade_no
     * @return void
     */
    public function getOrder($trade_no = '')
    {
        return self::where("type", 1)->where("trade_no", $trade_no)->find();
    }

    /**
     * 返回支付信息
     *
     * @return void
     */
    public function pay()
    {
        $model = app()->make($this->getAttr("wxpay") ? Wechat::class : Alipay::class);
        return $model->pay($this);
    }
}