<?php

namespace app\Manage\controller;

use app\common\controller\Manage;
use app\common\model\UserFenyong;
use think\facade\Request;
use think\Db;

/**
 * 分佣列表
 *
 * @Author Fdw
 * @DateTime 2021-01-25
 */
class Commission extends Manage
{
    /**
     * 用户列表
     * @return mixed
     */
    public function index()
    {
        if (Request::isAjax()) {
            $commissionModel = new UserFenyong();
            return $commissionModel->tableData(input('param.'),true);
        }
        return $this->fetch('index');
    }

    /**
     * 修改分佣奖励金额
     *
     * @return void
     * @Author Fdw
     * @DateTime 2021-01-28
     */
    public function updateAward()
    {
        $result = [
            'status' => false,
            'data'   => [],
            'msg'    => '',
        ];
        $field  = input('post.field/s');
        $value  = input('post.value/d');
        $id     = input('post.id/d', '0');
        if (!$field || !$id) {
            return error_code(10081);
        }
        $res = Db::name('user_fenyong')->where(['id'=>$id])->update([$field => $value]);
        if ($res) {
            $result['msg']    = '更新成功';
            $result['status'] = true;
        } else {
            return error_code(10021);
        }
        return $result;
    }


    public function bonus()
    {
        if (Request::isAjax()) {
            $userFenhong = new UserFenhong();
            return $userFenhong->tableData(input('param.'),true);
        }
        return $this->fetch();
    }

}
