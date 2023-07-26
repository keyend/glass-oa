<?php
namespace app\admin\controller\system;
use app\admin\Controller;
use app\common\model\system\UserAccessModel;
/**
 * 后台管理账户安全
 * @package admin.controller.index
 * @version 1.0.0
 */
class Security extends Controller
{
    /**
     * 进入账户安全界面
     *
     * @param UserAccessModel $model
     * @return void
     */
    public function index(UserAccessModel $model)
    {
        $roles = ['sysUser', 'sysGroup', 'sysRole', 'sysRule'];
        foreach($roles as $role) {
            if ($model->check($role, $this->request->user)) {
                redirect(url($role))->send();
                die;
            }
        }

        return $this->fail(lang('no access'));
    }
}