<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\crebo\OrderPay;
/**
 * 财务管理
 * @package admin.controller.finance
 * @version 1.0.0
 */
class Finance extends Controller
{
    /**
     * 收款明细
     *
     * @param integer $id
     * @param Withdraw $withdraw_model
     * @return void
     */
    public function payment(OrderPay $orderpay_model)
    {
        if ($this->request->isAjax() || $this->request->isPost()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""],
                ["search_time", ""],
                ["export", 0]
            ]);
            if ($id != 'all') {
                $filter["status"] = $id;
            }
            [$page, $limit] = $this->getPaginator();
            $data = $orderpay_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            return $this->fetch('Finance/payment');
        }
    }

    /**
     * 应收账款
     *
     * @param OrderPay $orderpay_model
     * @return void
     */
    public function receivable(OrderPay $orderpay_model)
    {
        if ($this->request->isAjax() || $this->request->isPost()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""],
                ["search_time", ""],
                ["export", 0]
            ]);
            if ($id != 'all') {
                $filter["status"] = $id;
            }
            [$page, $limit] = $this->getPaginator();
            $data = $orderpay_model->getReceivable($page, $limit, $filter);
            return $this->success($data);
        } else {
            return $this->fetch('Finance/receivable');
        }
    }
}