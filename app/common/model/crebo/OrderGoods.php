<?php
namespace app\common\model\crebo;
use app\Model;

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
        if (isset($filter['search_value']) && !empty($filter['search_value']) ) {
            $filter['search_value'] = trim($filter['search_value']);
            $query->where("order_goods.category|order_goods.craft|order_goods.width|order_goods.height|order.customer|order.address|order.mobile", 'LIKE', "%{$filter['search_value']}%");
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0] . " 00:00:00");
                $times[1] = strtotime($times[1] . " 23:59:59");
                $query->where('order.create_time', 'BETWEEN', $times);
            }
        }
        $query->where("order_goods.is_delete", "=", 0);
        $query->where("order.is_trash", "=", 0);
        $fields = "order_goods.*,order.trade_no,order.customer,order.order_num";
        $result = $this->maps(function($query, $page, $limit) {
            $cursor = $query->order("order_goods.id DESC")->cursor();
            $sql = $query->getLastSql();
            foreach($cursor as $row) {
                $list[] = $this->mapsItem(function($row, $item) {
                    $row["customer"] = $item->order["customer"];
                    $row["height"] = (float)$row["height"];
                    $row["width"] = (float)$row["width"];
                    return $row;
                }, $row);
            }
            return compact('list', 'sql');
        }, [
            "query"  => $query,
            "filter" => $filter,
            "fields" => $fields,
            "page"   => $page,
            "limit"  => $limit,
            'title'  => '标签记录_' . date("Y_m_d"),
            'headers'=> [
                ["title" => "开单时间", "field" => "create_time", "width" => 18],
                ["title" => "客户名称", "field" => "customer", "width" => 24],
                ["title" => "产品名称", "field" => "category", "width" => 24],
                ["title" => "工艺", "field" => "craft", "width" => 12],
                ["title" => "宽mm", "field" => "width", "width" => 12],
                ["title" => "高mm", "field" => "height", "width" => 12],
                ["title" => "数量", "field" => "num", "width" => 12],
                ["title" => "面积m²", "field" => "area", "width" => 12],
                ["title" => "单价", "field" => "unitprice", "width" => 12],
                ["title" => "备注", "field" => "remark", "width" => 96]
            ]
        ]);

        return $result;
    }

    /**
     * 补单记录列表
     *
     * @param [type] $page
     * @param [type] $limit
     * @param array $filter
     * @return void
     */
    public function getSupplementList($page, $limit, $filter = [])
    {
        $query = $this->withJoin(["order"], "left");
        if (isset($filter['search_value']) && !empty($filter['search_value']) ) {
            $filter['search_value'] = trim($filter['search_value']);
            $query->where("order_goods.category|order_goods.craft|order_goods.width|order_goods.height|order.customer|order.address|order.mobile", 'LIKE', "%{$filter['search_value']}%");
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0] . " 00:00:00");
                $times[1] = strtotime($times[1] . " 23:59:59");
                $query->where('order.create_time', 'BETWEEN', $times);
            }
        }
        $query->where("order_goods.is_delete", "=", 0);
        $query->where("order.is_trash", "=", 0);
        $query->where("order_goods.type", 'IN', [2,3]);
        $fields = [
            "order_goods.parent_id as id",
            "order_goods.print_label",
            "order_goods.create_time",
            "order_goods.num",
            "order_goods.width",
            "order_goods.height",
            "order_goods.area",
            "order_goods.category",
            "order_goods.craft",
            "order_goods.remark",
            "order.trade_no,order.customer,order.order_num"
        ];
        $fields = implode(",",$fields);
        $columns = [
            ["title" => "补单时间", "field" => "create_time", "width" => 18],
            ["title" => "客户名称", "field" => "customer", "width" => 24],
            ["title" => "订单编号", "field" => "trade_no", "width" => 18, "type" => "numeric"],
            ["title" => "产品名称", "field" => "category", "width" => 24],
            ["title" => "工艺", "field" => "craft", "width" => 12],
            ["title" => "宽mm", "field" => "width", "width" => 12],
            ["title" => "高mm", "field" => "height", "width" => 12],
            ["title" => "面积m²", "field" => "area", "width" => 12],
            ["title" => "补单数量", "field" => "num", "width" => 12],
            ["title" => "下单数量", "field" => "num1", "width" => 12],
            ["title" => "备注", "field" => "remark", "width" => 96]
        ];
        $result = $this->maps(function($query, $page, $limit) {
            $cursor = $query->order("order_goods.id DESC")->cursor();
            $sql = $query->getLastSql();
            $list = [];
            foreach($cursor as $row) {
                $list[] = $this->mapsItem(function($row, $item) {
                    $row["customer"] = $item->order["customer"];
                    $row["trade_no"] = $item->order["trade_no"];
                    $row["order_num"] = $item->order["order_num"];
                    $row["height"] = (float)$row["height"];
                    $row["width"]  = (float)$row["width"];
                    return $row;
                }, $row);
            }
            return compact('list', 'sql');
        }, [
            "query"  => $query,
            "filter" => $filter,
            "fields" => $fields,
            "page"   => $page,
            "limit"  => $limit,
            "headers"=> $columns,
            'title' => '补单记录_' . ($filter['search_value'] ?? "") . "_" . date("Y_m_d"),
        ]);

        return $result;
    }
}