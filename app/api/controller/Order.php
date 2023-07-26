<?php
/**
 * Order 订单
 * @version 1.0.0
 */
namespace app\api\controller;
use app\api\Controller;
use app\common\model\crebo\Order as OrderModel;
use app\common\model\Payment as PaymentModel;

class Order extends Controller
{
    /**
     * 返回套餐列表
     *
     * @return void
     */
    public function combo()
    {
        $rule = getVipRule();
        return $this->success($rule);
    }

    /**
     * 创建订单
     *
     * @param OrderModel $order_model
     * @return void
     */
    public function create(OrderModel $order_model, PaymentModel $payment_model)
    {
        try {
            $vip_type = input( 'post.vip', 0 );
            $share_id = input( 'post.share_id', 0 );
            $pay_type = input( 'post.pay_type', 0 );
            $pay_types = array_values(array_column($payment_model->getTypes(), "name"));
            if ( !in_array($pay_type, $pay_types) ) {
                return $this->fail( '支付方式错误' );
            }
    
            // 获取VIP规则
            $rule = getVipRule();
            // 判断VIP类型是否正确
            if ( !isset( $rule[ $vip_type ] ) ) {
                return $this->fail( 'VIP类型不存在' );
            }
    
            // 获取VIP价格
            $vip_config = $rule[ $vip_type ];
            // 本地创建订单号
            $trade_no = strtoupper( date( 'YmdHis' ) . substr( md5( uniqid() ), 0, 10 ) );
            // 订单数据
            $order = [
                'uid'       => S1,
                'trade_no'  => $trade_no,
                'type'      => $pay_type,
                'profit_id' => $share_id,
                'money'     => $vip_config[ 'money' ],
                'vip_day'   => $vip_config[ 'day' ],
                'vip_id'    => $vip_type,
                'status'    => 0,
                'create_time' => TIMESTAMP
            ];
            // 订单创建
            $order["id"] = $order_model->insertGetId( $order );
            // 5分钟后
            addCron(__CLASS__ . "::close", [$order], 300);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }

        return $this->success([
            "id" => $order['id'],
            "trade_no" => $order['trade_no']
        ]);
    }

    /**
     * 关闭订单
     *
     * @param array $order
     * @return void
     */
    public function close($order = [])
    {
        $order_info = app()->make(OrderModel::class)->getPending($order["trade_no"]);
        if (!empty($order_info)) {
            $order_info->status = -1;
            $order_info->save();
        }
    }
}