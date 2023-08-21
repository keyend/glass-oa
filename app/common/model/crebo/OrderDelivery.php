<?php
namespace app\common\model\crebo;
use app\Model;
use think\facade\Db;
use think\facade\Cache;

class OrderDelivery extends Model
{
    protected $name = "users_delivery";

    /**
     * 配送单明细
     * @collection relation.model
     */
    public function goods()
    {
        return $this->hasMany(OrderDeliveryGoods::class, 'delivery_id', 'id');
    }

    /**
     * 订单
     * @collection relation.model
     */
    public function order()
    {
        return $this->hasOne(Order::class, "id", "order_id");
    }

    /**
     * 列表
     *
     * @param int page
     * @param int limit 页码
     * @param array 筛选条件
     * @return array
     */
    public function getList($page, $limit, $filter = [])
    {
        $query = $this->withJoin(["order"], "left")->with(['goods' => function($query) use($filter) {
            if (isset($filter['search_value']) && !empty($filter['search_value']) ) {
                $filter['search_value'] = trim($filter['search_value']);
                $query->where("category|craft|width|height", 'LIKE', "%{$filter['search_value']}%");
                $query->where("is_delete", "=", 0);
            }
        }]);
        if (isset($filter['search_value']) && !empty($filter['search_value']) ) {
            $filter['search_value'] = trim($filter['search_value']);
            $query->where("order_delivery.trade_no|order.customer|order.address|order.mobile", 'LIKE', "%{$filter['search_value']}%");
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0] . " 00:00:00");
                $times[1] = strtotime($times[1] . " 23:59:59");
                $query->where('delivery.create_time', 'BETWEEN', $times);
            }
        }
        $query->where("order.is_trash", "=", $filter['is_trash']);
        $columns = [
            ["title" => "配送时间", "field" => "create_time", "width" => 18],
            ["title" => "客户名称", "field" => "customer", "width" => 24],
            ["title" => "配送单号", "field" => "trade_no", "width" => 18, "type" => "numeric"],
            ["title" => "配送数量", "field" => "delivery_num", "width" => 12],
            ["title" => "配送总金额", "field" => "total_money", "width" => 12],
            ["title" => "打印次数", "field" => "print_times", "width" => 12],
            ["title" => "配送地址", "field" => "address", "width" => 24],
            ["title" => "备注", "field" => "remark", "width" => 96]
        ];

        $result = $this->maps(function($query, $page, $limit) {
            $cursor = $query->order("order_delivery.id DESC")->cursor();
            $sql = $query->getLastSql();
            $list = [];
            foreach($cursor as $row) {
                $list[] = $this->mapsItem(function($row, $item) {
                    $row["customer"] = $item->order["customer"];
                    $row["address"] = $item->order["address"];
                    $row["mobile"] = $item->order["mobile"];
                    $row["order"] = $item->order->toArray();
                    $row["goods"] = $item->goods->toArray();
                    $row["total_money"] = $row["manual_money"] + $row["delivery_money"];
                    foreach($row["goods"] as &$goods) {
                        $goods["width"] = (float)$goods["width"];
                        $goods["height"] = (float)$goods["height"];
                        $goods["umb"] = floatval($goods['width']) . "mm X " . floatval($goods['height']) . "mm X {$goods['num']} = " . round($goods['area'] * $goods['num'], 2) . "m² X {$goods['unitprice']}元 = {$goods['delivery_money']}元";
                        $goods["remark"] = OrderGoods::where("id", $goods["goods_id"])->value("remark");
                    }
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
            'title' => '配送记录_' . ($filter['search_value'] ?? "") . "_" . date("Y_m_d"),
        ]);

        return $result;
    }

    /**
     * 返回订单数据
     *
     * @return void
     */
    public function getBetweenData($times= [])
    {
        $count = (int)self::where("create_time", "BETWEEN", $times)->sum("delivery_num");
        $delivery_money = (float)self::where("create_time", "BETWEEN", $times)->sum("delivery_money");
        $manual_money = (float)self::where("create_time", "BETWEEN", $times)->sum("manual_money");
        $money = round($delivery_money + $manual_money, 4);
        $money = number_format($money, 2, '.', '');
        return compact('count', 'money');
    }

    /**
     * 返回近月数据
     *
     * @return void
     */
    public function getChartData()
    {
        $timestamp = time();
        $previousTime = strtotime(date("Y-m-d 00:00:00", $timestamp - 30 * 86400));
        $version = date("ymdH");
        $maps = [$previousTime,$timestamp,$version];
        $version = md5(json_encode($maps));
        $cache = Cache::get("shape_delivery_{$version}");
        if (!empty($cache)) {
            return $cache;
        }

        $maps = ["deliveryNum" => "配送数量", "deliveryMoney" => "配送金额"];
        $result = [];
        $result["names"] = array_values($maps);
        $result["dates"] = [];
        $result["list"] = [];
        $colors = ["#ffc000", "#44abf7"];
        $values = [];
        while($previousTime < $timestamp) {
            $time = date("Y/m/d", $previousTime);
            foreach($maps as $key => $name) {
                if (!isset($values[$key])) {
                    $values[$key] = [
                        "name" => $name,
                        "type" => "line",
                        "data" => [],
                        "markPoint" => [
                            "data" => [
                                ["type" => "max", "name" => "最大值"],
                                ["type" => "min", "name" => "最小值"],
                            ]
                        ],
                        "itemStyle" => [ "normal" => ["color" => $colors[$i]] ]
                    ];
                }
                $method = "getChartData" . ucfirst($key);
                $values[$key]["data"][] = $this->$method($previousTime);
            }
            $result["dates"][] = $time;
            $previousTime += 86400;
        }
        $result["list"] = array_values($values);
        Cache::set("shape_delivery_{$version}", $result);
        return $result;
    }

    /**
     * 获取订单数
     *
     * @param integer $timestamp
     * @return void
     */
    private function getChartDataDeliveryNum($timestamp) 
    {
        return self::where("create_time", "BETWEEN", [$timestamp, $timestamp + 86399])->sum("delivery_num");
    }

    /**
     * 获取订单金额
     *
     * @param integer $timestamp
     * @return void
     */
    private function getChartDataDeliveryMoney($timestamp) 
    {
        return self::where("create_time", "BETWEEN", [$timestamp, $timestamp + 86399])->sum("delivery_money");
    }
}