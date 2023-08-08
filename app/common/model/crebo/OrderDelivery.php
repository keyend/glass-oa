<?php
namespace app\common\model\crebo;
use think\Model;
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
        $condition = [];
        $condition[] = "order.is_trash = {$filter['is_trash']}";
        if (isset($filter['search_value']) && !empty($filter['search_value']) ) {
            $where = [];
            $where[] = "`order`.customer LIKE '%{$filter['search_value']}%'";
            $where[] = "`order`.address LIKE '%{$filter['search_value']}%'";
            $where[] = "`order`.mobile LIKE '%{$filter['search_value']}%'";
            $where[] = "`order`.trade_no LIKE '%{$filter['search_value']}%'";
            $where[] = "`delivery`.trade_no LIKE '%{$filter['search_value']}%'";
            $condition[] = "(" . implode(" OR ", $where) . ")";
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0] . " 00:00:00");
                $times[1] = strtotime($times[1] . " 23:59:59");
                $condition[] = "delivery.create_time BETWEEN " . implode( " AND " , $times);
            }
        }
        if (!isset($filter["print"]) || $filter["print"] != 1) {
            $limit = " LIMIT " . (($page - 1) * $limit) . ",{$limit}";
        } else {
            $limit = "";
        }
        $prefix = env("database.prefix", "");
        $tables = "{$prefix}{$this->name} `delivery` LEFT JOIN " .
        "{$prefix}users_orders `order` ON (`delivery`.order_id = `order`.id) LEFT JOIN " . 
        "{$prefix}users `member` ON (`order`.customer_id = `member`.id)";
        $condition = implode(" ) AND (", $condition);
        if (!empty($condition)) {
            $condition  = " WHERE ({$condition})";
        }
        $count_query = Db::query("SELECT COUNT(*) AS think_count FROM {$tables} {$condition}");
        $count = (int)$count_query[0]['think_count'];
        $list = Db::query("SELECT delivery.*,member.nickname,member.desc as `address`,member.mobile FROM {$tables} {$condition} ORDER BY delivery.id DESC {$limit}");
        $sql = $this->getLastSql();
        foreach($list as &$row) {
            $row["create_time"] = date("Y-m-d H:i:s", $row["create_time"]);
            $row["goods"] = OrderDeliveryGoods::where("delivery_id", $row["id"])->select();
            $row["order"] = Order::where("id", $row["order_id"])->field("customer,address,trade_no,order_money,order_num,is_trash")->find();
            foreach($row["goods"] as &$goods) {
                $goods["width"] = (float)$goods["width"];
                $goods["height"] = (float)$goods["height"];
                $goods["umb"] = floatval($goods['width']) . "mm X " . floatval($goods['height']) . "mm X {$goods['num']} = " . round($goods['area'] * $goods['num'], 2) . "m² X {$goods['unitprice']}元 = {$goods['delivery_money']}元";
                $goods["remark"] = OrderGoods::where("id", $goods["goods_id"])->value("remark");
            }
            $row["total_money"] = $row["manual_money"] + $row["delivery_money"];
        }

        return compact('count', 'list', 'sql');
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