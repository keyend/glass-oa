<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\crebo\Users;
use app\common\model\crebo\Order;
use app\common\model\crebo\OrderDelivery;

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
    public function dashboard(Order $order_model, OrderDelivery $order_delivery)
    {
        if (request()->isAjax()) {
            $patch = input("patch", "");
            if ($patch == "shape") {
                $model = new Users();
                $res = $model->getChartData();
            } elseif($patch == "pie") {
                $model = new Users();
                $res = $model->getPieData();
            } elseif($patch == "order") {
                $model = new Order();
                $res = $model->getChartData();
            } elseif($patch == "delivery") {
                $res = $order_delivery->getChartData();
            }
            return $this->success($res);
        } else {
            $today_first = mktime(0,0,0);
            $today_last = mktime(23,59,59);
            $today = $order_model->getBetweenData([$today_first, $today_last]);
            $this->assign("today", $today);
            $month_first = strtotime(date("Y-m-01", time()));
            $month_last = strtotime(date("Y-m-t", time()));
            $month = $order_model->getBetweenData([$month_first, $month_last]);
            $this->assign("month", $month);
            $total = $order_model->getBetweenData([0, $today_last]);
            $this->assign("total", $total);
            $delivery = $order_delivery->getBetweenData([$today_first, $today_last]);
            $this->assign("delivery", $delivery);
            return $this->fetch();
        }
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
