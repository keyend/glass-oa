<?php
namespace app\common\model\crebo;

use app\common\exception\LoginError;
use think\Exception;
use think\facade\Session;
use think\facade\Cache;
use think\Model;

class Users extends Model
{
    /**
     * 实名认证
     * @collection relation.model
     */
    public function certify()
    {
        return $this->hasOne(Certify::class, 'id', 'uid');
    }

    /**
     * 所属组
     *
     * @return void
     */
    public function groups()
    {
        return $this->hasOne(Groups::class, 'id', 'group');
    }

    /**
     * 获取器
     *
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function getCategoryAttr($value, $data)
    {
        return unserialize($value);
    }

    /**
     * 修改器
     *
     * @param [type] $value
     * @return void
     */
    public function setCategoryAttr($value)
    {
        return serialize($value);
    }

    /**
     * 获取登录明细
     *
     * @param integer $id
     * @return void
     */
    static public function getInfo($id)
    {
        return self::where("id", $id)->field("username,nickname,avatar,parent_id,group,group_expire,is_auth,mobile,openid")->findOrEmpty();
    }

    /**
     * 用户组获取器
     *
     * @param string $value
     * @return void
     */
    public function getGroupAttr($value, $data)
    {
        static $default_group = null;
        if ($default_group === null) $default_group = conf('register.default_group');
        if ($data["group_expire"] != 0 && $value != $default_group) {
            return $default_group;
        }

        return $value;
    }


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
        if (isset($filter['search_type']) && !empty($filter['search_type']) && isset($filter['search_value']) && !empty($filter['search_value']) ) {
            $condition[] = [$filter["search_type"], 'LIKE', "%{$filter['search_value']}%"];
        }

        if (isset($filter['group']) && !empty($filter['group'])) {
            $condition[] = ['group', '=', (int)$filter['group']];
        }

        if (isset($filter['status']) && !empty($filter['status'])) {
            $condition[] = ['status', '=', (int)$filter['status']];
        }

        $query = $this->where($condition);
        $count = $query->count();
        $list = $query->page($page,$limit)->order('id desc')
            ->field('id,nickname,username,group,is_auth,amount,email,mobile,avatar,status,category,minarea,create_time')
            ->select()
            ->each(function ($item) {
                $item['avatar'] = getUserHead($item['avatar']);
                $item['minarea'] = (float)$item['minarea'];
                return $item;
            });

