<?php
namespace app\common\model\crebo;
use app\Model;
use think\facade\Db;
use think\facade\Cache;
use app\common\model\system\UserModel;

class Order extends Model
{
    protected $name = "users_orders";

    // 订单状态 已作废
    const STATUS_TRASH = -1;
    // 订单状态 进行中
    const STATUS_PENDING = 0;
    // 订单状态 已完成(已配送)
    const STATUS_COMPLETE = 1;
    // 订单状态 已完成(已收款)
    const STATUS_FINISHED = 1;
    // 配送状态 待配送
    const DELIVERY_READY = 0;
    // 配送状态 待配送
    const DELIVERY_PEDING = 1;
    // 配送状态 已完成
    const DELIVERY_COMPLETE = 2;
    // 收款状态 待收款
    const REWARD_READY = 0;
    // 收款状态 收款中
    const REWARD_PENDING = 1;
    // 收款状态 已收款
    const REWARD_COMPLETE = 2;

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
        return $this->hasMany(OrderGoods::class, 'order_id', 'id')->where("is_delete", 0)->order("id ASC");
    }

    /**
     * 已存在的配送单
     * @collection relation.model
     */
    public function delivery()
    {
        return $this->hasMany(OrderDelivery::class, 'order_id', 'id')->order("id DESC");
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
        if (isset($filter['search_value']) && !empty($filter['search_value']) ) {
            $filter['search_value'] = trim($filter['search_value']);
            $query->where("order.trade_no|order.out_trade_no|member.nickname|member.mobile|member.desc", 'LIKE', "%{$filter['search_value']}%");
        }
        if (isset($filter['search_time']) && !empty($filter['search_time'])) {
            $times = explode(" - ", $filter['search_time']);
            if (count($times) === 2) {
                $times[0] = strtotime($times[0] . " 00:00:00");
                $times[1] = strtotime($times[1] . " 23:59:59");
                $query->where('order.create_time', 'BETWEEN', $times);
            }
        }
        if (isset($filter['status']) && $filter['status'] != "all") {
            if ($filter['status'] === 3) {
                $query->where('order.is_trash', '=', 1);
            } elseif($filter['status'] === 4) {
                $query->where('order.pay_status', '=', 0);
            } else {
                $query->where('order.status', '=', (int)$filter['status'])->where("order.is_trash", 0);
            }
        }
        $result = $this->maps(function($query, $page, $limit) use($filter) {
            $cursor = $query->order("order.id DESC")->cursor();
            $sql = $query->getLastSql();
            $list = [];
            $pay_status = ["待收款", "收款中", "已收款"];
            $delivery_status = ["待配送", "配送中", "已完成"];
            $order_status = ["进行中", "已配送", "已完成"];
            foreach($cursor as $row) {
                $list[] = $this->mapsItem(function($row, $item) use($filter, $pay_status, $delivery_status, $order_status) {
                    $member = $item->member?$item->member->toArray():[];
                    $row = array_merge($member, $row);
                    $row['type'] = $pay_types[$row['type']] ?? '';
                    $row["customer_id"] = $item->member["id"];
                    $row['pay_money'] = number_format($row['pay_money'],2);
                    $row['discount_money'] = number_format($row['discount_money'],2);
                    $row['order_money'] = number_format($row['order_money'],2);
                    $row["order_num"] = (int)$row['order_num'];
                    $row["deduct_num"] = (int)$row['deduct_num'];
                    $row["process"] = round($row["deduct_num"] / $row["order_num"], 4) * 100;
                    if ($filter["export"]) {
                        $row["pay_status"] = $pay_status[$row["pay_status"]];
                        $row["delivery_status"] = $delivery_status[$row["delivery_status"]];
                        $row["status"] = $row["status"] == -1 ? "已作废" : $order_status[$row["status"]];
                    }
                    return $row;
                }, $row);
            }
            return compact('list', 'sql');
        }, [
            "query"  => $query,
            "filter" => $filter,
            "page"   => $page,
            "limit"  => $limit,
            'headers' => [
                ["title" => "订单号", "field" => "trade_no", "width" => 18],
                ["title" => "客户名", "field" => "customer", "width" => 24],
                ["title" => "手机号", "field" => "mobile", "width" => 18],
                ["title" => "订单状态", "field" => "status", "width" => 12],
                ["title" => "配送状态", "field" => "delivery_status", "width" => 12],
                ["title" => "收款状态", "field" => "pay_status", "width" => 12],
                ["title" => "订单金额", "field" => "order_money", "width" => 12, "sum" => 1],
                ["title" => "已收金额", "field" => "pay_money", "width" => 12, "sum" => 1],
                ["title" => "优惠金额", "field" => "discount_money", "width" => 12, "sum" => 1],
                ["title" => "下单时间", "field" => "create_time", "width" => 18]
            ],
            'title'  => '订单记录_' . ($filter['search_value'] ?? "") . "_" . date("Y_m_d")
        ]);

        return $result;
    }

    /**
     * 返回订单
     *
     * @param string $trade_no
     * @return void
     */
    public function getOrder($trade_no = '') 
    {
        return self::where("trade_no", $trade_no)->findOrEmpty();
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
            $itm["manual_cals"]     = json_encode($row['manual_cals'], JSON_UNESCAPED_UNICODE);
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
        $order["goods"] = $goods;
        $this->logger('logs.order.add', 'CREATEED', $order);
        return [
            "order_id" => $order['order_id'],
        ];
    }

    /**
     * 编辑订单
     *
     * @param array $data
     * @return void
     */
    public function editOrder($data = [])
    {
        $customer_id = (int)$this->getAttr("customer_id");
        $customer = Users::where('id', $customer_id)->field("id,nickname,desc,mobile,category,minarea")->find();
        if (empty($customer)) {
            throw new \Exception("客户名称为空或不存在!");
        }
        $customer["minarea"] = (float)$customer["minarea"];
        $order_id = $this->getAttr("id");
        $original = $this->getData();
        $category = $customer->category;
        $addGoods = [];
        foreach($data["goods"] as $goods) {
            if (isset($goods['id'])) {
                $updateGoods[$goods['id']] = $goods;
            } else {
                $itm = [];
                $itm["type"]            = 1;//附加订单
                $itm["order_id"]        = $order_id;
                $itm["trade_no"]        = $this->getAttr("trade_no");
                $itm["width"]           = (float)$goods["width"];
                $itm["height"]          = (float)$goods["height"];
                $itm["num"]             = (int)$goods["num"];
                $itm["manual"]          = (float)$goods["manual"];
                $itm["category_id"]     = (int)$goods["category_id"];
                $itm["manual_cals"]     = $goods["manual_cals"];
                $itm["area"]            = round($itm["width"] * $itm["height"] / 10E5, 2);
                $itm["create_time"]     = TIMESTAMP;
                if ($itm["category_id"] === 0) {
                    continue;
                }

                $category_data = Category::where("id", $itm["category_id"])->field("category")->find();
                if (empty($category_data)) {
                    throw new \Exception("无对应类目 category_id {$itm['category_id']}");
                }

                $craft_id               = (float)$goods["craft_id"];
                $craft_data             = Craft::where("id", $craft_id)->field("craft,thumb")->find();
                if (empty($craft_data)) {
                    throw new \Exception("无对应工艺 craft_id {$craft_id}");
                }

                if ($itm["area"] < $customer["minarea"]) {
                    $itm["area"] = $customer["minarea"];
                }

                $itm["remark"]          = $goods["remark"];
                $itm["craft"]           = $craft_data["craft"];
                $itm["craft_thumb"]     = $craft_data["thumb"];
                $itm["category"]        = $category_data['category'];
                $itm["unitprice"]       = (float)$category[$itm["category_id"]];
                $itm["manual_money"]    = $itm["manual"] * $itm["num"];
                $itm["order_money"]     = $itm["area"] * $itm["num"] * $itm["unitprice"] + $itm["manual_money"];
                $addGoods[] = $itm;
            }
        }
        $this->startTrans();
        foreach($this->getAttr('goods') as $goods) {
            if (!isset($updateGoods[$goods["id"]])) {
                $this->logger('logs.order.edit.delgoods', 'DELETE', $goods);
                $goods->is_delete = 1;
                $goods->save();
            } else {
                $goods["unitprice"] = (float)$goods["unitprice"];
                $itm                = array_keys_filter($updateGoods[$goods['id']], [
                    'width',
                    'height',
                    'manual',
                    'manual_cals',
                    'num',
                    'remark',
                    'unitprice'
                ]);
                $itm["num"]         = (int)$itm["num"];
                $itm["width"]       = (float)$itm["width"];
                $itm["height"]      = (float)$itm["height"];
                $itm["manual"]      = (float)$itm["manual"];
                $itm["area"]        = round($itm["width"] * $itm["height"] / 10E5, 2);
                $itm["manual_money"] = $itm["manual"] * $itm["num"];
                $itm["order_money"] = $itm["area"] * $itm["num"] * $itm["unitprice"] + $itm["manual_money"];
                $itm["manual_cals"] = json_encode($itm['manual_cals'], JSON_UNESCAPED_UNICODE);
                $originGoods = $goods->toArray();
                $goods->update($itm, ["id" => $goods["id"]]);
                $this->logger('logs.order.edit.updategoods', 'DELETE', [$originGoods, $goods->toArray()]);
            }
        }
        $deliverys = $this->getAttr("delivery");
        if (!empty($deliverys)) {
            foreach($deliverys as $delivery) {
                if ($delivery->status == 0) {
                    $deliveryGoods = $delivery->goods;
                    if (!empty($deliveryGoods)) {
                        foreach($deliveryGoods as $goods) {
                            if (isset($updateGoods[$goods["goods_id"]])) {
                                $itm            = array_keys_filter($updateGoods[$goods['goods_id']], [
                                    'width',
                                    'height',
                                    'manual',
                                    'remark',
                                    'unitprice'
                                ]);
                                $itm["width"]   = (float)$itm["width"];
                                $itm["height"]  = (float)$itm["height"];
                                $itm["manual"]  = (float)$itm["manual"];
                                $itm["area"]    = round($itm["width"] * $itm["height"] / 10E5, 2);
                                $itm["manual_money"] = $itm["manual"] * $goods["num"];
                                $itm["order_money"] = $itm["area"] * $goods["num"] * $itm["unitprice"] + $itm["manual_money"];
                                $originGoods = $goods->toArray();
                                $goods->update($itm, ["id" => $goods["id"]]);
                                $this->logger('logs.order.edit.updatedeliverygoods', 'DELETE', [$originGoods, $goods->toArray()]);
                            }
                        }
                    }
                }
            }
        }
        $order_goods_model = app()->make(OrderGoods::class);
        if(!empty($addGoods)) {
            $order_goods_model->insertAll($addGoods);
            $this->logger('logs.order.append', 'CREATEED', $goods);
        }
        $goods = $order_goods_model->where("order_id", $this->getAttr("id"))->where("is_delete", 0)->field("order_money,manual_money,num")->select()->toArray();
        if (empty($goods))
            $goods = [];
        $this->order_money = array_sum(array_values(array_column($goods, "order_money")));
        $this->manual_money = array_sum(array_values(array_column($goods, "manual_money")));
        $this->order_num = array_sum(array_values(array_column($goods, "num")));
        $this->update_time = TIMESTAMP;
        $this->save();
        $this->commit();
        $this->logger('logs.order.edit', 'UPDATED', [$original, $this->getData()]);
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
        $order_goods_model = new OrderGoods();
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
            $order_goods_model->where("id", $goods["goods_id"])->inc("deductnum", $goods["num"])->update($goodsChange);
            $deductNum += $goods["num"];
        }
        OrderDeliveryGoods::insertAll($data["goods"]);
        $this->setAttr("deduct_num", Db::raw("deduct_num+{$deductNum}"));
        $this->save();
        event("OrderChange", $this->getAttr("id"));
        $this->logger('logs.order.delivery.add', 'CREATEED', $data);
        return $delivery_id;
    }

    /**
     * 返回订单数据
     *
     * @return void
     */
    public function getBetweenData($times= [])
    {
        $count = (int)self::where("create_time", "BETWEEN", $times)->count();
        $money = (float)self::where("create_time", "BETWEEN", $times)->sum("order_money");
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
        $cache = Cache::get("shape_order_{$version}");
        if (!empty($cache)) {
            return $cache;
        }

        $maps = ["orderNum" => "订单数量", "orderMoney" => "订单金额"];
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
        Cache::set("shape_order_{$version}", $result);
        return $result;
    }

    /**
     * 获取订单数
     *
     * @param integer $timestamp
     * @return void
     */
    private function getChartDataOrderNum($timestamp) 
    {
        return self::where("is_trash", "=", 0)->where("create_time", "BETWEEN", [$timestamp, $timestamp + 86399])->sum("order_num");
    }

    /**
     * 获取订单金额
     *
     * @param integer $timestamp
     * @return void
     */
    private function getChartDataOrderMoney($timestamp) 
    {
        return self::where("is_trash", "=", 0)->where("create_time", "BETWEEN", [$timestamp, $timestamp + 86399])->sum("order_money");
    }

    /**
     * 补单
     *
     * @param integer $goodsid
     * @param integer $num
     * @param string $remark
     * @return void
     */
    public function smentary($goodsid, $num, $remark = '')
    {
        $model = app()->make(OrderGoods::class);
        $goods = $model->where("id", $goodsid)->find();
        $parent_id = $goodsid;
        if ($goods["type"] == 0) {
            $type = 2;
        } elseif($goods["type"] == 1) {
            $parent_id = $goods["parent_id"];
            $type = 3;
        } else {
            $type = $goods["type"];
        }
        $cloneGoods = $goods->toArray();
        $cloneGoods["parent_id"] = $goodsid;
        $cloneGoods["type"] = $type;
        $cloneGoods["num"] = $num;
        $cloneGoods["remark"] = $remark;
        $cloneGoods["create_time"] = TIMESTAMP;
        unset($cloneGoods["id"]);
        $model->insert($cloneGoods);
        $this->setAttr("supplement_num", \think\facade\Db::raw("supplement_num + {$num}"));
        $this->save();
    }

    /**
     * 订单支付
     *
     * @param array $data
     * @return void
     */
    public function pay($data = [])
    {
        $deliveryStatus = (int)$this->getAttr("delivery_status");
        $payMoney       = (float)$data["money"];
        $currentMoney   = (float)$this->getAttr("pay_money");
        $orderMoney     = (float)$this->getAttr("order_money");
        $afterMoney     = $payMoney + $currentMoney;
        $payStatus      = self::REWARD_PENDING;
        $manualStatus   = $data["status"] ?? "";
        if ($afterMoney > $orderMoney) {
            throw new \Exception("金额过大!");
        } elseif($payMoney <= 1) {
            throw new \Exception("金额过小");
        }
        if ($manualStatus !== "") {
            $payStatus = self::REWARD_COMPLETE;
        } elseif($afterMoney == $orderMoney) {
            $payStatus = self::REWARD_COMPLETE;
        }
        $this->setAttr("pay_money", \think\facade\Db::raw("pay_money+{$payMoney}"));
        $this->setAttr("pay_time", TIMESTAMP);
        $this->setAttr("pay_status", $payStatus);
        $pay_model = new OrderPay();
        $payinfo = [ "remark" => $data["remark"]??"" ];
        $pay = [
            "uid"           => S1,
            "customer_id"   => $this->getAttr("customer_id"),
            "customer"      => $this->getAttr("customer"),
            "trade_no"      => $this->getAttr("trade_no"),
            "out_trade_no"  => $this->getAttr("out_trade_no"),
            "pay_type"      => "OFFLINE",
            "pay_money"     => $payMoney,
            "pay_info"      => serialize($payinfo),
            "pay_status"    => 1,
            "pay_time"      => TIMESTAMP
        ];
        $pay["id"] = $pay_model->insertGetId($pay);
        $this->save();
        event("OrderChange", $this->getAttr('id'));
        return $pay;
    }
}