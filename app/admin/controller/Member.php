<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\crebo\Category;
use app\common\model\crebo\Craft;
use app\common\model\crebo\Users;
use app\common\model\crebo\OrderPay;
/**
 * 后台用户管理
 * @package admin.controller.merchant
 * @version 1.0.0
 */
class Member extends Controller
{
    /**
     * 用户列表
     *
     * @return void
     */
    public function index(Users $user_model)
    {
        if($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ''],
                ['search_value', ''],
                ['group', '']
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $user_model->getList($page, $limit, $filter);
            return $this->success($data);
		} else {
			return $this->fetch();
		}
    }

    /**
     * 添加用户
     *
     * @param Users $user_model
     * @return void
     */
    public function add(Users $user_model, Category $category_model)
    {
        if($this->request->isPost()) {
			$data = input('post.');
			$result = $this->validate($data,[
                'username|客户标识' => 'require|alphaNum|length:6,26',
                'nickname|客户名称' => 'require'
            ]);

            if($result !== true) {
                return $this->fail($result);
            }

            unset($data["id"]);
            $data["category"] = serialize($data["category"]);
            $data["create_time"] = time();

            try {
				$user_model->register($data);
                $this->logger('logs.member.add', 'CREATEED', $data);
			} catch (Exception $e) {
                return $this->fail($e->getMessage());
			}

            return $this->success();
		} else {
            $this->assign("info", [
                "username" => uniqid(),
                "minarea" => 0.2
            ]);

            $this->assign("categorys", $category_model->getCategories());
			return $this->fetch('Member/form');
		}
    }

    /**
     * 编辑用户
     *
     * @param Users $user_model
     * @param Category $category_model
     * @return void
     */
    public function edit(Users $user_model, Category $category_model)
    {
        $id = (int)input('get.id');
		$info = $user_model->find($id);
		if (empty($info)) {
			$this->error('数据不存在');
		}

        if($this->request->isPost()) {
			$data = input('post.');
			$result = $this->validate($data,[
                'nickname|客户名称' => 'require',
                'minarea|保底面积' => 'require',
            ]);

            if($result !== true)
                return $this->fail($result);

            $update = [];
			if(!empty($data['mobile'])) {
				$update['mobile'] = $data['mobile'];
			}
            $update["id"] = $id;
            $update['desc'] = $data['desc'];
            $update['minarea'] = (float)$data['minarea'];
            $update["category"] = $data["category"] ?? [];
			$info->save($update);
            $this->logger('logs.member.edit', 'UPDATED', $info);
            return $this->success();
		} else {
            $data = $info->toArray();
            $data["category"] = $info->category;
            $this->assign("categorys", $category_model->getCategories());
			$this->assign('info',$data);
			return $this->fetch('Member/form');
		}
    }

    /**
     * 付款
     *
     * @param Users $user_model
     * @return void
     */
    public function pay(Users $user_model, OrderPay $order_pay)
    {
        $id = (int)input('get.id');
		$info = $user_model->find($id);
		if (empty($info)) {
			$this->error('数据不存在');
		}
        if($this->request->isPost()){
			$data = input('post.');
			$result = $this->validate($data,[
                'money|收款金额' => 'require',
                'remark|补充说明' => 'require',
            ]);
            $trade_no = date( 'YmdHis' );
            $payinfo = [
                "voucher" => $data["voucher"],
                "remark" => $data["remark"]
            ];
            try {
                $pay_id = $order_pay->insertGetId([
                    "uid"           => S1,
                    "customer_id"   => $info["id"],
                    "customer"      => $info["nickname"],
                    "trade_no"      => $trade_no,
                    "out_trade_no"  => $trade_no,
                    "pay_type"      => "OFFLINE",
                    "pay_money"     => $data["money"],
                    "pay_info"      => serialize($payinfo),
                    "pay_status"    => 1,
                    "pay_time"      => TIMESTAMP
                ]);
                return $this->success($pay_id);
            } catch(\Exception $e) {
                return $this->fail($e->getMessage());
            }
        }
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 付款记录
     *
     * @param Users $user_model
     * @param OrderPay $order_pay
     * @return void
     */
    public function palst(Users $user_model, OrderPay $order_pay)
    {
        if($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ''],
                ['search_value', ''],
                ['search_time', ''],
                ['customer_id', 0]
            ]);
            $filter["customer_id"] = (int)$filter["customer_id"];
            [$page, $limit] = $this->getPaginator();
            $data = $order_pay->getList($page, $limit, $filter);
            return $this->success($data);
		} else {
            $customer_id = input("mid", 0);
            $this->assign("customer_id", $customer_id);
			return $this->fetch();
		}
    }

    /**
     * 更新用户
     *
     * @param Users $user_model
     * @return void
     */
    public function update(Users $user_model)
    {
        if (IS_AJAX) {
            $id = (int)input('get.id');
            $status = (int)input("post.status");
            $info = $user_model->find($id);
            if (empty($info)) {
                $this->error('数据不存在');
            }
            $info->status = $status;
            $info->save();
            $this->logger('logs.member.edit', 'UPDATED', $info);
        }

        return $this->success();
    }

    /**
     * 删除用户
     *
     * @param Users $user_model
     * @return void
     */
    public function delete(Users $user_model)
    {
        if (IS_AJAX) {
            $ids = $this->request->post('id');
            if (!is_array($ids)) {
                $ids = [ intval($ids) ];
            }

            $members = $user_model->where([["id", "IN", $ids]])->select();
            if (!empty($members)) {
                foreach($members as $member) {
                    $this->logger('logs.member.delete', 'DELETE', $member);
                    $member->delete();
                }
            }
        }
        return $this->success();
    }

    /**
     * 用户等级管理
     *
     * @param Category $category_model
     * @return void
     */
    public function category(Category $category_model)
    {
        if($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), []);
            [$page, $limit] = $this->getPaginator();
            $data = $category_model->getList($page, $limit, $filter);
            return $this->success($data);
		} else {
			return $this->fetch();
		}
    }

    /**
     * 添加类目
     *
     * @param Category $category_model
     * @return void
     */
    public function addCategory(Category $category_model)
    {
        if($this->request->isPost()){
            $data = input('post.');
            $data["create_time"] = time();
            $category_model->addCategory($data);
            $this->logger('logs.category_model.add', 'CREATEED', input("post."));
            return $this->success();
        }
        return $this->fetch('Member/categoryForm');
    }

    /**
     * 编辑类目
     *
     * @param Category $category_model
     * @return void
     */
    public function editCategory(Category $category_model)
    {
        $id = input('get.id');
        $info = $category_model->find($id);
        if(empty($info)){
            $this->fail('类目不存在');
        }
        if($this->request->isPost()){
            $category_model->editCategory($id, input("post."));
            $this->logger('logs.category_model.edit', 'UPDATED', input("post."));
            return $this->success();
        }
        $this->assign('info',$info);
        return $this->fetch('Member/categoryForm');
    }

    /**
     * 删除类目
     *
     * @param Category $category_model
     * @return void
     */
    public function deleteCategory(Category $category_model)
    {
        if (IS_AJAX) {
            $ids = $this->request->post('id');
            if (!is_array($ids)) {
                $ids = [ intval($ids) ];
            }
            $infos = $category_model->where([["id", "IN", $ids]])->select();
            if (!empty($infos)) {
                foreach($infos as $info) {
                    $this->logger('logs.category_model.delete', 'DELETE', $info);
                    $info->delete();
                }
            }
        }
        return $this->success();
    }

    /**
     * 工艺列表
     *
     * @param Craft $craft_model
     * @return void
     */
    public function craft(Craft $craft_model)
    {
        if($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), []);
            [$page, $limit] = $this->getPaginator();
            $data = $craft_model->getList($page, $limit, $filter);
            return $this->success($data);
		} else {
			return $this->fetch();
		}
    }

    /**
     * 添加工艺
     *
     * @param Craft $craft_model
     * @return void
     */
    public function addCraft(Craft $craft_model)
    {
        if($this->request->isPost()){
            $data = input('post.');
            $data["create_time"] = time();
            $craft_model->addCraft($data);
            $this->logger('logs.craft.add', 'CREATEED', input("post."));
            return $this->success();
        }
        return $this->fetch('Member/craftForm');
    }

    /**
     * 编辑工艺
     *
     * @param Craft $craft_model
     * @return void
     */
    public function editCraft(Craft $craft_model)
    {
        $id = input('get.id');
        $info = $craft_model->find($id);
        if(empty($info)){
            $this->fail('工艺不存在');
        }
        if($this->request->isPost()){
            $craft_model->editCraft($id, input("post."));
            $this->logger('logs.craft.edit', 'UPDATED', input("post."));
            return $this->success();
        }
        $this->assign('info',$info);
        return $this->fetch('Member/craftForm');
    }

    /**
     * 删除类目
     *
     * @param Craft $craft_model
     * @return void
     */
    public function deleteCraft(Craft $craft_model)
    {
        if (IS_AJAX) {
            $ids = $this->request->post('id');
            if (!is_array($ids)) {
                $ids = [ intval($ids) ];
            }
            $infos = $craft_model->where([["id", "IN", $ids]])->select();
            if (!empty($infos)) {
                foreach($infos as $info) {
                    $this->logger('logs.craft.delete', 'DELETE', $info);
                    $info->delete();
                }
            }
        }
        return $this->success();
    }
}