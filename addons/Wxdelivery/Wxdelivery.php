<?php

namespace addons\Wxdelivery;

use addons\Wxdelivery\model\Wxdelivery as ModelWxdelivery;
use myxland\addons\Addons;
use app\common\model\Addons as addonsModel;
use app\common\model\BillPayments;
use app\common\model\OrderItems;
use app\common\model\Payments;
use app\common\model\UserWx;
use think\Db;
use DateTime;

/**
 * 微信小程序发货
 */
class Wxdelivery extends Addons
{
    // 该插件的基础信息
    public $info = [
        'name' => 'Wxdelivery',    // 插件标识
        'title' => '微信小程序发货信息管理服务',    // 插件名称
        'description' => '根据《商家自营类小程序运营规范》,特定类型的小程序需要在平台完成发货信息录入及确认收货流程后方可进行资金结算。可以通过该接入服务，完成商品发货信息录入、提醒用户确认收货、在小程序内调起确认收货组件等功能，提升发货信息录入效率，优化用户体验。',    // 插件简介
        'status' => 0,    // 状态
        'author' => 'sin',
        'version' => '1.0'
    ];

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $db = new Db();
        $sql = file_get_contents(ADDON_PATH . 'Wxdelivery/SQL/install.sql');
        $sql = str_replace("`jshop_", '`' . config('database.prefix'), $sql);
        $list = explode(';', $sql);
        for ($i = 0; $i < count($list); $i++) {
            if (trim($list[$i])) {
                $db::execute(trim($list[$i]));
            }
        }
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 实现的menu钩子方法
     * @return mixed
     */
    public function menu($params)
    {
        $addonModel = new addonsModel();
        $setting    = $addonModel->getSetting($this->info['name']);
        if (isset($setting['menu'])) {
            return $setting['menu'];
        }
        return true;
    }

    public function config($params = [])
    {
        $this->assign('config', $params);
        return $this->fetch('config');
    }

    public function deliveryOrder($data){
        $m = new ModelWxdelivery();
        $savedata = $this->formatData($data);
        if($savedata){
            $m->save($savedata);
        }
        return true;
    }

    //data里包含订单信息和发货单信息
    private function formatData($data){
        //https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/order-shipping/order-shipping.html

        $redata = "";

        //只同步微信小程序订单
        //目前没有source字段，如果需要，最好查一下
        // if($data['orderInfo']['source'] != 3){
        //     return false;
        // }

        //查询支付单
        $payment_id = $this->getPaymentId($data['orderInfo']['order_id']);
        if($payment_id == ""){
            return false;
        }

        //查询商户号
        $pModel = new Payments();
        $pInfo = $pModel->where('code','wechatpay')->find();
        if(!$pInfo){
            return false;
        }
        $pConfig = json_decode($pInfo['params'],true);
        if(!isset($pConfig['mch_id']) || $pConfig['mch_id'] == ""){
            return false;
        }

        $senddata = [
            'order_key' => [
                'order_number_type' => 1,
                'out_trade_no' => $payment_id,//$data['orderInfo']['order_id'],
                'mchid' => $pConfig['mch_id']
            ],
            'logistics_type' => 1,
            'delivery_mode' => 1,
            'shipping_list' => [
                [
                    'tracking_no' => $data['deliveryInfo']['logi_no'],                        //物流单号
                    'express_company' => $this->getWxExpressCode($data['deliveryInfo']['logi_code']),                    //物流公司编码  SF
                    'item_desc' => $this->getOrderItemsName($data['orderInfo']['order_id']),                      //订单明细
                    'contact' => [
                        'receiver_contact' => substr($data['orderInfo']['ship_mobile'], 0, 3) . "****" . substr($data['orderInfo']['ship_mobile'], 7)                //收货人信息
                    ]
                ]
            ],
            'upload_time' => date('Y-m-d\TH:i:s.120+08:00', time()),             //上传时间
            'payer' => [
                'openid' => $this->getWxOpenid($data['orderInfo']['user_id'])                  //小程序openid
            ]
        ];

        $savedata = [
            'order_id' => $data['orderInfo']['order_id'],
            'delivery_id' => $data['deliveryInfo']['delivery_id'],
            'status' => 1,
            'senddata' => json_encode($senddata,JSON_UNESCAPED_UNICODE),
            'redata' => $redata
        ];
        return $savedata;
    }

    //根据系统内部物流公司编码转换微信物流公司编码
    private function getWxExpressCode($logi_code){
        $logis = get_addon_config("Wxdelivery")['logis'];
        if(isset($logis[$logi_code])){
            return $logis[$logi_code];
        }
        return 'SF';
    }

    //获取订单明细商品
    private function getOrderItemsName($order_id){
        $m = new OrderItems();
        $list = $m->where('order_id', $order_id)->select();
        $name = "";
        foreach($list as $v){
            $name .= $v['name']." ";
        }
        return $name;
    }

    private function getWxOpenid($user_id){
        $m = new UserWx();
        $info = $m->where('user_id', $user_id)->where('type',1)->find();
        if($info){
            return $info['openid'];
        }else{
            return "";
        }
    }

    //根据订单号获取支付成功的支付单号
    private function getPaymentId($order_id){
        $p = new BillPayments();
        $info = $p->field('bp.payment_id')->alias('bp')
        ->join('bill_payments_rel bpr','bp.payment_id = bpr.payment_id')
        ->where('bpr.source_id', $order_id)
        ->where('bp.payment_code', 'wechatpay')
        ->where('bp.status', 2)
        ->find();
        if(!$info){
            return "";
        }
        return $info['payment_id'];
    }

}
