<?php
namespace app\admin\controller;
use app\admin\Controller;
use app\common\model\crebo\Reports;
/**
 * 举报处理
 * @package admin.controller.tipoff
 * @version 1.0.0
 */
class Tipoff extends Controller
{
    /**
     * 返回记录列表
     *
     * @param integer $id
     * @param Reports $report_model
     * @return void
     */
    public function getList($id, Reports $report_model)
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
            $data = $report_model->getList($page, $limit, $filter);
            return $this->success($data);
        } else {
            $this->assign("status_id", $id);
            return $this->fetch('withdraw');
        }
    }

    /**
     * 举报首页
     *
     * @param Reports $report_model
     * @return void
     */
    public function index(Reports $report_model)
    {
        $this->assign("status_id", 'all');
        return $this->fetch();
    }

    /**
     * 确认举报操作
     *
     * @param Reports $report_model
     * @return void
     */
    public function update(Reports $report_model)
    {
        if (IS_AJAX) {
            $ids = $this->request->post('id');
            $status = (int)$this->request->post('status');
            if (!is_array($ids)) {
                $ids = [ intval($ids) ];
            }

            $datas = $report_model->where([["id", "IN", $ids]])->select();
            if (!empty($datas)) {
                foreach($datas as $data) {
                    if ($data->status == 0) {
                        $data->status = $status;
                        $data->save();
                        $this->logger('logs.tipoff.change', 'UPDATED', $data);
                        event("TipoffChange", $data->toArray());
                    }
                }
            }
        }
        return $this->success();
    }

    /**
     * 举报记录明细
     *
     * @param Reports $report_model
     * @return void
     */
    public function detail(Reports $report_model)
    {
        $id = input('get.id');
        $info = $report_model->with(['member'])->find($id);

        if(empty($info)){
            $this->fail('记录不存在');
        }

        $info["type"] = $info->getType();
        $this->assign('info',$info);

        return $this->fetch();
    }
}