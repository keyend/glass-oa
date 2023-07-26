<?php
namespace app\common\model\system;
/*
 * 用户扩展属性
 * @package app.common.model
 * @Author: k.
 * @Date: 2021-05-10 20:19:31
 */
use app\Model;

class UserExtendModel extends Model
{
    protected $name = 'sys_user_extend';
    protected $pk = 'user_extend_id';

    /**
     * 属性
     * @collection relation.model
     */
    public function info() {
        return $this->hasOne(UserAttrModel::class, 'attr_id', 'attr_id')->bind(['attr']);
    }
}
