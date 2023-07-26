<?php
namespace app\common\model\system;
/*
 * 用户工作组应用角色
 * @package app.common.model
 * @Author: k.
 * @Date: 2021-05-10 20:19:31
 */
use app\Model;

class UserGroupRoleModel extends Model
{
    protected $name = 'sys_group_role';
    protected $pk = 'group_role_id';

    /**
     * 对应工作组
     * @collection relation.model
     */
    public function group() {
        return $this->hasOne(UserGroupModel::class, 'group_id', 'group_id');
    }

    /**
     * 对应角色
     * @collection relation.model
     */
    public function role() {
        return $this->hasOne(UserRoleModel::class, 'role_id', 'role_id');
    }
}
