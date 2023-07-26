<?php
/**
 * Payment 支付
 * @version 1.0.0
 */
namespace app\api\controller;
use app\api\Controller;
use app\common\model\crebo\Order as OrderModel;
use app\common\model\Payment as PaymentModel;

class Payment extends Controller
{
    /**
     * 返回支付方式列表
     *
     * @return void
     */
    public function types(PaymentModel $payment_model)
    {
        return $this->success($payment_model->getTypes());
    }

    /**
     * 订单支付
     *
     * @param OrderModel $order_model
     * @param PaymentModel $payment_model
     * @return void
     */
    public function order(OrderModel $order_model, PaymentModel $payment_model)
    {
        $validate = $this->validate($this->params([ 'trade_no' ], true), [ 'trade_no|订单号' => 'require|alphaNum|length:6,32' ]);
        if ($validate !== true) {
            return $this->fail($validate);
        }

        $payment = $payment_model->getOrder($this->params['trade_no']);
        if (empty($payment)) {
            $order_info = $order_model->getPending($this->params['trade_no']);
            if (empty($order_info)) {
                return $this->fail("订单记录不存在");
            }

            $params = $this->params([ "trade_no", ["notify_url", ""], ["trade_type", "JSAPI"] ]);
            $payment = PaymentModel::create([
                "type"          => PaymentModel::ORDER_PAYMENT,
                "trade_no"      => $order_info['trade_no'],
                "out_trade_no"  => PaymentModel::trade_no(),
                "trade_type"    => $params["trade_type"],
                "pay_type"      => $order_info["type"],
                "pay_status"    => 0,
                "pay_info"      => "",
                "status"        => 0,
                "notify_url"    => $params["notify_url"],
                "notify_status" => empty($params["notify_url"]) ? 1 : 0,
                "update_time"   => TIMESTAMP,
                "create_time"   => TIMESTAMP
            ]);
        }

        // 支付状态为支付中
        if ($payment->status == 1) {
            return $this->success([
                "trade_no" => $payment->trade_no,
                "out_trade_no" => $payment->out_trade_no,
                "pay_info" => $payment->pay_info,
            ]);
        } elseif($payment->status == 3) {
            return $this->success("已支付");
        } elseif($payment->status != 0) {
            return $this->success("已取消");
        }

        // try {
        //     $result = $payment->pay();
        // } catch(\Exception $e) {
        //     return $this->fail($e->getMessage());
        // }

        $payment->setComplete();

        return $this->success();
        // $this->app->config->set([ "response_data_type" => "text/html" ], "app");
        // return $this->fetch("success");
    }
}