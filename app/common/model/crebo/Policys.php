<?php

namespace app\common\model\crebo;

use think\Model;

class Policys extends Model
{
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
        $list = $query->page($page,$limit)->order('id desc')->field('id,name,type,max_size')->select()->each(function($item){
            $item['max_size'] = countSize($item['max_size']);
            $item['type'] = PolicyType($item['type']);
            $item['file_num'] = Stores::where('policy_id',$item['id'])->count();
            $item['store_num'] = countSize(Stores::where('policy_id',$item['id'])->sum('size'));
            return $item;
        });

        return compact('count', 'list');
    }

    public function addPolicy($data){
        //基础参数
        $keys = ['name','type','filetype'];

        // 附加参数
        $field = [];

        foreach ($data as $key => $item){
            if(!in_array($key,$keys)){
                $field[$key] = $item;
                unset($data[$key]);
            }
        }

        $data['config'] = json_encode($field);

        self::insert($data);
        event("PolicyChange");
    }

    public function editPolicy($id,$data){
        //基础参数
        $keys = ['name','type','filetype'];
        // 附加参数
        $field = [];
        foreach ($data as $key => $item){
            if(!in_array($key,$keys)){
                $field[$key] = $item;
                unset($data[$key]);
            }
        }
        $data['config'] = json_encode($field);
        self::where('id',$id)->update($data);
        event("PolicyChange");
    }

    public function getConfigAttr($value){
        return json_decode($value,true);
    }

    public static function getPolicyAll(): array
    {
        return self::field('id,name')->select()->toArray();
    }

}