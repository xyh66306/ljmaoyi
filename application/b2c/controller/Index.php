<?php
/*
 * @Author: Xyhao
 * @Date: 2026-01-22 09:39:11
 * @Description: 安徽爱喜网络科技有限公司
 */
// +----------------------------------------------------------------------
// | JSHOP [ 小程序 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2018 http://jihainet.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: mark <jima@jihainet.com>
// +----------------------------------------------------------------------
namespace app\b2c\controller;

use app\common\controller\Base;
use app\common\model\BillAftersales;
use app\common\model\UserFenyong;
use app\common\model\Payments;
use app\common\model\User;
use app\common\model\UserWx;
use org\login\Wxofficial;
use org\Wx;
use think\Hook;


class Index extends Base
{
    public function index()
    {
        $this->redirect('/wap/','302');
    }


    /**
     * 外部web-view调用客服页面
     * url: 域名+/b2c/index/kefu.html
     * @return mixed
     */
    public function kefu()
    {
        $ent_id = getSetting('ent_id');
        $this->assign('ent_id', $ent_id);
        return $this->fetch();
    }


    public function ceshi(){
        $userFenyong = new UserFenyong();

        $order_id = "S01311846366842";
        $user_id = 2;
        $res = $userFenyong->fanyong($order_id,$user_id);
        dump($res);

    }

}