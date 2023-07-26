<?php
namespace app\cron\controller;
use app\BaseController;
use think\facade\Log;
use think\facade\Db;
use app\common\model\system\CronModel;
use app\cron\job\JobManager;

class Index extends BaseController
{
    /**
     * 订阅消息
     *
     * @param [type] $cron
     * @return void
     */
    private function execute($cron)
    {        
        $cron->reserved_at = TIMESTAMP;
        $cron->reserved = Db::raw('reserved-1');
        $cron->attempts = Db::raw('attempts+1');
        $cron->save();
        $payload = $cron->payload;
        if (is_array($payload)) {
            if ($cron->queue == "EntityMQService") {
                [$class, $method] = explode("::", $payload[0]);
                $argv = $payload[1];

                JobManager::push([
                    'require' => $class,
                    'method'  => $method,
                    "argv"    => $argv
                ]);
            } else {
                if (strpos($payload[0], "::") !== FALSE) {
                    [$class, $method] = explode("::", $payload[0]);
                    $argv = $payload[1];
                    app()->make($class)->$method($argv);
                } else {
                    $class = $payload[0];
                    $method = $payload[1];
                    app()->make($class)->$method();
                }
            }
        }
    }

    /**
     * 自动消费队列
     *
     * @param Cron $cron_model
     * @return void
     */
    public function index(CronModel $cron_model)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        Log::write("事件执行" . date("Y-m-d H:i:s"));
        $lastTimestamp = (int)redis()->get("cron_timestamp");
        if ($lastTimestamp - TIMESTAMP < 2) {
            exit();
        }

        redis()->tag("config")->set("cron_timestamp", TIMESTAMP);
        $cron = $cron_model->getSubscribe();
        if (!empty($cron)) {
            $this->execute($cron);
        }
    }
}
