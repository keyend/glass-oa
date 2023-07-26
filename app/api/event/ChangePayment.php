<?php
namespace app\api\event;
use think\facade\Log;
use think\facade\Cache;
use mashroom\provider\Request;
use app\common\model\Payment;

/**
 * 更新支付状态
 * 
 * @version 1.0.0
 */
class ChangePayment
{
    /**
     * 异步
     *
     * @param array $params
     * @return void
     */
    public function handle($params = [])
    {
        triggerAsync([__CLASS__, 'handleChange'], [$params]);
    }

    /**
     * 开通会员
     *
     * @param array $params
     * @return void
     */
    public function handleChange($params = [])
    {
        Log::info("支付状态变更 => " . json_encode($params));
        $payment = app()->make(Payment::class)->getOrder($params["trade_no"]);
        if ($payment->status == Payment::PAYMENT_STATUS_COMPLETE) {
            $payment->order->setComplete();
            $member_info = $payment->order->member;
            app()->request->merge([
                "group" => conf('vip.vip_group'),
                "group_expire" => $member_info['group_expire']
            ]);
            Log::info("订单支付完成 => {$payment->trade_no}");
        }
    }
}