        return compact('count', 'list', 'default_group');
    }
    /**
     * 用户注册
     *
     * @param array $data
     * @return void
     */
    public function register($data = [])
    {
        if (self::where('username', $data["username"])->count() > 0){
            throw new Exception('当前帐号已被注册');
        }

        if (!empty($data["mobile"])) {
            $data["mobile_verifytime"] = TIMESTAMP;
        }
        $data["status"] = 1;
        $data["create_time"] = TIMESTAMP;
        $data["openid"] = uniqid();
        $data["device_type"] = "H5";
        self::insert($data);
        $data["id"] = self::getLastInsID();
        event("UserRegister", $data);
        return $data;
    }

    /**
     * 强制更新
     *
     * @param array $data
     * @return void
     */
    public function forceUpdate($data)
    {
        $this->save($data);
        request()->merge($this->getData());
    }

    /**
     * 用户登录登录方法
     * @param $username
     * @param $password
     * @param string $login_type
     * @return bool
     * @throws LoginError
     */
    public function login($username,$password,$login_type = 'default'){
        // 登录用户组类型
        if($login_type == 'admin'){
            $group = 1;
        }else{
            $group = config('register.default_group') .','.config('vip.vip_group');
        }
        // 查找用户
        $user = self::where('username',$username)->where('group','in',$group)->find();
        // 用户不存在
        if(empty($user)){
            throw new LoginError('登录帐号或者密码错误，请重试');
        }
        // 加密密码
        $password = md5($password . config('app.pass_salt'));
        if($user['password'] != $password){
            throw new LoginError('登录帐号或者密码错误，请重试');
        }

        // 不允许登录
        if($login_type == 'default' && $user['status'] == 0){
            throw new LoginError('登录帐号已被管理员封禁，请联系管理员处理！');
        }
        $update = [];
        $update['login_time'] = time();
        $update['login_real_ip'] = request()->ip();
        self::where('id',$user['id'])->update($update);
        // 登录成功
        Session::set($login_type .'_uid',$user['id']);
        Session::set($login_type .'_lkey',md5($username . $password . $user['status']));

        return true;
    }


    /**
     * 退出登录方法
     * @param string $type
     */
    public function logout(string $type = 'default'){
        Session::delete($type .'_uid');
        Session::delete($type .'_lkey');
    }


    /**
     *登录验证方法
     * @param string $type
     * @return bool
     */
    public function login_auth(string $type = 'default'): bool
    {
        $uid = Session::get($type .'_uid');
        $key = Session::get($type .'_lkey');

        $user = self::where('id',$uid)->find();

        if(empty($user)){
            return false;
        }

        if($key != md5($user['username'] . $user['password'] . $user['status'])){
            return false;
        }

        return true;
    }


    /**
     * 获取当前登录用户信息
     * @param string $type
     * @return Users|false
     */
    public function login_info(string $type = 'default'){
        $uid = Session::get($type .'_uid');

        $user = self::where('id',$uid)->find();

        if(empty($user)){
            return false;
        }

        return $user;
    }


    public function getgroup($id){
       $user = self::where('id',$id)->find();
       $user['group'] = Groups::where('id',$user['group'])->find();
           // ->field('group_name,policy_id,create_time,login_time')
 
      return $user;      
    }
	
    public function getCertify(){
        return Certify::where('uid',$this->id)
            ->where('status',1)
            ->field('name,idcard,create_time')
            ->find();
    }

    /**
     * 返回统计数据
     *
     * @return void
     */
    public function getChartData()
    {
        $timestamp = time();
        $currentMonthBegin = strtotime(date("Y-m-01", $timestamp));
        $currentMonthLast = strtotime(date("Y-m-t", $timestamp));
        $previousMonth = $currentMonthBegin - 86400;
        $previousMonthBegin = strtotime(date("Y-m-01", $previousMonth));
        $previousMonthLast = strtotime(date("Y-m-t", $previousMonth));
        $version = date("ymdH");
        $maps = [$currentMonthBegin,$currentMonthLast,$previousMonthBegin,$previousMonthLast,$version];
        $version = md5(json_encode($maps));
        $cache = Cache::get("shape");
        if (!empty($cache)) {
            // return $cache;
        }
        $customers = self::where("status", 1)->column("nickname", "id");
        $result = [];
        $result["list"] = [];
        $previousData = $this->getDataItem($customers, [$previousMonthBegin, $previousMonthLast]);
        $currentData = $this->getDataItem($customers, [$currentMonthBegin, $currentMonthLast]);
        $values = $this->sortValues([$previousData, $currentData, $customers]);
        $result["customer"] = $values["customer"];
        $result["list"][] = [
            "name" => date("m", $previousMonthBegin) . "月份",
            "type" => "bar",
            "data" => $values["previous"]
        ];
        $result["list"][] = [
            "name" => date("m", $currentMonthBegin) . "月份",
            "type" => "bar",
            "data" => $values["current"]
        ];
        Cache::set("shape", $result);
        return $result;
    }

    /**
     * 返回混合排序值
     *
     * @param [type] $data
     * @return void
     */
    public function sortValues($data) 
    {
        $sorts = [];
        foreach($data[0] as $id => $value) {
            $sorts[$id] = (string)floatval(($value + $data[1][$id]) * 100);
        }
        $sorts = array_flip($sorts);
        ksort($sorts);
        $res = [
            "customer" => [],
            "previous" => [],
            "current" => []
        ];
        foreach($sorts as $value => $id) {
            $res["previous"][] = $data[0][$id];
            $res["current"][] = $data[1][$id];
            $res["customer"][] = $data[2][$id];
        }
        return $res;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getDataItem($customers, $times)
    {
        $result = [];
        foreach($customers as $customer_id => $customer) {
            $result[$customer_id] = Order::where("customer_id", $customer_id)->where("create_time", 'BETWEEN', $times)->where("is_trash", 0)->sum("order_money");
        }
        return $result;
    }
}