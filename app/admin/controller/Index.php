<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\crebo\Profit;
use app\common\model\crebo\Stores;
use app\common\model\crebo\Users;

/**
 * 后台管理首页
 * @package admin.controller.index
 * @version 1.0.0
 */
class Index extends Controller
{
    /**
     * 管理界面
     *
     * @return void
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 仪表盘页面
     *
     * @return void
     */
    public function dashboard()
    {
        // error_reporting(E_ALL);
        // $es = app('mushroom')->ES('hello');
        // $query = $es->find(5);
        // // $query->title = $query->title . "###";
        // $query->delete();
        // die;
        // // var_dump($query->toArray());
        // // die;
        // // $query = $es->where(['title', 'LIKE', '我'])->order('id ASC')->field('id,title,content')->select();
        // // die;
        // // $es->create([[...]]);
        // // $es->insert([]);
        // // $es->insertAll([[],[]]);
        // // $es->find();
        // // $es->where()->find();
        // // $query = $es->insert([
        // //     'id' => 6,
        // //     'title' => 'The IteratorAggregate interface ',
        // //     'content' => 'It might seem obvious, but you can return a compiled generator from your IteratorAggregate::getIterator() implementation.'
        // // ]);
        // $query = $es->select();
        // var_dump($query->toArray());
        // 获取当月所有时间
        $month_time = [];
        $time_type = [];

        for ($i = 0;$i < 30;$i++){
            $month_time[] = strtotime('-'.$i.' day');
            $time_type[] = "'".date('m-d',strtotime('-'.$i.' day'))."'";
        }

        $time_type = array_reverse($time_type);
        $time_type = implode(',',$time_type);

        // 获取当月间隔时间
        $start_time = strtotime(date('Y-m-d',strtotime('-29 day')));
        $end_time = strtotime('now');

        // 获取30天的统计记录
        $profit = Profit::whereTime('create_time','>=',$start_time)
            ->whereTime('create_time','<=',$end_time)
            ->order('create_time desc')
            ->select();


        // 图表统计
        $series_reg = str_split(str_repeat('0',30),1);
        $series_order = str_split(str_repeat('0',30),1);
        $series_money = str_split(str_repeat('0',30),1);

        foreach ($month_time as $key => $item){
            $item_time = date('Y-m-d',$item);
            foreach ($profit as $profit_item){
                $profit_time = date('Y-m-d',$profit_item['create_time']);
                if($item_time == $profit_time){
                    $series_reg[$key] = $profit_item['count_reg'];
                    $series_order[$key] = $profit_item['count_order_yes'];
                    $series_money[$key] = $profit_item['count_money'];
                }
            }
        }

        $series_reg = array_reverse($series_reg);
        $series_reg = implode(',',$series_reg);

        $series_order = array_reverse($series_order);
        $series_order = implode(',',$series_order);

        $series_money = array_reverse($series_money);
        $series_money = implode(',',$series_money);

        // 用户数量统计

        $default_group = config('register.default_group');
        $vip_group = config('vip.vip_group');

        $default_user = Users::where('group',$default_group)->count();
        $vip_user = Users::where('group',$vip_group)->count();

        $file = Stores::count();
        $size = Stores::sum('size');

        $this->assign('count',[
            'user' => $default_user,
            'vip' => $vip_user,
            'file' => $file,
            'size' => countSize($size)
        ]);

        $this->assign('profit',$profit);

        $this->assign('series_reg',$series_reg);
        $this->assign('series_order',$series_order);
        $this->assign('series_money',$series_money);
        $this->assign('time_type',$time_type);

        return $this->fetch();
    }

    /**
     * 未定义路由
     *
     * @return void
     */
    public function miss()
    {
        return $this->fetch('Error/404');
        die('<h1>Not Found!</h1>');
    }

    /**
     * 清除运行缓存
     *
     * @return void
     */
    public function clearCache()
    {
        redis()->tag("temp")->clear();
        $paths = glob(dirname($this->app->getRuntimePath()) . "/*", GLOB_ONLYDIR);
        foreach($paths as $path) {
            if (is_dir($path)) {
                $this->clearFolder($path, basename($path) == "cache");
            }
        }

        return $this->success();
    }

    /**
     * 清除文件目录
     *
     * @param string $path
     * @return void
     */
    private function clearFolder($path = '', $flag = false)
    {
        foreach(glob($path . "/*") as $_path) {
            if (is_dir($_path)) {
                if ($flag || basename($_path) == "temp") {
                    $this->clearFolder($_path, true);
                }
            } else {
                unlink($_path);
            }
        }

        if ($flag) {
            unlink($path);
        }
    }
}
