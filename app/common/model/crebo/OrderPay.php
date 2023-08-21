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
            'title' => '收款明细_' . ($filter['search_value'] ?? "") . "_" . date("Y_m_d"),
        ]);

        return $result;
    }
}