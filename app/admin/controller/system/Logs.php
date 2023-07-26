<?php
namespace app\admin\controller\system;
use app\admin\Controller;
use app\common\model\system\LogsModel;
/**
 * 后台管理日志
 * @package admin.controller.index
 * @version 1.0.0
 */
class Logs extends Controller
{
    /**
     * 日志列表
     *
     * @param LogsModel $logs_model
     * @return void
     */
    public function index(LogsModel $logs_model)
    {
        if (IS_AJAX) {
            $labels = [
                'login' => ['LOGGED', 'REFRESH', 'LOGOUT'],
                'operator' => ['CREATEED', 'UPDATED', 'DELETE', 'UPDATED']
            ];
            $filter = array_keys_filter($this->request->param(), [
                ['type', 'operator'],
                ['date', ''],
                ['keyword', 0]
            ]);
            $filter['labels'] = $labels[$filter['type']];
            [$page, $limit] = $this->getPaginator();
            $data = $logs_model->getList($page, $limit, $filter);
            return $this->success($data);
        } elseif(checkAccess('sysLogsOperator')) {
            return redirect(url("sysLogsOperator")->build());
        } elseif(checkAccess('sysLogsLogin')) {
            return redirect(url("sysLogsLogin")->build());
        }
    }

    /**
     * 操作日志
     *
     * @return void
     */
    public function operator()
    {
        $this->assign('type', __FUNCTION__);
        return $this->fetch('index');
    }

    /**
     * 登录日志
     *
     * @return void
     */
    public function login()
    {
        $this->assign('type', __FUNCTION__);
        return $this->fetch('index');
    }
}