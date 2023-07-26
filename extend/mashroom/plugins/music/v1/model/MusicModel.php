<?php
namespace mashroom\plugins\music\v1\model;
/**
 * 音乐
 * 
 * @package app.common.model
 * @Author: k.
 * @Date: 2021-05-10 20:19:31
 */
use think\Model;

class MusicModel extends Model
{
    protected $name = 'music';
    protected $pk = 'id';

    public function provider()
    {
        return $this->hasOne(MusicProviderModel::class, 'provider_id', 'provider_id');
    }
}
