<?php
namespace app\common\model\crebo;
use think\Model;

class Withdraw extends Model
{
    protected $name = "users_withdraws";

    /**
     * 所属用户
     * @collection relation.model
     */
    public function member()
    {
        return $this->hasOne(Users::class, 'id', 'uid')->field('id,username,nickname,mobile,group');
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
        $query = $this->order('withdraw.id desc')->withJoin(["member"], "left");
        if (isset($filter['search_type']) && !empty($filter['search_type']) && isset($filter['search_value']) && !empty($filter['search_value']) ) {
            if (in_array($filter['search_type'], ["trade_no", "out_trade_no"])) {
                $query->where("withdraw.{$filter["search_type"]}", 'LIKE', "%{$filter['search_value']}%");
            } elseif (in_array($filter["search_type"], ["username", "mobile"])) {
                $query->where("member.{$filter["search_type"]}", 'LIKE', "%{$filter['search_value']}%");
            }
        }
        if (isset($filter['status']) && $filter['status'] !== "" && $filter['status'] != "all") {
            $query->where('withdraw.status', '=', (int)$filter['status']);
        }
        $list = [];
        $pay_types = ['alipay' => '支付宝','weixin' => '微信', 'app' => 'APP'];
        $count = $query->count();
        $query->page($page,$limit)->select()
        ->each(function ($item) use ($pay_types, &$list) {
            $row = array_merge($item->member?$item->member->toArray():[], $item->getData());
            $row["create_time"] = date("Y-m-d H:i:s", $row["create_time"]);
            $row["user_id"] = $item->member["id"];
            $row["account_name"] = isset($pay_types[$row["account_name"]])?$pay_types[$row["account_name"]]:$row["account_name"];
            $list[] = $row;
            return $item;
        });
        $sql = $query->getLastSql();

        return compact('count', 'list', 'sql');
    }
}