<?php
namespace app\common\model\crebo;
use think\Model;

class OrderGoods extends Model
{
    protected $name = "users_orders_goods";

    /**
     * 订单商品
     * @collection relation.model
     */
    public function order()
    {
        return $this->hasOne(Order::class, "id", "order_id");
    }

    /**
     * 汇总
     *
     * @param int page
     * @param int limit 页码
     * @param array 筛选条件
     * @return array
     */
    public function getList($page, $limit, $filter = [])
    {

    }
}