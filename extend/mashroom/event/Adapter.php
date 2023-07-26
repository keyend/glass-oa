<?php
namespace mashroom\event;

use think\facade\Event;
use think\facade\Cache;
/**
 * 灵活事件分配
 * @version 1.0.0
 */
class Adapter
{
    public function handle()
    {
        $this->listener();
    }

    /**
     * 清除缓存
     *
     * @return void
     */
    public function clear()
    {
        Cache::tag("event")->clear();
    }

    /**
     * 监听事件
     *
     * @return void
     */
    private function listener()
    {
        // $cache = Cache::get("adapter_list");
        // if (!empty($cache)) {
        //     $folders = glob(app()->getBasePath(), GLOB_ONLYDIR);
        //     foreach($folders as $folder) {
        //         $file = $folder . DIRECTORY_SEPARATOR . "event.php";
        //         if (file_exists($file)) {

        //         }
        //     }
        // }
    }
}