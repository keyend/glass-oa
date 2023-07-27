<?php
namespace app\common\model\crebo;
use think\Model;
use think\facade\Db;

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
     * 列表
     *
     * @param int page
     * @param int limit 页码
     * @param array 筛选条件
     * @return array
     */
    public function getList($page, $limit, $filter = [])
    {
        static $excel = null;
        static $columns = null;

        if ($filter['export'] == 1) {
            $excel = new \mashroom\Excel();
            $columns = [
                ["title" => "ID", "field" => "id", "width" => 6],
                ["title" => "客户名称", "field" => "customer", "width" => 24],
                ["title" => "联系电话", "field" => "mobile", "width" => 18],
                ["title" => "地址", "field" => "address", "width" => 48],
                ["title" => "流水号", "field" => "trade_no", "width" => 24, "type" => "numeric"],
                ["title" => "收款金额", "field" => "pay_money", "width" => 12],
                ["title" => "收款时间", "field" => "pay_time", "width" => 24],
                ["title" => "备注", "field" => "remark", "width" => 96]
            ];
        }

        $query = $this->order('order_pay.id desc')->withJoin(["member"], "left");
        if (isset($filter['search_type']) && !empty($filter['search_type']) && isset($filter['search_value']) && !empty($filter['search_value']) ) {
            if (in_array($filter["search_type"], ["username", "mobile"])) {
                $query->where("member.{$filter["search_type"]}", 'LIKE', "%{$filter['search_value']}%");
            } elseif(in_array($filter["search_type"], ["remark"])) {
                $query->where("order_pay.pay_info", 'LIKE', "%{$filter['search_value']}%");
            }
        }
        if (isset($filter['customer_id']) && $filter['customer_id'] !== 0) {
            $query->where('order_pay.customer_id', '=', (int)$filter['customer_id']);
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0]);
                $times[1] = strtotime($times[1]);
                $query->where('order_pay.pay_time', 'BETWEEN', $times);
            }
        }
        $list = [];
        $count = $query->count();
        $query->when(!isset($filter['export']) || $filter['export'] != 1, function($query) use($page, $limit) {
            $query->page($page,$limit);
        })->field("order_pay.*")->chunk(100, function ($lists) use (&$list, $filter, $columns, $excel) {
            if ($filter['export'] == 1) {
                $list = [];
            }

            foreach($lists as $item) {
                $row = $item->toArray();
                $row["pay_time"] = date("Y-m-d H:i:s", $row["pay_time"]);
                $row["mobile"] = $item->member["mobile"];
                $row["address"] = $item->member["address"];
                $row["remark"] = $item->pay_info->remark;
                $list[] = $row;
            }

            if ($filter['export'] == 1) {
                $excel->excel($list, [
                    'title' => '收款账单_' . date("Y_m_d"),
                    'headers' => $columns
                ]);
            }
        }, "order_pay.id");

        $sql = $query->getLastSql();
        if ($filter['export'] == 1) {
            $excel->excel(null, []);
        }

        return compact('count', 'list', 'sql');
    }
}