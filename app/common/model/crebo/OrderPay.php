<?php
namespace app\common\model\crebo;
use app\Model;
use think\facade\Db;
use app\common\model\system\UserModel;

class OrderPay extends Model
{
    protected $name = "users_orders_pay";

    /**
     * 获取器
     *
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function getPayInfoAttr($value, $data)
    {
        return unserialize($value);
    }

    /**
     * 修改器
     *
     * @param [type] $value
     * @return void
     */
    public function setPayInfoAttr($value)
    {
        return serialize($value);
    }

    /**
     * 所属用户
     * @collection relation.model
     */
    public function member()
    {
        return $this->hasOne(Users::class, 'id', 'customer_id')->field('id,username,nickname,mobile,group');
    }

    /**
     * 所属用户
     * @collection relation.model
     */
    public function user()
    {
        return $this->hasOne(UserModel::class, 'user_id', 'uid')->field("user_id,username");
    }

    /**
     * 所属用户
     * @collection relation.model
     */
    public function order()
    {
        return $this->hasOne(Order::class, 'trade_no', 'trade_no');
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
        $query = $this->withJoin(["member", "user"], "left");

        if (isset($filter['search_value']) && !empty($filter['search_value']) ) {
            $query->where("order_pay.pay_info|order_pay.customer|member.mobile|member.desc|order_pay.trade_no|user.username", 'LIKE', "%{$filter['search_value']}%");
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0]);
                $times[1] = strtotime($times[1]);
                $query->where('order_pay.pay_time', 'BETWEEN', $times);
            }
        }
        if (isset($filter['customer_id']) && $filter['customer_id'] !== 0) {
            $query->where("customer_id", $filter["customer_id"]);
        }
        $result = $this->maps(function($query, $page, $limit) {
            if ($page == 1) {
                $pay_money = (float)$query->sum("order_pay.pay_money");
                $discount_money = (float)$query->sum("order_pay.discount_money");
            }
            $cursor = $query->order("order_pay.id DESC")->cursor();
            $sql = $query->getLastSql();
            $list = [];
            foreach($cursor as $row) {
                $list[] = $this->mapsItem(function($row, $item) {
                    $row["pay_time"] = date("Y-m-d H:i:s", $row["pay_time"]);
                    $row["mobile"] = $item->member["mobile"];
                    $row["address"] = $item->member["address"];
                    $row["operator"] = $item->user["username"];
                    $row["remark"] = $item->pay_info->remark;
                    return $row;
                }, $row);
            }
            return compact('list', 'sql', 'discount_money', 'pay_money');
        }, [
            "query"  => $query,
            "filter" => $filter,
            "fields" => $fields,
            "page"   => $page,
            "limit"  => $limit,
            "headers"=> [
                ["title" => "收款时间", "field" => "pay_time", "width" => 24],
                ["title" => "流水号", "field" => "trade_no", "width" => 12, "type" => "numeric"],
                ["title" => "客户名称", "field" => "customer", "width" => 24],
                ["title" => "联系电话", "field" => "mobile", "width" => 18],
                ["title" => "地址", "field" => "address", "width" => 48],
                ["title" => "收款金额", "field" => "pay_money", "width" => 12, "sum" => 1],
                ["title" => "优惠金额", "field" => "discount_money", "width" => 12, "sum" => 1],
                ["title" => "操作员", "field" => "operator", "width" => 12],
                ["title" => "备注", "field" => "remark", "width" => 96]
            ],
            'title' => '收款明细_' . ($filter['search_value'] ?? ""),
        ]);

        return $result;
    }

    /**
     * 应收账款
     *
     * @param [type] $page
     * @param [type] $limit
     * @param array $filter
     * @return void
     */
    public function getReceivable($page, $limit, $filter = [])
    {
        $query = Order::order("id DESC");
        if (isset($filter['search_value']) && !empty($filter['search_value']) ) {
            $filter['search_value'] = trim($filter['search_value']);
            $query->where("trade_no|out_trade_no|customer|mobile|address", 'LIKE', "%{$filter['search_value']}%");
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0] . " 00:00:00");
                $times[1] = strtotime($times[1] . " 23:59:59");
                $query->where('create_time', 'BETWEEN', $times);
            }
        }
        $query->where("delivery_status", ">", 0);
        $query->where("pay_status", "<>", 2);
        $query->group("customer_id");
        $query->having("SUM(order_money)>0");
        $fields = [
            "MAX(id) as id",
            "customer_id",
            "customer",
            "mobile",
            "address",
            "SUM(order_money) as order_money",
            "SUM(pay_money) as pay_money"
        ];
        $fields = implode(",",$fields);
        $result = $this->maps(function($query, $page, $limit) {
            if ($page == 1) {
                $order_money = (float)$this->getSum($query, "order_money");
                $pay_money = (float)$this->getSum($query, "pay_money");
                $surplus_money = $order_money - $pay_money;
            }
            $cursor = $query->order("id DESC")->cursor();
            $sql = $query->getLastSql();
            $list = [];
            foreach($cursor as $row) {
                $list[] = $this->mapsItem(function($row, $item) {
                    $row["pay_time"] = "";
                    $row["operator"] = "";
                    $row["order_money"] = (float)$row["order_money"];
                    $row["pay_money"] = (float)$row["pay_money"];
                    $row["surplus_money"] = $row["order_money"] - $row["pay_money"];
                    $last = self::with(['user'])->where("customer_id", $row["customer_id"])->order("id DESC")->field("id,pay_time")->find();
                    if (!empty($last)) {
                        $row["operator"] = $last->user->username;
                        $row["pay_time"] = date("Y-m-d H:i:s", $last["pay_time"]);
                    }
                    return $row;
                }, $row);
            }
            return compact('list', 'sql', 'order_money', 'pay_money', 'surplus_money');
        }, [
            "query"  => $query,
            "filter" => $filter,
            "fields" => $fields,
            "page"   => $page,
            "limit"  => $limit,
            "headers"=> [
                ["title" => "客户名", "field" => "customer", "width" => 24],
                ["title" => "联系手机", "field" => "mobile", "width" => 18, "type" => "numeric"],
                ["title" => "订单金额", "field" => "order_money", "width" => 12, "sum" => 1],
                ["title" => "结余金额", "field" => "surplus_money", "width" => 12, "sum" => 1],
                ["title" => "最后收款时间", "field" => "pay_time", "width" => 24],
                ["title" => "操作员", "field" => "operator", "width" => 12],
                ["title" => "联系地址", "field" => "address", "width" => 96]
            ],
            'title' => '应收账款_' . ($filter['search_value'] ?? ""),
        ]);

        return $result;
    }
}