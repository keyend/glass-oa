<?php
namespace app\common\model\crebo;
use think\Model;
use think\facade\Db;
use app\common\model\system\UserModel;

class Order extends Model
{
    protected $name = "users_orders";

    const ORDER_STATUS_PENDING = 0;
    const ORDER_STATUS_COMPLETED = 1;

    /**
     * 所属用户
     * @collection relation.model
     */
    public function member()
    {
        return $this->hasOne(Users::class, 'id', 'customer_id')->field('id,username,nickname,mobile,group,group_expire,desc');
    }

    /**
     * 录单人员
     *
     * @return void
     */
    public function user()
    {
        return $this->hasOne(UserModel::class, "user_id", "uid");
    }

    /**
     * 订单产品列表
     * @collection relation.model
     */
    public function goods()
    {
        return $this->hasMany(OrderGoods::class, 'order_id', 'id');
    }

    /**
     * 已存在的配送单
     * @collection relation.model
     */
    public function delivery()
    {
        return $this->hasMany(OrderDelivery::class, 'order_id', 'id');
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
        $query = $this->withJoin(["member"], "left");
        if (isset($filter['search_type']) && !empty($filter['search_type']) && isset($filter['search_value']) && !empty($filter['search_value']) ) {
            if (in_array($filter['search_type'], ["trade_no", "out_trade_no"])) {
                $query->where("order.{$filter["search_type"]}", 'LIKE', "%{$filter['search_value']}%");
            } elseif (in_array($filter["search_type"], ["username", "mobile"])) {
                $query->where("member.{$filter["search_type"]}", 'LIKE', "%{$filter['search_value']}%");
            }
        }
        if (isset($filter['status']) && $filter['status'] != "all") {
            if ($filter['status'] != 3) {
                $query->where('order.status', '=', (int)$filter['status'])->where("order.is_trash", 0);
            } else {
                $query->where('order.is_trash', '=', 1);
            }
        }
        $list = [];
        $pay_types = ['alipay' => '支付宝','wxpay' => '微信', 'app' => 'APP'];
        $count = $query->count();
        $query->page($page,$limit)->order('order.id desc')->select()
        ->each(function ($item) use ($pay_types, &$list) {
            $row = array_merge($item->member?$item->member->toArray():[], $item->getData());
            $row["create_time"] = date("Y-m-d H:i:s", $row["create_time"]);
            $row['type'] = $pay_types[$row['type']] ?? '';
            $row["customer_id"] = $item->member["id"];
            $row['pay_money'] = "&yen; " . number_format($row['pay_money'],2);
            $row['discount_money'] = "&yen; " . number_format($row['discount_money'],2);
            $row['order_money'] = "&yen; " . number_format($row['order_money'],2);
            $row["order_num"] = (int)$row['order_num'];
            $row["deduct_num"] = (int)$row['deduct_num'];
            $row["process"] = round($row["deduct_num"] / $row["order_num"], 4) * 100;
            $list[] = $row;
            return $item;
        });
        $sql = $query->getLastSql();

        return compact('count', 'list', 'sql');
    }

    /**
     * 返回订单
     *
     * @param string $trade_no
     * @return void
     */
    public function getPending($trade_no = '') 
    {
        return self::where("trade_no", $trade_no)->where("status", 0)->findOrEmpty();
    }

    /**
     * 设置为支付成功
     *
     * @return void
     */
    public function setComplete()
    {
        if ($this->getAttr("status") == self::ORDER_STATUS_PENDING) {
            $this->setAttr("status", self::ORDER_STATUS_COMPLETED);
            $this->save();
            
            $user = $this->getAttr("member");
            $day = (int)$this->getAttr("vip_day");
            $timestamp = $day * 86400;
            $today = mktime(23,59,59);
            $vipGroup = conf('vip.vip_group');
            $originTimestamp = max($user->group_expire, $today);

            $user->group = $vipGroup;
            $user->group_expire = $originTimestamp + $timestamp;
            $user->save();
        }
    }

    /**
     * 返回后三位
     *
     * @return void
     */
    public function getLastId()
    {
        $timestamp = mktime(0, 0, 0);
        $lastId = (int)$this->where('create_time', '>', $timestamp)->count();
        $lastId = "000" . ($lastId + 1);
        return substr($lastId, strlen($lastId) - 3, 3);
    }

