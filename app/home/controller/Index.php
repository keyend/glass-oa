<?php
namespace app\home\controller;
/**
 * 首页
 * @package home.controller.index
 * @version 1.0.0
 */
class Index extends \app\BaseController
{
    public function index()
    {
        return $this->fetch();
    }
}