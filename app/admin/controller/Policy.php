<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\crebo\Policys;

/**
 * 储存策略
 * @package admin.controller.policy
 * @version 1.0.0
 */
class Policy extends Controller
{
    /**
     * 策略列表
     *
     * @return void
     */
    public function index(Policys $policy_model)
    {
        if($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), []);
            [$page, $limit] = $this->getPaginator();
            $data = $policy_model->getList($page, $limit, $filter);
            return $this->success($data);
		} else {
			return $this->fetch();
		}
    }

    /**
     * 添加策略
     *
     * @param Policys $policy_model
     * @return void
     */
    public function add(Policys $policy_model)
    {
        if($this->request->isPost()){
            $policy_model->addPolicy(input('post.'));
            $this->logger('logs.policy.add', 'CREATEED', input("post."));
            return $this->success();
        }
        return $this->fetch('form');
    }

    /**
     * 编辑策略
     *
     * @param Policys $policy_model
     * @return void
     */
    public function edit(Policys $policy_model)
    {
        $id = input('get.id');
        $info = $policy_model->find($id);
        if (empty($info)){
            $this->fail('存储策略数据不存在');
        }

        if($this->request->isPost()){
            $policy_model->editPolicy($info["id"], input('post.'));
            $this->logger('logs.policy.edit', 'UPDATED', input("post."));
            return $this->success();
        }
        $this->assign('info',$info);
        return $this->fetch('form');
    }

    /**
     * 删除策略
     *
     * @param Policys $policy_model
     * @return void
     */
    public function delete(Policys $policy_model)
    {
        if (IS_AJAX) {
            $ids = $this->request->post('id');
            if (!is_array($ids)) {
                $ids = [ intval($ids) ];
            }

            $policys = $policy_model->where([["id", "IN", $ids]])->select();
            if (!empty($policys)) {
                foreach($policys as $policy) {
                    $this->logger('logs.policy.delete', 'DELETE', $policy);
                    $policy->delete();
                }
            }
        }
        return $this->success();
    }
}