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
        $query = $this->withJoin(["order"], "left");
        if (isset($filter['search_type']) && !empty($filter['search_type']) && isset($filter['search_value']) && !empty($filter['search_value']) ) {
            if (in_array($filter["search_type"], ["customer", "trade_no"])) {
                $query->where("order.{$filter["search_type"]}", 'LIKE', "%{$filter['search_value']}%");
            } elseif(in_array($filter["search_type"], ["category", "craft"])) {
                $query->where("order_goods.{$filter["search_type"]}", 'LIKE', "%{$filter['search_value']}%");
            }
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0]);
                $times[1] = strtotime($times[1]);
                $query->where('order.create_time', 'BETWEEN', $times);
            }
        }
        $uspage = true;
        if (!isset($filter['print']) || $filter['print'] != 1) {
            $uspage = false;
        }
        $list = [];
        $count = $query->count();
        $fields = "order_goods.*,order.trade_no,order.customer";
        $query->when($uspage, function($query) use($page, $limit) {
            $query->page($page,$limit);
        })->field($fields)->chunk(100, function ($lists) use (&$list) {
            foreach($lists as $item) {
                $row = $item->toArray();
                $row["height"] = (int)$row["height"];
                $row["width"] = (int)$row["width"];
                $list[] = $row;
            }
        }, "order_goods.id", "desc");
        $sql = $query->getLastSql();
        return compact('count', 'list', 'sql');
    }
}