<?php
namespace app\common\model\system;
/*
 * 用户权限属性
 * @package app.common.model
 * @Author: k.
 * @Date: 2021-05-10 20:19:31
 */
use app\Model;

class UserAccessExtendModel extends Model
{
    protected $name = 'sys_rule_extend';
    protected $pk = 'rule_extend_id';

    /**
     * 权限明细
     * @collection relation.model
     */
    public function rule() {
        return $this->hasOne(UserAccessModel::class, 'rule_id', 'rule_id');
    }
}
