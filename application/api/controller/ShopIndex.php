<?php
/*
 * @Author: Xyhao
 * @Date: 2026-01-24 15:46:59
 * @Description: 安徽爱喜网络科技有限公司
 */

namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\Goods as GoodsModel;
use app\common\model\Notice as NoticeModel;
use think\facade\Cache;

/**
 * 首页
 * Class ShopIndex
 * @package app\api\controller
 */
class ShopIndex extends Api
{
    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $result = [
            'status' => true,
            'msg' => '获取成功',
            'data' => []
        ];

        $GoodsModel = new GoodsModel();

        $hotlist = $GoodsModel->field("id,name,price,mktprice,thumb")->where("marketable",1)->where("goods_cat_id",4)->cache(3600)->select()->toArray();
        $reomlist = $GoodsModel->field("id,name,price,mktprice,image_id")->where("marketable",1)->where("is_recommend",1)->cache(3600)->select()->toArray();

        foreach ($hotlist as &$item) {
            $item['image_url'] = _sImage($item['thumb']);
            $item['price'] = floatval($item['price']);
        }

        foreach ($reomlist as &$item) {
            $item['image_url'] = _sImage($item['image_id']);
            $item['price'] = floatval($item['price']);
        }

        $noticeModel = new NoticeModel();
        $notice = $noticeModel->where('type',1)->cache(3600)->find();

        $result['data']['notice'] = $notice;
        $result['data']['reomlist'] = $reomlist;
        $result['data']['hotlist'] = $hotlist;
        return $result;
 
    }


}