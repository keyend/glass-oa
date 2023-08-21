<?php
namespace app\admin\event;
use think\facade\Log;
use think\facade\Cache;
use app\common\model\crebo\Order;
use app\common\model\crebo\OrderPay;

/**
 * 订单状态更新事件
 * @version 1.0.0
 */
class OrderChange
{
    public function handle($order_id)
    {
        $order = Order::find($order_id);
        if (empty($order)) {
            return false;
        } elseif($order['is_trash'] == 1) {
            return false;
        }

        if ($order['delivery_status'] == 0 && $order['deduct_num'] > 0) {
            $order->delivery_status = $order['deduct_num'] >= $order['order_num'] ? 2 : 1;
        } elseif($order['delivery_status'] == 1 && $order['deduct_num'] >= $order['order_num']) {
            $order->delivery_status = 2;
        }

        if ($order['pay_status'] == 0 && $order['pay_money'] > 0) {
            $order->pay_status = 1;
        } elseif($order['pay_status'] == 1 && $order['pay_money'] >= $order['order_money']) {
            $order->pay_status = 2;
        }

        if ($order->pay_status == 2) {
            $pay_money = (float)$order['pay_money'];
            $order_money = (float)$order['order_money'];
            $preferential = $order_money - $pay_money;
            $order->discount_money = $preferential;
            triggerAsync([__CLASS__, 'handleDiscount'], [[
                'trade_no' => $order->trade_no,
                'discount_money' => $preferential
            ]], 1);
        }

        if ($order['status'] != 2) {
            if ($order->pay_status == 2 && $order->delivery_status == 2) {
                $order->status = 2;
            }
        }

        $order->save();

        return $order;
    }

    /**
     * 把优惠金额写入最后一条收款记录的优惠金额下面
     *
     * @param [type] $param
     * @return void
     */
    public function handleDiscount($param)
    {
        $last = OrderPay::where("trade_no", $param["trade_no"])->order("id DESC")->find();
        if (!empty($last)) {
            $last->discount_money = $param["discount_money"];
            $last->save();
        }
    }
}