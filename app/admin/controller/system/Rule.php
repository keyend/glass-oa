<?php
namespace app\admin\controller\system;
use app\admin\Controller;
use app\common\model\system\UserAccessModel;
/**
 * 后台管理权限管理
 * @package admin.controller.index
 * @version 1.0.0
 */
class Rule extends Controller
{
    /**
     * 权限列表
     *
     * @return void
     */
    public function index(UserAccessModel $user_access_model)
    {
        if (IS_AJAX) {
            $filter = array_keys_filter($this->request->param(), [
                ['keyword', 0]
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $user_access_model->getList($page, $limit, $filter);
            return $this->success($data);
        }

        return $this->fetch(__FUNCTION__);
    }

    /**
     * 添加权限
     *
     * @param UserAccessModel $user_access_model
     * @return void
     */
    public function add(UserAccessModel $user_access_model)
    {
        $id = (int)$this->request->get('id');
        if (IS_POST) {
            $data = array_keys_filter($this->request->post(), [
                ['parent_id', 0],
                'name',
                'title',
                ['sort', 0]
            ]);
            $data['parent_id'] = (int)$data['parent_id'];
            if (empty($data['name']) || empty($data['title'])) {
                return $this->fail('标题、标识不能为空!');
            }
            $data['rule_id'] = $user_access_model->insertGetId($data);
            $this->logger('logs.sys.rule.create', 'CREATEED', $data);
            return $this->success($data);
        } else {
            $id = $id == 0 ? $user_access_model->getParentId() : $id;
            $edit = ['parent_id' => $id];
            $parentRule = [];
            if ($id != 0) {
                $parentRule = $user_access_model->find($id);
            }
            $this->assign('parentRule', $parentRule);
            $this->assign('edit', $edit);
        }

        return $this->fetch('form');
    }

    /**
     * 编辑权限
     *
     * @param UserAccessModel $user_access_model
     * @return void
     */
    public function edit(UserAccessModel $user_access_model)
    {
        $id = (int)$this->request->get('id');
        $edit = $user_access_model->where([['rule_id', '=', $id]])->find();
        if (empty($edit)) {
            return $this->fail('记录不存在!');
        }

        if (IS_POST) {
            $data = array_keys_filter($this->request->post(), [
                'name',
                'title',
                ['sort', 0]
            ]);
            $data = array_merge($edit->toArray(), $data);
            $edit->update($data);
            $this->logger('logs.sys.rule.update', 'UPDATED', $data);
            return $this->success($data);
        } else {
            if ($id != 0) {
                $parentRule = $user_access_model->find($edit['parent_id']);
            }
            $this->assign('parentRule', $parentRule);
            $this->assign('edit', $edit);
        }

        return $this->fetch('form');
    }

    /**
     * 删除权限
     *
     * @param UserAccessModel $user_access_model
     * @return void
     */
    public function delete(UserAccessModel $user_access_model)
    {
        $id = (int)$this->request->param('id');
        $rule = $user_access_model->where('rule_id', (int)$id)->find();
        if (!$rule) {
            return $this->fail('记录不存在!');
        }

        $this->logger('logs.sys.rule.delete', 'DELETE', $rule);
        $rule->delete();
        return $this->success();
    }
}