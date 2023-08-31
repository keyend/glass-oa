<?php
namespace app\admin\event;
use think\facade\Log;
use think\facade\Cache;
use app\common\model\crebo\Order;
use app\common\model\crebo\OrderDelivery;

/**
 * 配送状态更新事件
 * @version 1.0.0
 */
class OrderDeliveryChange
{
    public function handle($order_id)
    {
        $receive_num = (int)OrderDelivery::where("order_id", $order_id)->where("status", 1)->sum("delivery_num");
        $order_model = new Order();
        $order = $order_model->find($order_id);
        $bubble = false;
        if (!empty($order)) {
            if ($order["order_num"] <= $receive_num) {
                $bubble = true;
                $order->delivery_status = 2;
            }
            $order->receive_num = \think\facade\Db::raw("receive_num + {$receive_num}");
            $order->save();
            if ($bubble)
                event("OrderChange", $order_id);
        }
    }
}