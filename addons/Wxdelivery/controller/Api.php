<?php

namespace addons\Wxdelivery\controller;

use addons\Wxdelivery\model\Wxdelivery;

class Api extends \app\common\controller\Api
{
    /**
     * 定时任务
     * @return \think\response\Json
     */
    public function crontab()
    {
        $m = new Wxdelivery();
        $where = [
            ['status','in', [1]]
        ];
        $list = $m->where($where)->limit(10)->select();
        foreach($list as $v){
            $re = $m->toSend($v['id']);
            dump($re);
        }
        return "over";
    }

}
