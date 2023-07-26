<?php
namespace app\admin\controller\system;
use app\admin\Controller;
use app\common\model\system\UserRoleModel;
use app\common\model\system\UserRoleAccessModel;
/**
 * 后台管理角色管理
 * @package admin.controller.index
 * @version 1.0.0
 */
class Role extends Controller
{
    /**
     * 角色列表
     *
     * @param UserRoleModel $user_role_model
     * @return void
     */
    public function index(UserRoleModel $user_role_model)
    {
        if (IS_AJAX) {
            $filter = array_keys_filter($this->request->param(), [
                ['keyword', 0]
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $user_role_model->getList($page, $limit, $filter);
            return $this->success($data);
        }

        return $this->fetch(__FUNCTION__);
    }

    /**
     * 添加角色
     *
     * @param UserRoleModel $user_role_model
     * @return void
     */
    public function add(UserRoleModel $user_role_model)
    {
        if (IS_POST) {
            $data = array_keys_filter($this->request->post(), [
                'role',
                'remark',
                'treeCheckbox'
            ]);
            $data['roles'] = $data['treeCheckbox'];
            if ($user_role_model->where('role', $data['role'])->find()) {
                return $this->fail('角色已存在');
            }
            $data['role_id'] = $user_role_model->insertGetId(array_keys_filter($data, [
                'role', 
                'remark'
            ]));

            if ($data['role_id']) {
                $roles = [];
                foreach($data['roles'] as $rule_id) {
                    $roles[] = [
                        'rule_id'  => $rule_id,
                        'role_id' => $data['role_id'],
                        'tree_in' => 1
                    ];
                }
                (new UserRoleAccessModel)->insertAll($roles);
            }
            $this->logger('logs.sys.role.create', 'CREATEED', $data);
            return $this->success($data);
        } else {
            $edit = $user_role_model->getRole();
            $edit['internal'] = 0;
            $this->assign('edit', $edit);
        }
        return $this->fetch('form');
    }

    /**
     * 编辑角色
     *
     * @param UserRoleModel $user_role_model
     * @param UserRoleAccessModel $user_role_access_model
     * @return void
     */
    public function edit(UserRoleModel $user_role_model, UserRoleAccessModel $user_role_access_model)
    {
        $id = (int)$this->request->param('id');
        $edit = $user_role_model->getRole($id);
        if (!isset($edit['role_id'])) {
            return $this->fail('角色不存在!');
        }

        if (IS_POST) {
            if ($edit->internal) {
                return $this->fail(lang('no access'));
            }
            $data = array_keys_filter($this->request->post(), [
                'role',
                'remark',
                'treeCheckbox'
            ]);
            $data['roles'] = $data['treeCheckbox'];
            $role = $user_role_model->find($id);
            $role->save(array_keys_filter($data, [
                'role', 
                'remark'
            ]));

            $user_role_access_model->where('role_id', $id)->delete();
            $roles = [];
            foreach($data['roles'] as $rule_id) {
                $roles[] = [
                    'rule_id'  => $rule_id,
                    'role_id' => $id,
                    'tree_in' => 1
                ];
            }
    
            $user_role_access_model->insertAll($roles);
            $this->logger('logs.sys.role.update', 'UPDATED', $data);
            return $this->success();
        } else {
            $this->assign('edit', $edit);
        }

        return $this->fetch('form');
    }

    /**
     * 删除角色
     * @param int $id
     * @return mixed
     */
    public function delete(UserRoleModel $user_role_model)
    {
        $id = (int)$this->request->param('id');
        $edit = $user_role_model->with(['access'])->find($id);
        if (empty($edit)) {
            return $this->fail('角色不存在!');
        }
        $this->logger('logs.sys.role.delete', 'DELETE', $edit);
        $edit->together(['access'])->delete();
        return $this->success();
    }
}