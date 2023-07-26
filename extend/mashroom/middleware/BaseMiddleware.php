<?php
namespace mashroom\middleware;
use app\Request;
use think\App;

class BaseMiddleware
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        if (!defined('MODULE') && !app()->runningInConsole()) {
            event("AppConstant", $app);
        }
    }
}
