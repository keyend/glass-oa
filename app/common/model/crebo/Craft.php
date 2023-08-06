<?php
namespace app\common\model\crebo;
use think\Model;

class Craft extends Model
{
    protected $name = "users_craft";

    /**
     * 列表
     *
     * @param int page
     * @param int limit 页码
     * @param array 筛选条件
     * @return array
     */
    public function getList($page, $limit, $filter = [])
    {
        $condition = [];
        $query = $this->where($condition);
        $count = $query->count();
        $list = $query->page($page,$limit)->order('sort DESC,id desc')->select();
        return compact('count', 'list');
    }

    /**
     * 加工艺
     *
     * @param [type] $data
     * @return void
     */
    public function addCraft($data){
        self::insert($data);
        $this->updateCache();
    }

    /**
     * 更新工艺
     *
     * @param [type] $id
     * @param [type] $data
     * @return void
     */
    public function editCraft($id, $data){
        self::where('id',$id)->update($data);
        $this->updateCache();
    }

    private function updateCache()
    {
        $list = $this->where("1=1")->select()->toArray();
        redis()->set("craft", array_values($list));
    }

    public function getCrafts()
    {
        return redis()->get("craft") ?? [];
    }
}