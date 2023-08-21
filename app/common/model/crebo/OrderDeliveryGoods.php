<?php
namespace app\common\model\crebo;
use app\Model;

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
        $query = $this->withJoin(["delivery", "goods", "order"], "left");
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0] . " 00:00:00");
                $times[1] = strtotime($times[1] . " 23:59:59");
                $query->where('delivery.create_time', 'BETWEEN', $times);
            }
        }

        $query->where("order.is_trash", "=", 0);
        $query->where("goods.is_delete", "=", 0);
        if (!empty($filter["keyword"])) {
            $query->where("order.customer|goods.craft|goods.category|goods.width|goods.height|order.address|goods.remark|order.mobile|delivery.trade_no|order.trade_no", 'LIKE', "%{$filter['keyword']}%");
        }

        $fields = "order_delivery_goods.id,order.create_time,delivery.create_time as delivery_time,order.customer,order.trade_no";
        $fields.= ",goods.category,goods.craft,goods.width,goods.height,goods.area,order_delivery_goods.num,goods.unitprice";
        $fields.= ",order_delivery_goods.manual_money,order_delivery_goods.delivery_money,goods.remark";
        $columns = [
            ["title" => "开单时间", "field" => "create_time", "width" => 18],
            ["title" => "送货时间", "field" => "delivery_time", "width" => 18],
            ["title" => "客户名称", "field" => "customer", "width" => 24],
            ["title" => "订单编号", "field" => "trade_no", "width" => 18, "type" => "numeric"],
            ["title" => "产品名称", "field" => "category", "width" => 24],
            ["title" => "工艺", "field" => "craft", "width" => 12],
            ["title" => "宽mm", "field" => "width", "width" => 12],
            ["title" => "高mm", "field" => "height", "width" => 12],
            ["title" => "数量", "field" => "num", "width" => 12],
            ["title" => "面积m²", "field" => "area", "width" => 12],
            ["title" => "单价", "field" => "unitprice", "width" => 12],
            ["title" => "加工费", "field" => "manual_money", "width" => 12],
            ["title" => "金额", "field" => "total_money", "width" => 12],
            ["title" => "备注", "field" => "remark", "width" => 96]
        ];

        $result = $this->maps(function($query, $page, $limit) {
            if ($page == 1) {
                $manual_money = (float)$query->sum("order_delivery_goods.manual_money");
                $delivery_money = (float)$query->sum("order_delivery_goods.delivery_money");
                $total_money = round($manual_money + $delivery_money, 2);
                $total_area = (float)$query->sum("order_delivery_goods.area");
                $total_num = (float)$query->sum("order_delivery_goods.num");
            }
            $cursor = $query->order("order_delivery_goods.id DESC")->cursor();
            $sql = $query->getLastSql();
            $list = [];
            foreach($cursor as $row) {
                $list[] = $this->mapsItem(function($row, $item) {
                    $row["height"] = (int)$row["height"];
                    $row["width"] = (int)$row["width"];
                    $row["customer"] = $item->order["customer"];
                    $row["trade_no"] = $item->order["trade_no"];
                    $row["remark"] = $item->goods["remark"];
                    $row["create_time"] = $item->order["create_time"];
                    $row["delivery_time"] = $item->delivery["create_time"];
                    $row["manual_money"] = (float)$row["manual_money"];
                    $row["delivery_money"] = (float)$row["delivery_money"];
                    $row["total_money"] = $row["delivery_money"] + $row["manual_money"];
                    return $row;
                }, $row);
            }

            return compact('list', 'manual_money', 'delivery_money', 'total_money', 'total_area', 'total_num', 'sql');
        }, [
            "query"  => $query,
            "filter" => $filter,
            "fields" => $fields,
            "page"   => $page,
            "limit"  => $limit,
            "headers"=> $columns,
            'title' => '订单汇总_' . date("Y_m_d"),
        ]);

        return $result;
    }
}