    /**
     * 新境订单
     *
     * @param array $data
     * @return void
     */
    public function addOrder($data = [])
    {
        $trade_no = date( 'Ymd' ) . $this->getLastId();
        $customer_id = (int)$data["customer_id"];
        $customer = Users::where('id', $customer_id)->field("id,nickname,desc,mobile,category,minarea")->find();
        if (empty($customer)) {
            throw new \Exception("客户名称为空或不存在!");
        } elseif (empty($data["goods"])) {
            throw new \Exception("校验订单失败，空订单!");
        }

        $customer["minarea"] = (float)$customer["minarea"];
        $category = $customer->category;
        $goods = [];
        foreach($data["goods"] as $row) {
            $itm = [];
            $itm["width"]           = (float)$row["width"];
            $itm["height"]          = (float)$row["height"];
            $itm["num"]             = (float)$row["num"];
            $itm["manual"]          = (float)$row["manual"];
            $itm["category_id"]     = (float)$row["category_id"];
            $itm["area"]            = round($itm["width"] * $itm["height"] / 10E5, 2);
            $itm["trade_no"]        = $trade_no;
            $itm["create_time"]     = TIMESTAMP;

            if ($itm["area"] < $customer["minarea"]) {
                $itm["area"] = $customer["minarea"];
            }

            $category_data = Category::where("id", $itm["category_id"])->find();
            if (empty($category_data)) {
                throw new \Exception("无对应类目 category_id {$itm['category_id']}");
            // }elseif (!isset($category[$row["category_id"]]) || empty($category[$itm["category_id"]])) {
            //     throw new \Exception("未设置{$customer['nickname']}的{$category_data['category']}价格!");
            }

            $craft_id = (float)$row["craft_id"];
            $craft_data = Craft::where("id", $craft_id)->find();
            if (empty($craft_data)) {
                throw new \Exception("无对应工艺 craft_id {$craft_id}");
            }

            $itm["remark"]          = $row["remark"];
            $itm["craft"]           = $craft_data["craft"];
            $itm["craft_thumb"]     = $craft_data["thumb"];
            $itm["category"]        = $category_data['category'];
            $itm["unitprice"]       = (float)$category[$itm["category_id"]];
            $itm["manual_money"]    = $itm["manual"] * $itm["num"];
            $itm["order_money"]     = $itm["area"] * $itm["num"] * $itm["unitprice"] + $itm["manual_money"];
            $goods[] = $itm;
        }

        $order_money = array_sum(array_values(array_column($goods, "order_money")));
        $order_manual = array_sum(array_values(array_column($goods, "manual_money")));
        $order_num = array_sum(array_values(array_column($goods, "num")));
        $order = [
            "uid" => S1,
            "trade_no" => $trade_no,
            "out_trade_no" => $trade_no,
            "customer_id" => $customer_id,
            "customer" => $customer["nickname"],
            "address" => $customer["desc"],
            "mobile" => $customer["mobile"],
            "order_money" => $order_money,
            "order_num" => $order_num,
            "manual_money" => $order_manual,
            "create_time" => TIMESTAMP
        ];

        $order['order_id'] = $this->insertGetId($order);
        $order_goods_model = app()->make(OrderGoods::class);
        $order_goods_model->insertAll($goods);
        $order_goods_model->where("trade_no", $trade_no)->update(["order_id" => $order['order_id']]);

        return [
            "order_id" => $order['order_id'],
        ];
    }

    /**
     * 创建配送单
     *
     * @param array $data
     * @return void
     */
    public function addDelivery($data = [])
    {
        $delivery_id = OrderDelivery::insertGetId(array_keys_filter($data, [
            'trade_no',
            'order_id',
            'delivery_money',
            'delivery_num',
            'manual_money',
            ['remark', ''],
            ['create_time', TIMESTAMP]
        ]));
        $deductNum = 0;
        foreach($data["goods"] as &$goods) {
            $goods["delivery_id"] = $delivery_id;
            $goodsOrigin = $data["goods_list"][$goods["goods_id"]];
            $goodsChange = [];
            $goodsTotalNum = $goodsOrigin["num"];
            $goodsDeductNum = $goodsOrigin["deductnum"] + $goods["num"];
            if ($goodsDeductNum >= $goodsTotalNum) {
                $goodsChange["status"] = 3;
            } elseif ($goodsOrigin["pay_status"] != 0) {
                $goodsChange["status"] = 2;
            } else {
                $goodsChange["status"] = 1;
            }
            OrderGoods::where("id", $goods["goods_id"])->inc("deductnum", $goods["num"])->update($goodsChange);
            $deductNum += $goods["num"];
        }
        OrderDeliveryGoods::insertAll($data["goods"]);
        $totalNum = (int)$this->getAttr("order_num");
        $originDeductNum = (int)$this->getAttr("deductnum");
        $currentDeductNum = $deductNum + $originDeductNum;
        $status = $currentDeductNum < $totalNum ? 1 : 2;
        $this->setAttr("status", $status);
        $this->setAttr("deduct_num", Db::raw("deduct_num+{$deductNum}"));
        $this->save();
    }
}