<?php
namespace mashroom\plugins\merchant\v1\model;
/**
 * 商户关联中间表
 * 
 * @package app.common.model
 * @Author: k.
 * @Date: 2021-05-10 20:19:31
 */
use think\Model;

class MerchantUserModel extends Model
{
    protected $name = 'sys_merchant_user';
    protected $pk = 'mer_user_id';

    /**
     * 用户信息
     * @collection relation.model
     */
    public function user()
    {
        return $this->hasOne(UserModel::class, 'user_id', 'user_id');
    }

    /**
     * 商户
     * @collection relation.model
     */
    public function merchant()
    {
        return $this->hasOne(MerchantModel::class, 'mer_id', 'mer_id');
    }
}
