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
use app\common\model\crebo\Users;

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
        if ($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""]
            ]);
            if ($id != 'all') {
                $filter["status"] = $id;
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

            $orders = $order_model->where([["id", "IN", $ids]])->select();
            if (!empty($orders)) {
                foreach($orders as $order) {
                    if (in_array($order->status, [0, 2])) {
                        $this->logger('logs.order.delete', 'DELETE', $order);
                        $order->delete();
                    }
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
                        $this->logger('logs.order.trash', 'DELETE', $order);
                        $order->is_trash = 1;
                        $order->save();
                    }
                }
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
        $this->assign("order", $order);
        return $this->fetch();
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
            $category = [];
            foreach($customers_result["list"] as $row) {
                $item = $row->toArray();
                $item["category"] = json_encode($row->category, JSON_UNESCAPED_SLASHES);
                $customers[] = $item;
                if ($customer_id === 0) {
                    $customer_id = $row["id"];
                    $customer = $row["nickname"];
                    $customer_minarea = $row["minarea"];
                    $customer_category = $item["category"];
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
                ['search_time', ''],
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

            $delivery_length = max(array_values(array_column($order_list["delivery"], "id"))) + 1;
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
                $res = $order->addDelivery($delivery);
                return $this->success($res);
            } catch (\Exception $e) {
                return $this->fail($e->getMessage());
            }
		} else {
            $this->assign("order", $order_info);
            $options = $this->getOptions($config_model, "basic");
            $options["order_printrm"] = preg_replace(PHP_EOL, "\\n", $options["order_printrm"]);
            str_replace("", "", $options["order_printrm"]);
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
                ["search_value", ""]
            ]);
            if ($is_trash != 0) {
                $filter["is_trash"] = $is_trash;
            }
            [$page, $limit] = $this->getPaginator();
            $data = $order_delivery_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $this->assign("is_trash", $is_trash);
            return $this->fetch('Order/delivery_list');
        }
    }

    /**
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
    public function print(OrderDelivery $order_delivery_model, ConfigModel $config_model)
    {
        $id = input("id", 0);
        $delivery = $order_delivery_model->with(['goods', 'order', 'order.member'])->find($id);
        if (empty($delivery)) {
            return $this->fail("配送记录不存在!");
        }
        $options = $this->getOptions($config_model, "basic");
        $this->assign("manual", input("manual", 0));
        $this->assign("delivery", $delivery);
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
                ['print', 0]
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $order_goods->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            return $this->fetch('Order/label');
        }
    }
}