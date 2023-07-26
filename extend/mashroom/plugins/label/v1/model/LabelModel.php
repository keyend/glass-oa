<?php
namespace app\common\model;
/**
 * 标签库
 * 
 * @package app.common.model
 * @Author: k.
 * @Date: 2021-05-10 20:19:31
 */
use think\Model;

class LabelModel extends Model
{
    protected $name = 'label';
    protected $pk = 'label_id';

    /**
     * 产品分类
     * @collection relation.model
     */
    public function category()
    {
        return $this->hasMany(CategoryLabelModel::class, 'label_id', 'label_id');
    }

    /**
     * 返回列表
     *
     * @param integer $page
     * @param integer $limit
     * @param boolean $force
     * @return array
     */
    public function getList(int $page = 1, int $limit = 9999, bool $force = false)
    {
        $data = fileCache('label', function($name = '') {
            // 倒序
            $query = self::with(['category'])->order('label_id DESC');
            // 获取所有记录
            $list = $query->select()->toArray();

            return $list;
        }, $force);

        return $data;
    }
}
