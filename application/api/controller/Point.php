<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\facade\Cache;
use think\facade\Request;
use app\common\model\Goods as GoodsModel;

/***
 * 积分相关接口
 * Class Point
 * @package app\api\controller
 * User: wjima
 * Email:jima@jihainet.com
 * Date: 2024-03-28
 */
class Point extends Api
{
    public function getDetial(){
        $goods_id = input('id/d', 0);          //商品ID
        $token    = input('token', '');        //token值 会员登录后传
        if (!$goods_id) {
            return error_code(10027);
        }
        $field       = input('field', '*');
        $goodsModel  = new GoodsModel();
        $returnGoods = $goodsModel->getPointDetial($goods_id, $field, $token);
        if ($returnGoods['status'] && $returnGoods['data']) {
            //浏览量加1
            $goodsModel->where([['id', '=', $goods_id]])->setInc('view_count', 1);
        }
        return $returnGoods;
    }
}
