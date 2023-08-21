<?php
namespace app\admin\event;
use think\facade\Log;
use think\facade\Cache;
use app\common\model\crebo\Order;

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
            $order->delivery_status = 1;
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
        }

        if ($order['status'] != 2) {
            if ($order->pay_status == 2 && $order->delivery_status == 2) {
                $order->status = 2;
            }
        }

        $order->save();
    }
}