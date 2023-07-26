<?php
namespace app\common\model\system;
/**
 * 组应用范围

 * @package app.common.model
 * @author: k.
 * @date: 2021-05-10 20:19:31
 */
use app\Model;

class UserRangeModel extends Model
{
    protected $name = 'sys_group_range';
    protected $pk = 'range_id';

    /**
     * 所属用户组
     * @collection relation.model
     */
    public function group()
    {
        return $this->hasOne(UserGroup::class, 'group_range', 'group_range')->field('group_id,group');
    }
}
