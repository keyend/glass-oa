<?php
/**
 * API
 * @version 1.0.0
 */
namespace app\api\controller;
use app\api\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fail("NOT_FOUND", 404);
    }
}
