<?php
namespace app\admin\controller\system;
use app\admin\Controller;
use app\common\model\system\UserModel;
use app\common\model\system\UserGroupModel;
use app\common\model\system\LogsModel;
use app\common\model\system\UserAttrModel;
use think\Lang;

/**
 * 后台管理会员
 * @package admin.controller.index
 * @version 1.0.0
 */
class User extends Controller
{
    /**
     * 管理用户列表
     *
     * @return void
     */
    public function index(UserModel $user_model)
    {
        if (IS_AJAX) {
            $filter = array_keys_filter($this->request->param(), [
                ['keyword', 0]
            ]);
            [$page, $limit] = $this->getPaginator();
            $data = $user_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $userGroupModel = app()->make(UserGroupModel::class);
            $userGroup = $userGroupModel->getList();
            $this->assign('userGroup', $userGroup);
        }

        return $this->fetch(__FUNCTION__);
    }

    /**
     * 更新用户信息
     *
     * @param UserModel $user_model
     * @return void
     */
    public function status(UserModel $user_model)
    {
        $id = (int)$this->request->get('id');
        $data = array_keys_filter($this->request->post(), [
            ['status', 0]
        ]);
        $data['status'] = (int)$data['status'];
        $data['user_id'] = $id;
        $user = $user_model->where('user_id', $id)->with(['group', 'attr'])->find();
        if (!$user) {
            return $this->fail(lang('no exist'));
        } elseif (super($user->group->group_range)) {
            return $this->fail(lang('no access'));
        }
        $user->update($data);
        $this->logger('logs.sys.user.status', 'UPDATED', $user);
        return $this->success();
    }

    /**
     * 获取用户提交参数
     *
     * @return void
     */
    private function getFormFields($fields = [])
    {
        $filters = [
            ['username', ''],
            ['password', ''],
            ['group_id', 0],
            ['parent_id', 0]
        ];
        $attrs = $this->getAttrList();
        $required = [];
        foreach($attrs as $attr) {
            $filters[] = [$attr['attr'], ''];
            if ($attr['required'] != '') {
                $required[$attr['attr']] = $attr['title'];
            }
        }

        if (!empty($fields)) {
            foreach($filters as $i => $key) {
                if (is_array($key)) {
                    $key = $key[0];
                }
                if (in_array($key, $fields)) {
                    unset($filters[$i]);
                }
            }
        }

        $requiredKeys = array_keys($required);
        $data = array_keys_filter($this->request->post(), $filters);
        foreach($data as $key => $value) {
            if (empty($value)) {
                if (in_array($key, $requiredKeys)) {
                    return $this->fail($required[$key] . "不能为空!");
                }
            }
        }

        return $data;
    }

    /**
     * 返回扩展字段
     *
     * @return void
     */
    private function getAttrList()
    {
        static $attrs = null;
        if ($attrs === null) {
            $attr_model = app()->make(UserAttrModel::class);
            $attrs = $attr_model->getList();
        }
        return $attrs;
    }

    /**
     * 添加用户信息
     *
     * @param UserModel $user_model
     * @return void
     */
    public function add(UserModel $user_model)
    {
        if (IS_POST) {
            $data = $this->getFormFields();
            $data['parent_id'] = (int)$data['parent_id'];
            $data['group_id'] = (int)$data['group_id'];
            $data['status'] = 1;
            $data['create_time'] = TIMESTAMP;
            $data['expire_time'] = TIMESTAMP + 63072000;
            if (empty($data['password'])||$data['parent_id']==0||$data['group_id']==0) {
                return $this->fail('INVALID PARAM');
            }
            $data['salt'] = rand_string();
            $data['password'] = password_check($data['password'], $data['salt']);
            $attrs = $this->getAttrList();
            $res = $user_model->addUser($data, $attrs);
            return $this->success($res);
        } else {
            $item = ['is_delete' => 1, 'parent_id' => S1, 'group' => []];
            $userGroupModel = app()->make(UserGroupModel::class);
            $userGroup = $userGroupModel->getList();
            $userAttrModel = app()->make(UserAttrModel::class);
            $userAttrs = $userAttrModel->getList();
            foreach($userAttrs as &$userAttr) {
                $userAttr['required'] = $userAttr['required']?'required':'';
            }

            $this->assign('userGroup', $userGroup);
            $this->assign('userAttr', $userAttrs);
            $this->assign('item', $item);
        }

        return $this->fetch('form');
    }

    /**
     * 编辑用户信息
     *
     * @param UserModel $user_model
     * @return void
     */
    public function edit(UserModel $user_model)
    {
        $id = (int)$this->request->param('id');
        $item = $user_model->getUser([['user_id', '=', $id]]);
        if (empty($item)) {
            return $this->fail('用户不存在!');
        }

        if (IS_POST) {
            $data = $this->getFormFields(['parent_id', 'group_id']);
            $data['update_time'] = TIMESTAMP;
            if (!empty($data['password'])) {
                $data['salt'] = rand_string();
                $data['password'] = password_check($data['password'], $data['salt']);
            } else {
                unset($data['password']);
            }
            $attrs = $this->getAttrList();
            $res = $item->editUser($data, $attrs);
            return $this->success($res);
        } else {
            $userGroupModel = app()->make(UserGroupModel::class);
            $userGroup = $userGroupModel->getList();
            $userAttrModel = app()->make(UserAttrModel::class);
            $userAttrs = $userAttrModel->getList();
            foreach($userAttrs as &$userAttr) {
                $userAttr['required'] = $userAttr['required']?'required':'';
            }
            $this->assign('userGroup', $userGroup);
            $this->assign('userAttr', $userAttrs);
            $this->assign('item', $item->getData());
        }

        return $this->fetch('form');
    }

    /**
     * 删除用户
     *
     * @param UserModel $user_model
     * @return void
     */
    public function delete(UserModel $user_model)
    {
        $id = (int)$this->request->post('id');
        if ($id === S1) {
            return $this->fail(lang('no access'));
        }

        $user = $user_model->getUser([['user_id', '=', $id]]);
        if (!$user) {
            return $this->fail(lang('no exist'));
        }
        $user->delUser();

        return $this->success();
    }

    /**
     * 修改正身登录密码
     *
     * @param UserModel $user_model
     * @return void
     */
    public function accountSecurity(UserModel $user_model)
    {
        if (IS_AJAX) {
            $data = array_keys_filter($this->request->post(), [
                ['password', ''],
                ['password1', '']
            ]);

            if (empty($data['password1'])) {
                return $this->fail("INVALID PARAM");
            }

            $user = $user_model->find(S1);
            if (!password_check($user->password, $user->salt, $data['password'])) {
                return $this->fail("当前密码输入不正确!");
            }

            $user->password = password_check($data['password1'], $user->salt);
            if ($user->save()) {
                $this->logger('logs.sys.user.edit', 'UPDATED', $user);
                event("OpeartorSecurity", $this->request->user);
            }

            return $this->success();
        }
        return $this->fetch('security');
    }
}