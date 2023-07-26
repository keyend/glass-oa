<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\crebo\Withdraw;
/**
 * 财务管理
 * @package admin.controller.finance
 * @version 1.0.0
 */
class Finance extends Controller
{
    /**
     * 返回订单列表
     *
     * @param integer $id
     * @param Withdraw $withdraw_model
     * @return void
     */
    public function getWithdrawList($id, Withdraw $withdraw_model)
    {
        if ($this->request->isAjax()) {
            $filter = array_keys_filter($this->request->param(), [
                ['search_type', ""],
                ["search_value", ""]
            ]);
            if ($id != 'all') {
                $filter["status"] = $id;
            }
            [$page, $limit] = $this->getPaginator();
            $data = $withdraw_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $this->assign("status_id", $id);
            return $this->fetch('withdraw');
        }
    }

    /**
     * 用户提现列表
     *
     * @param Withdraw $withdraw_model
     * @return void
     */
    public function withdraw(Withdraw $withdraw_model)
    {
        $this->assign("status_id", 'all');
        return $this->fetch();
    }

    /**
     * 确认提现操作
     *
     * @param Withdraw $withdraw_model
     * @return void
     */
    public function auditWithdraw(Withdraw $withdraw_model)
    {
        if (IS_AJAX) {
            $ids = $this->request->post('id');
            $status = (int)$this->request->post('status');
            if (!is_array($ids)) {
                $ids = [ intval($ids) ];
            }

            $datas = $withdraw_model->where([["id", "IN", $ids]])->select();
            if (!empty($datas)) {
                foreach($datas as $data) {
                    if ($data->status == 0) {
                        $data->status = $status;
                        $data->save();
                        $this->logger('logs.withdraw.audit', 'UPDATED', $data);
                        event("WithdrawChange", $data->toArray());
                    }
                }
            }
        }
        return $this->success();
    }
}