<?php
/**
 * 三方接入
 * @version 1.0.0
 */
namespace app\api\controller;
use app\api\Controller;

class Soclia extends Controller
{
    /**
     * 微信交互
     *
     * @return void
     */
    public function wechat()
    {
        try {
            return $this->model->wechat->listener();
        } catch(\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}