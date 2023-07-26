<?php
namespace app\common\model\crebo;
use think\Model;

class Category extends Model
{
    protected $name = "users_category";

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
        $list = $query->page($page,$limit)->order('id desc')->select();
        return compact('count', 'list');
    }

    /**
     * 加类目
     *
     * @param [type] $data
     * @return void
     */
    public function addCategory($data){
        self::insert($data);
        $this->updateCache();
    }

    /**
     * 更新类目
     *
     * @param [type] $id
     * @param [type] $data
     * @return void
     */
    public function editCategory($id, $data){
        self::where('id',$id)->update($data);
        $this->updateCache();
    }

    private function updateCache()
    {
        $categorys = [];
        $list = $this->where("1=1")->select()->toArray();
        foreach($list as $row) {
            if (!isset($categorys[$row['group']])) {
                $categorys[$row['group']] = [
                    'group' => $row["group"],
                    "list" => []
                ];
            }
            $categorys[$row['group']]['list'][] = $row;
        }
        redis()->set("category", array_values($categorys));
    }

    public function getCategories()
    {
        return redis()->get("category") ?? [];
    }
}