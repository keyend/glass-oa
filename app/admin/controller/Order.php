<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\system\ConfigModel;
use app\common\model\crebo\Order as OrderModel;
use app\common\model\crebo\OrderGoods;
use app\common\model\crebo\OrderDelivery;
use app\common\model\crebo\OrderDeliveryGoods;
use app\common\model\crebo\Category;
use app\common\model\crebo\Craft;
use app\common\model\crebo\OrderPay;
use app\common\model\crebo\Users;
use think\facade\Db;

/**
 * 订单管理
 * @package admin.controller.order
 * @version 1.0.0
 */
class Order extends Controller
{
    /**
     * 返回订单列表
     *
     * @param integer $id
     * @param OrderModel $order_model
     * @return void
     */
    public function getList($id, OrderModel $order_model)
    {
        if ($this->request->isAjax() || $this->request->isPost()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""],
                ["search_time", ""],
                ["export", 0]
            ]);
            if ($id != 'all') {
                $filter["status"] = (int)$id;
            }
            [$page, $limit] = $this->getPaginator();
            $data = $order_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $this->assign("status_id", $id);
            return $this->fetch('Order/index');
        }
    }

    /**
     * 订单首页
     *
     * @param OrderModel $order_model
     * @return void
     */
    public function index(OrderModel $order_model)
    {
        $this->assign("status_id", 'all');
        return $this->fetch();
    }

    /**
     * 删除订单
     *
     * @param OrderModel $order_model
     * @return void
     */
    public function delete(OrderModel $order_model)
    {
        if (IS_AJAX) {
            $ids = $this->request->post('id');
            if (!is_array($ids)) {
                $ids = [ intval($ids) ];
            }

            $orders = $order_model->with(['goods','delivery', 'delivery.goods'])->where([["id", "IN", $ids]])->select();
            if (!empty($orders)) {
                foreach($orders as $order) {
                    if ($order->delivery) {
                        foreach($order->delivery as $delivery) {
                            if ($delivery->goods) {
                                foreach($delivery->goods as $goods) {
                                    $this->logger('logs.order.delete.delivery.goods', 'DELETE', $goods);
                                    $goods->delete();
                                }
                                $this->logger('logs.order.delete.delivery', 'DELETE', $delivery);
                                $delivery->delete();
                            }
                        }
                    }
                    if ($order->goods) {
                        foreach($order->goods as $goods) {
                            $this->logger('logs.order.delete.goods', 'DELETE', $goods);
                            $goods->delete();
                        }
                    }
                    $this->logger('logs.order.delete', 'DELETE', $order);
                    $order->delete();
                }
            }
        }
        return $this->success();
    }

    /**
     * 订单作废
     *
     * @param OrderModel $order_model
     * @return void
     */
    public function trash(OrderModel $order_model)
    {
        if (IS_AJAX) {
            $ids = $this->request->post('id');
            if (!is_array($ids)) {
                $ids = [ intval($ids) ];
            }

            $orders = $order_model->where([["id", "IN", $ids]])->select();
            if (!empty($orders)) {
                foreach($orders as $order) {
                    if (in_array($order->status, [0, 1])) {
                        $order->is_trash = 1;
                        $order->status = -1;
                        $order->save();
                        $this->logger('logs.order.trash', 'UPDATED', $order);
                    }
                }
            }
        }
        return $this->success();
    }

    /**
     * 订单修改
     *
     * @param OrderModel $order_model
     * @return void
     */
    public function update(OrderModel $order_model)
    {
        $orderid = (int)input("id", 0);
        $order = $order_model->with(['member'])->find($orderid);
        if (empty($order)) {
            return $this->fail("订单不存在");
        }
        $field = input("field", "");
        $value = input("value", "");
        $polymerization = input("polymerization", 0);
        if ($field == 'mobile') {
            if (empty($order->member->mobile)) {
                $order->member->mobile = $value;
                $order->member->save();
                $this->logger('logs.order.change_mobile', 'UPDATED', $order->member);
            }
            if (empty($order->mobile)) {
                $order->mobile = $value;
                $order->save();
                $this->logger('logs.order.change_mobile', 'UPDATED', $order);
            }
            if ($polymerization == 1) {
                $order_model->where([["customer_id", "=", $order->customer_id]])->update(["mobile" => $value]);
            }
        }
        return $this->success();
    }

    /**
     * 订单明细
     *
     * @param OrderModel $order_model
     * @return void
     */
    public function detail(OrderModel $order_model)
    {
        $orderid = input("order_id", 0);
        $order = $order_model->with(['goods', 'user', 'delivery', 'delivery.goods'])->find($orderid);
        if (empty($order)) {
            return $this->fail("订单记录不存在!");
        }

        if ($this->request->isPost()) {
            $data = input("post.");
            try {
                if (!isset($data["filt"])) {
                    throw new \Exception("INVALID_PARAM");
                } elseif(!checkAccess('order' . ucfirst($data["filt"]))) {
                    throw new \Exception("NO_ACCESS");
                }
                $data["num"] = (int)$data["num"];
                call_user_func_array([$order, $data["filt"]], [$data["goods"]["id"], $data["num"], $data["remark"]]);
                $this->logger("logs.order.change_{$data['filt']}", 'UPDATED', $order);
            } catch(\Exception $e) {
                return $this->fail($e->getMessage());
            }
            return $this->success();
        } else {
            $delivery_goods_model = new OrderDeliveryGoods();
            foreach($order['goods'] as &$goods) {
                $goods["recived"] = $delivery_goods_model->getReceivedGoods($goods["parent_id"]?$goods['parent_id']:$goods['id']);
                $goods['finished'] = $goods["num"] <= $goods["recived"];
            }
            unset($goods);
            $this->assign("order", $order);
            return $this->fetch();
        }
    }

    /**
     * 新增订单
     *
     * @param OrderModel $order_model
     * @return void
     */
    public function add(OrderModel $order_model, Users $user_model, Category $category_model, Craft $craft_model)
    {
        if($this->request->isPost()) {
            $data = input("post.");
            try {
                $res = $order_model->addOrder($data);
                return $this->success($res);
            } catch (\Exception $e) {
                return $this->fail($e->getMessage());
            }
		} else {
            $customers_result = $user_model->getList(1, 9999, ["status" => 1]);
            $customers = [];
            $customer_id = 0;
            $customer_minarea = 0;
            $customer_category = "[]";
            $category = [];
            foreach($customers_result["list"] as $row) {
                $item = $row->toArray();
                $item["category"] = json_encode($row->category, JSON_UNESCAPED_SLASHES);
                $customers[] = $item;
                // if ($customer_id === 0) {
                //     $customer_id = $row["id"];
                //     $customer = $row["nickname"];
                //     $customer_minarea = $row["minarea"];
                //     $customer_category = $item["category"];
                // }
            }

            $categorys_result = $category_model->getList(1, 9999);
            $categorys = $categorys_result["list"];
            $category_id = 0;
            if (!empty($categorys)) {
                $category_id = $categorys[0]["id"];
            }

            $crafts_result = $craft_model->getList(1, 9999);
            $crafts = $crafts_result["list"];
            $craft_id = 0;
            if (!empty($crafts)) {
                $craft_id = $crafts[0]["id"];
            }

            $order = [];
            $data = [];
            $order_id = (int)input("order_id", 0);
            if ($order_id !== 0) {
                $order = $order_model->with(['goods'])->find($order_id);
                if (!empty($order)) {
                    foreach($customers_result["list"] as $row) {
                        if ($row["id"] == $order["customer_id"]) {
                            $item["category"] = json_encode($row->category, JSON_UNESCAPED_SLASHES);
                            $customer_id = $row["id"];
                            $customer = $row["nickname"];
                            $customer_minarea = $row["minarea"];
                            $customer_category = $item["category"];
                        }
                    }

                    $customer_id = $order["customer_id"];
                    $crafts = $crafts->toArray();
                    $crafts_maps = array_column($crafts, "id", "craft");
                    $order = $order->toArray();
                    $data = [];
                    foreach($order['goods'] as &$goods) {
                        $goods["width"] = (int)$goods["width"];
                        $goods["height"] = (int)$goods["height"];
                        $goods["order_money"] = (float)$goods["order_money"];
                        $goods["deductnum"] = (int)$goods["deductnum"];
                        $data[$goods['id']] = [
                            "id"            => $goods['id'],
                            "width"         => $goods["width"],
                            "height"        => $goods["height"],
                            "num"           => $goods["num"],
                            "manual"        => $goods["manual"],
                            "unitprice"     => $goods["unitprice"],
                            "category_id"   => $goods["category_id"],
                            "craft_id"      => isset($crafts_maps[$goods["craft"]]) ? $crafts_maps[$goods["craft"]] : 0,
                            "status"        => $goods["status"],
                            "remark"        => $goods["remark"],
                            "price"         => $goods["order_money"]
                        ];
                    }
                    unset($order["id"]);
                }
            }

            $this->assign("data",       json_encode($data, JSON_UNESCAPED_UNICODE));
            $this->assign("order",      $order);
            $this->assign("customers",  $customers);
            $this->assign("categorys",  $categorys);
            $this->assign("crafts",     $crafts);
            $this->assign("customer",   $customer);
            $this->assign("customer_id",$customer_id);
            $this->assign("category_id",$category_id);
            $this->assign("customer_minarea", $customer_minarea);
            $this->assign("customer_category",$customer_category);
            $this->assign("craft_id",   $craft_id);
            return $this->fetch('Order/form');
		}
    }

    /**
     * 编辑订单
     *
     * @param OrderModel $order_model
     * @param Users $user_model
     * @param Category $category_model
     * @param Craft $craft_model
     * @return void
     */
    public function edit(OrderModel $order_model, Users $user_model, Category $category_model, Craft $craft_model)
    {
        $order_id = (int)input("order_id", 0);
        $order = $order_model->with(['goods', 'delivery', 'delivery.goods'])->find($order_id);
        if (empty($order)) {
            $this->fail("订单记录不存在!");
        }

        if($this->request->isPost()) {
            $data = input("post.");
            Db::startTrans();
            try {
                $res = $order->editOrder($data);
                Db::commit();
                return $this->success($res);
            } catch (\Exception $e) {
                Db::rollback();
                return $this->fail($e->getMessage());
            }
        } else {
            $customers_result = $user_model->getList(1, 9999, ["status" => 1]);
            $customers = [];
            $customer_id = $order["customer_id"];
            $customer_minarea = 0;
            $customer_category = [];
            $customer_catelog = [];
            $category = [];
            foreach($customers_result["list"] as $row) {
                $item = $row->toArray();
                $item["category"] = json_encode($row->category, JSON_UNESCAPED_SLASHES);
                $customers[] = $item;
                if ($customer_id === $row["id"]) {
                    $customer = $row["nickname"];
                    $customer_minarea = $row["minarea"];
                    $customer_category = $item["category"];
                    $customer_catelog = $row->category;
                }
            }

            $categorys_result = $category_model->getList(1, 9999);
            $categorys = $categorys_result["list"];
            $category_id = 0;
            if (!empty($categorys)) {
                $category_id = $categorys[0]["id"];
            }

            $crafts_result = $craft_model->getList(1, 9999);
            $crafts = $crafts_result["list"];
            $craft_id = 0;
            if (!empty($crafts)) {
                $craft_id = $crafts[0]["id"];
            }

            $crafts_maps = array_column($crafts, "craft_id", "craft");
            $delivery_goods_model = new OrderDeliveryGoods();
            $order = $order->toArray();
            $data = [];
            foreach($order['goods'] as &$goods) {
                if (isset($customer_catelog[$goods["category_id"]])) {
                    $goods["unitprice"] = (float)$customer_catelog[(string)$goods["category_id"]];
                    $goods["order_money"] = $goods["area"] * $goods["unitprice"] * $goods["num"] + $goods["manual"] * $goods["num"];
                }
                $goods["width"] = (int)$goods["width"];
                $goods["height"] = (int)$goods["height"];
                $goods["order_money"] = (float)$goods["order_money"];
                $goods["deductnum"] = (int)$goods["deductnum"];
                $goods["recived"] = $delivery_goods_model->getReceivedGoods($goods["id"]);
                $goods['finished'] = $goods["num"] <= $goods["recived"];
                $data[$goods['id']] = [
                    "id"            => $goods['id'],
                    "width"         => $goods["width"],
                    "height"        => $goods["height"],
                    "num"           => $goods["num"],
                    "manual"        => $goods["manual"],
                    "unitprice"     => $goods["unitprice"],
                    "category_id"   => $goods["category_id"],
                    "craft_id"      => isset($crafts_maps[$goods["craft"]]) ? $crafts_maps[$goods["craft"]] : 0,
                    "status"        => $goods["status"],
                    "remark"        => $goods["remark"],
                    "price"         => $goods["order_money"]
                ];
            }

            $this->assign("data",       json_encode($data, JSON_UNESCAPED_UNICODE));
            $this->assign("order",      $order);
            $this->assign("customers",  $customers);
            $this->assign("categorys",  $categorys);
            $this->assign("crafts",     $crafts);
            $this->assign("customer",   $customer);
            $this->assign("customer_id",$customer_id);
            $this->assign("category_id",$category_id);
            $this->assign("customer_minarea", $customer_minarea);
            $this->assign("customer_category",$customer_category);
            $this->assign("craft_id",   $craft_id);
            return $this->fetch('Order/form');
        }
    }

    /**
     * 订单汇总
     *
     * @param OrderDeliveryGoods $order_delivery_model
     * @return void
     */
    public function converge(OrderDeliveryGoods $order_delivery_model, ConfigModel $config_model, Users $user_model)
    {
        if($this->request->isAjax() || $this->request->isPost()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""],
                ['search_time', ""],
                ['keyword', ''],
                ['export', 0],
                ['print', 0]
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $order_delivery_model->getConverge($page, $limit, $filter);
            if ($filter["print"] == 1) {
                $options = $this->getOptions($config_model, "basic");
                $this->assign('option', $options);
                $this->assign('data', $data);
                return $this->fetch('Order/converge_print');
            } else {
                return $this->success($data);
            }
        } else {
            $customers = $user_model->where("status", 1)->field("id,nickname")->select();
            $this->assign("customers", $customers);
            return $this->fetch('Order/converge');
        }
    }

    /**
     * 配送明细
     *
     * @param OrderModel $order_model
     * @return void
     */
    public function delivery(OrderModel $order_model, ConfigModel $config_model)
    {
        $orderid = input("order_id", 0);
        $order = $order_model->with(['goods', 'user', 'delivery', 'delivery.goods'])->find($orderid);
        if (empty($order)) {
            return $this->fail("订单记录不存在!");
        }
        $order_info = $order->toArray();
        $order_info["deductnum"] = array_sum(array_values(array_column($order_info['goods'], "deductnum")));
        $goods_list[] = [];
        foreach($order_info['goods'] as &$goods) {
            $goods["num"] = (int)$goods["num"];
            $goods["deductnum"] = (int)$goods["deductnum"];
            $goods["max"] = $goods["num"] - $goods["deductnum"];
            $goods_list[$goods["id"]] = $goods;
        }

        if($this->request->isPost()) {
            $data = input("post.");
            if (!isset($data["num"]) || !is_array($data["num"]) || empty($data["num"])) {
                return $this->fail("INVALID_PARAM");
            }

            $delivery_length = count($order_info["delivery"]) + 1;
            $delivery = [
                "order_id" => $order["id"],
                "trade_no" => $order["trade_no"] . "_" . $delivery_length,
                "remark" => $data["remark"],
                "goods_list" => $goods_list,
                "goods" => []
            ];
            $delivery_money_total = 0;
            $delivery_num_total = 0;
            $manual_money_total = 0;

            foreach($data["num"] as $goods_id => $num) {
                if (!isset($goods_list[$goods_id]) || $num > $goods_list[$goods_id]["max"]) {
                    return $this->fail("订单产品提交参数不正确！");
                }
                $goods = $goods_list[$goods_id];
                $goods["unitprice"] = (float)$goods["unitprice"];
                $delivery_money = $goods["area"] * $goods["unitprice"] * $num;
                $manual_money = $goods["manual"] * $num;
                $delivery["goods"][] = [
                    "goods_id"  => $goods["id"],
                    "order_id"  => $goods["order_id"],
                    "category"  => $goods["category"],
                    "craft"     => $goods["craft"],
                    "craft_thumb" => $goods["craft_thumb"],
                    "width"     => $goods["width"],
                    "height"    => $goods["height"],
                    "area"      => $goods["area"],
                    "unitprice" => $goods["unitprice"],
                    "manual"    => $goods["manual"],
                    "num"       => $num,
                    "delivery_money" => $delivery_money,
                    "manual_money" => $manual_money
                ];
                $delivery_money_total += $delivery_money;
                $delivery_num_total   += $num;
                $manual_money_total   += $manual_money;
            }
            $delivery["delivery_num"] = $delivery_num_total;
            $delivery["delivery_money"] = $delivery_money_total;
            $delivery["manual_money"] = $manual_money_total;

            try {
                $delivery_id = $order->addDelivery($delivery);
                $delivery = app()->make(OrderDelivery::class)->with(['order', 'goods'])->find($delivery_id)->toArray();
                return $this->success($delivery);
            } catch (\Exception $e) {
                return $this->fail($e->getMessage());
            }
		} else {
            $this->assign("order", $order_info);
            $options = $this->getOptions($config_model, "basic");
            $options["order_printrm"] = str_replace(PHP_EOL, "\\n", $options["order_printrm"]);
            $this->assign('option', $options);
            return $this->fetch('Order/delivery');
        }
    }

    /**
     * 配送单列表
     *
     * @param OrderDelivery $order_delivery_model
     * @return void
     */
    public function deliveryList(OrderDelivery $order_delivery_model)
    {
        $is_trash = input("is_trash", 0);

        if ($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""],
                ["search_time", ""],
                ["export", 0]
            ]);
            $filter["is_trash"] = (int)$is_trash;
            [$page, $limit] = $this->getPaginator();
            $data = $order_delivery_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $this->assign("is_trash", $is_trash);
            return $this->fetch('Order/delivery_list');
        }
    }

    /**
     * 配送单打印列表
     *
     * @param OrderDelivery $order_delivery_model
     * @return void
     */
    public function deliveryPrint(OrderDelivery $model, Users $user_model)
    {
        if($this->request->isAjax() || $this->request->isPost()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""],
                ['search_time', ""],
                ['is_trash', 0],
                ['export', 0],
                ['print', 0]
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $model->getPrintList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $is_trash = input("is_trash", 0);
            $customers = $user_model->where("status", 1)->field("id,nickname")->select();
            $this->assign("customers", $customers);
            return $this->fetch('Order/delivery_print');
        }
    }

    /**
     * 配送状态变更
     *
     * @param OrderDelivery $model
     * @return void
     */
    public function deliveryUpdate(OrderModel $model)
    {
        $ids = input("ids", "");
        $status = (int)input("status", 0);
        if (!is_array($ids)) {
            $ids = explode(",", $ids);
        }
        $orders = $model->with(['delivery'])->where("id", "IN", $ids)->select();
        foreach($orders as $order) {
            /**
             * 1.全部配送完成，则配送状态自动变更
             * 2.配送单未全部完成，则不会自动变更
             */
            foreach($order->delivery as $delivery) {
                $delivery->status = 1;
                $delivery->save();
                $this->logger('logs.delivery.status', 'UPDATED', $delivery);
                event("OrderDeliveryChange", $delivery['order_id']);
            }
            // $order->delivery_status = $status;
            // $order->save();
            // $this->logger('logs.order.delivery_status', 'UPDATED', $order);
        }
        return $this->success();
    }

    /**
     * 配送状态变更
     *
     * @param OrderDelivery $order_delivery_model
     * @return void
     */
    public function deliveryReceive(OrderDelivery $order_delivery_model)
    {
        $ids = input("ids", "");
        $status = (int)input("status", 0);
        if (!is_array($ids)) {
            $ids = explode(",", $ids);
        }
        $deliverys = $order_delivery_model->where("id", "IN", $ids)->select();
        foreach($deliverys as $delivery) {
            $delivery->status = $status;
            $delivery->save();
            $this->logger('logs.delivery.status', 'UPDATED', $delivery);
            event("OrderDeliveryChange", $delivery['order_id']);
        }
        return $this->success();
    }

    /**delivery
     * 返回参数列表
     *
     * @param ConfigModel $config_model
     * @param string $name
     * @return void
     */
    private function getOptions($config_model, $name = '')
    {
        $settings = $config_model->where('parent', $name)->select();
        $options = [];
        foreach ($settings as $item){
            $options[$item['name']] = $item['value'];
        }
        return $options;
    }

    /**
     * 打印
     *
     * @param OrderDelivery $order_delivery_model
     * @return void
     */
    public function print(OrderDelivery $order_delivery_model, ConfigModel $config_model, OrderGoods $order_goods)
    {
        $id = input("id", 0);
        $delivery = $order_delivery_model->with(['goods', 'order', 'order.member'])->find($id);
        if (empty($delivery)) {
            return $this->fail("配送记录不存在!");
        }
        foreach ($delivery['goods'] as &$goods) {
            $goods["remark"] = $order_goods->where("id", $goods["goods_id"])->value("remark");
        }
        $options = $this->getOptions($config_model, "basic");
        $this->assign("manual", input("manual", 0));
        $this->assign("delivery", $delivery);
        $this->assign("delivery_string", json_encode($delivery, JSON_UNESCAPED_UNICODE));
        $this->assign('option', $options);
        return $this->fetch('Order/printer');
    }

    /**
     * 标签列表
     *
     * @param OrderGoods $order_goods
     * @return void
     */
    public function label(OrderGoods $order_goods)
    {
        if($this->request->isAjax() || $this->request->isPost()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""],
                ['search_time', ''],
                ['export', 0],
                ['print', 0]
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $order_goods->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            return $this->fetch('Order/label');
        }
    }

    /**
     * 打印次数记录
     *
     * @param OrderGoods $order_goods
     * @return void
     */
    public function printRecord(OrderGoods $order_goods)
    {
        $ids = input("post.ids", "");
        if (is_string($ids)) {
            $ids = explode(",", $ids);
        }
        $goodsList = $order_goods->where("id", "IN", $ids)->select();
        foreach($goodsList as $goods) {
            $origin = $goods->toArray();
            $goods->inc("print_label")->update();
            $after = $goods->toArray();
            $this->logger('logs.order.label.print', 'UPDATED', [$origin, $after]);
        }
        return $this->success();
    }
    
    /**
     * 配送单次数记录
     *
     * @param OrderDelivery $order_delivery_model
     * @return void
     */
    public function printDeliveryRecord(OrderDelivery $order_delivery_model)
    {
        $ids = input("post.ids", "");
        if (is_string($ids)) {
            $ids = explode(",", $ids);
        }
        $deliveryList = $order_delivery_model->where("id", "IN", $ids)->select();
        foreach($deliveryList as $delivery) {
            $origin = $delivery->toArray();
            $delivery->inc("print_times")->update();
            $after = $goods->toArray();
            $this->logger('logs.order.delivery.print', 'UPDATED', [$origin, $after]);
        }
        return $this->success();
    }

    /**
     * 补单记录
     *
     * @param OrderGoods $order_goods
     * @return void
     */
    public function supplement(OrderGoods $order_goods)
    {
        if($this->request->isAjax() || $this->request->isPost()) {
            $filter = array_keys_filter($this->request->param(), [
                ["search_value", ""],
                ['search_time', ''],
                ['print', 0],
                ['export', 0]
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $order_goods->getSupplementList($page, $limit, $filter);
            return $this->success($data);
        } else {
            return $this->fetch('Order/supplement');
        }
    }

    /**
     * 订单收款
     *
     * @param OrderModel $order_model
     * @return void
     */
    public function pay(OrderModel $order_model)
    {
        $orderid = (int)input('id', 0);
        $order = $order_model->find($orderid);
        if (empty($order)) {
            return $this->fail("订单记录不存在");
        }
        try {
            $result = $order->pay(input('post.'));
            $this->logger('logs.order.pay', 'UPDATED', $result);
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }
        return $this->success();
    }

    /**
     * 支付状态变更
     *
     * @param OrderDelivery $model
     * @return void
     */
    public function payUpdate(OrderModel $model)
    {
        $ids = input("ids", "");
        $status = (int)input("status", 0);
        if (!is_array($ids)) {
            $ids = explode(",", $ids);
        }
        $orders = $model->where("id", "IN", $ids)->select();
        foreach($orders as $order) {
            $money = floatval($order["order_money"]) - floatval($order["pay_money"]);
            $result = $order->pay([
                "money" => $money,
                "status" => 2
            ]);
            $this->logger('logs.order.pay', 'UPDATED', $result);
            event("OrderChange", $order['id']);
        }
        return $this->success();
    }

    /**
     * 收款记录明细
     *
     * @param OrderPay $pay_model
     * @return void
     */
    public function payLogs(OrderModel $model, OrderPay $pay_model)
    {
        $orderid = (int)input("id");
        $order = $model->find($orderid);
        if (empty($order)) {
            return $this->fail("订单号不存在");
        }
        $logs = $pay_model->with(['user'])->where("trade_no", $order['trade_no'])->order("id DESC")->select();
        $this->assign("logs", $logs);
        $this->assign("order", $order);
        return $this->fetch("Order/pay_logs");
    }
}