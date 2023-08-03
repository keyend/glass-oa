<?php
namespace app\common\model\crebo;
use think\Model;

class OrderDeliveryGoods extends Model
{
    protected $name = "users_delivery_goods";

    /**
     * 订单
     * @collection relation.model
     */
    public function delivery()
    {
        return $this->hasOne(OrderDelivery::class, "id", "delivery_id");
    }

    /**
     * 订单商品
     * @collection relation.model
     */
    public function goods()
    {
        return $this->hasOne(OrderGoods::class, "id", "goods_id");
    }

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
    public function getConverge($page, $limit, $filter = [])
    {
        static $excel = null;
        static $columns = null;

        if ($filter['export'] == 1) {
            $excel = new \mashroom\Excel();
            $columns = [
                ["title" => "ID", "field" => "id", "width" => 6],
                ["title" => "开单时间", "field" => "create_time", "width" => 18],
                ["title" => "送货时间", "field" => "delivery_time", "width" => 18],
                ["title" => "客户名称", "field" => "customer", "width" => 24],
                ["title" => "订单编号", "field" => "trade_no", "width" => 18, "type" => "numeric"],
                ["title" => "产品名称", "field" => "category", "width" => 24],
                ["title" => "工艺", "field" => "craft", "width" => 12],
                ["title" => "宽mm", "field" => "width", "width" => 12],
                ["title" => "高mm", "field" => "height", "width" => 12],
                ["title" => "面积m²", "field" => "area", "width" => 12],
                ["title" => "数量", "field" => "num", "width" => 12],
                ["title" => "单价", "field" => "unitprice", "width" => 12],
                ["title" => "加工费", "field" => "manual_money", "width" => 12],
                ["title" => "金额", "field" => "total_money", "width" => 12],
                ["title" => "备注", "field" => "remark", "width" => 96]
            ];
        }

        $query = $this->withJoin(["delivery", "goods", "order"], "left");
        if (isset($filter['search_type']) && !empty($filter['search_type']) && isset($filter['search_value']) && !empty($filter['search_value']) ) {
            if (in_array($filter["search_type"], ["nickname"])) {
                $query->where("order.customer", 'LIKE', "%{$filter['search_value']}%");
            }
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0] . " 00:00:00");
                $times[1] = strtotime($times[1] . " 23:59:59");
                $query->where('delivery.create_time', 'BETWEEN', $times);
            }
        }

        if (!empty($filter["keyword"])) {
            $query->where("order.customer|goods.craft|goods.category|goods.width|goods.height", 'LIKE', "%{$filter['keyword']}%");
        }

        $uspage = true;
        if ((isset($filter['export']) && $filter['export'] == 1) || (isset($filter['print']) && $filter['print'] == 1)) {
            $uspage = false;
        }

        $list = [];
        $total_money = 0;
        $count = $query->count();
        $fields = "order_delivery_goods.id,order.create_time,delivery.create_time as delivery_time,order.customer,order.trade_no";
        $fields.= ",goods.category,goods.craft,goods.width,goods.height,goods.area,order_delivery_goods.num,goods.unitprice";
        $fields.= ",order_delivery_goods.manual_money,order_delivery_goods.delivery_money,goods.remark";
        $query->field($fields);
        if ((isset($filter['export']) && $filter['export'] == 1) || (isset($filter['print']) && $filter['print'] == 1)) {
            $query->chunk(100, function ($lists) use (&$list, $filter, $columns, $excel) {
                if ($filter['export'] == 1) {
                    $list = [];
                }
    
                foreach($lists as $item) {
                    $row = $item->toArray();
                    $row["height"] = (int)$row["height"];
                    $row["width"] = (int)$row["width"];
                    $row["create_time"] = date("Y-m-d H:i:s", $row["create_time"]);
                    $row["delivery_time"] = date("Y-m-d H:i:s", $row["delivery_time"]);
                    $row["manual_money"] = (float)$row["manual_money"];
                    $row["delivery_money"] = (float)$row["delivery_money"];
                    $row["total_money"] = $row["delivery_money"] + $row["manual_money"];
                    $list[] = $row;
                }
    
                if ($filter['export'] == 1) {
                    $excel->excel($list, [
                        'title' => '订单汇总_' . date("Y_m_d"),
                        'headers' => $columns
                    ]);
                }
            }, "order_delivery_goods.id", "desc");
        } else {
            $manual_money = (float)$query->sum("order_delivery_goods.manual_money");
            $delivery_money = (float)$query->sum("order_delivery_goods.delivery_money");
            $total_money = round($manual_money + $delivery_money, 2);
            $list = $query->page($page,$limit)->order("order_delivery_goods.id DESC")->select()->each(function($row) {
                $row["height"] = (int)$row["height"];
                $row["width"] = (int)$row["width"];
                $row["create_time"] = date("Y-m-d H:i:s", $row["create_time"]);
                $row["delivery_time"] = date("Y-m-d H:i:s", $row["delivery_time"]);
                $row["manual_money"] = (float)$row["manual_money"];
                $row["delivery_money"] = (float)$row["delivery_money"];
                $row["total_money"] = $row["delivery_money"] + $row["manual_money"];
                return $row;
            });
        }
        $sql = $query->getLastSql();
        if ($filter['export'] == 1) {
            $excel->excel(null, []);
        }

        return compact('count', 'list', 'manual_money', 'delivery_money', 'total_money', 'sql');
    }
}