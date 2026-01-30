<?php

namespace app\common\model;

use think\Db;

/**
 * 用户分销统计模型
 *
 * @Author Fdw
 * @DateTime 2021-01-19
 */
class UserFenyong extends Common
{

    /**
     * 返回layui的table所需要的格式
     * @author sin
     * @param $post
     * @return mixed
     */
    public function tableData($post, $isPage = true)
    {
        if (isset($post['limit'])) {
            $limit = $post['limit'];
        } else {
            $limit = config('paginate.list_rows');
        }
        $where = [];
        if (!empty($post['contribute'])) {
            $contributeuid = Db::name('user')->where(['mobile|username' => $post['contribute']])->value('id');
            if (!empty($contributeuid)) {
                $where[] = ['contribute_id', 'eq', $contributeuid];
            }
        }
        if (!empty($post['receipt'])) {
            $receiptuid = Db::name('user')->where(['mobile|username' => $post['receipt']])->value('id');
            if (!empty($receiptuid)) {
                $where[] = ['receipt_id', 'eq', $receiptuid];
            }
        }

        if (!empty($post['order_id'])) {
            $where[] = ['order_id', 'eq', $post['order_id']];
        }

        if (!empty($post['type'])) {
            $where[] = ['type', 'eq', $post['type']];
        }

        if (isset($post['datetime']) && $post['datetime'] != "") {
            $datetime = explode(' 到 ', $post['datetime']);
            $sd       = strtotime($datetime[0] . ' 00:00:00');
            $ed       = strtotime($datetime[1] . ' 23:59:59');
            $where[]  = ['ctime', 'BETWEEN', [$sd, $ed]];
        }

        if ($isPage) {
            $list      = Db::name('user_fenyong')->field('*')->where($where)->order('id desc')->paginate($limit);
            $re['sql'] = $this->getLastSql();

            $data = $list->all();
            foreach ($data as $key => $val) {
                $data[$key]['cust_price']         = bcmul($val['cust_price'],$val['nums'],2);

                $info = Db::name('user')->field("nickname,ident")->where(['id' => $val['receipt_id']])->find();

                $receipt_grade_name = Db::name('user_grade')->where(['id' => $val['receipt_grade']])->value('name');
                if($info['ident']==1){
                    $receipt_grade_name = $receipt_grade_name."合伙人";
                }
                $data[$key]['receipt_grade_name'] = $receipt_grade_name;

                $data[$key]['receipt_name']       = $info['nickname'];
                $data[$key]['contribute_name']    = Db::name('user')->where(['id' => $val['contribute_id']])->value('nickname');
                $data[$key]['contribute_grade_name'] = Db::name('user_grade')->where(['id' => $val['contribute_grade']])->value('name');
                $data[$key]['ctime'] = date('Y-m-d H:i:s', $val['ctime']);
                // $data[$key]['balance'] = bcadd($data[$key]['balance'], $data[$key]['price_spread'], 2);
            }
            $re['count'] = $list->total();
        } else {
            $list = Db::name('user_commission')->field('*')->where($where)->order('id desc')->select();
            if (!$list->isEmpty()) {
                $data = $list->toArray();
                foreach ($data as $k => $v) {
                    $data[$k]['receipt_grade_name'] = Db::name('user_grade')->where(['id' => $v['receipt_grade']])->value('name');
                    $data[$k]['receipt_name']       = Db::name('user')->where(['id' => $v['receipt_id']])->value('username');
                    $data[$k]['contribute_name']    = Db::name('user')->where(['id' => $v['contribute_id']])->value('username');
                    $data[$k]['contribute_grade_name'] = Db::name('user_grade')->where(['id' => $v['contribute_grade']])->value('name');
                    $data[$k]['ctime'] = date('Y-m-d H:i:s', $v['ctime']);
                    $data[$k]['balance'] = bcadd($data[$k]['balance'], $data[$k]['price_spread'], 2);
                }
            }
            $re['count'] = count($list);
        }
        $re['code'] = 0;
        $re['msg']  = '';

        $re['data'] = $data;

        return $re;
    }


    public function fanyong($order_id,$user_id){

        #订单详细
        $order_items = Db::name('order_items')->alias('a')
            ->join('order c', 'c.order_id = a.order_id', 'LEFT')
            ->join('goods g', 'g.id = a.goods_id', 'LEFT')
            ->where(['a.order_id'=>$order_id, 'c.pay_status'=>2, 'c.user_id'=>$user_id])
            ->where('a.is_fenyong', 0)
            ->field('a.*,c.ship_area_id, c.user_id, c.ctime as create_time,c.pay_status,c.payed,g.goods_cat_id,a.promotion_amount')
            ->select()
            ->toArray();


        if(empty($order_items)) {
            return true;
        }
        #用户邀请信息
        $userInfo = Db::name('user')->where(['id'=>$user_id])->find();
        if(empty($userInfo)){
            return true;
        }


        // if($userInfo['pid'] == 0){
        //     return true;
        // }else{
        //     //会员升级
        // }


        foreach($order_items as $key => $v) {

            if($v['goods_cat_id'] == 1){
                //套餐产品
                if($userInfo['pid'] == 0 || $userInfo['grade'] <=1){
                    continue;
                }
                $rate = 0;
                if($userInfo['grade'] ==2){
                    $rate = 0.08;
                }elseif($userInfo['grade'] ==3){
                    $rate = 0.12;
                }
                $profit = bcmul($v['payed'], $rate, 2);

            } else{
                //产品复购
                $this->fenyong_grade($userInfo,$v);   //代理返佣
            }

        }   

    }
    

    /*
    * 代理返佣
    * 区代、市代、省代（按收货地址划分）
    * $area_id 收货地址ID
    * $money 订单金额  
    * 区级区域销售总额：0.3%
    * 市级区域销售总额：0.3%
    * 省级区域销售总额：0.4%
    */
    public function fenyong_grade($userInfo,$orderInfo)
    { 
        if($orderInfo['order_amount']<=0){
            return;
        }
        $areaModel = new Area();
        $data      = $areaModel->getParents($orderInfo['ship_area_id']);
        if (empty($data) || count($data) < 3) { 
            return;
        }

        $province_id = isset($data[0]['id']) ? $data[0]['id'] : null;          //省代
        $city_id = isset($data[1]['id']) ? $data[1]['id'] : null;              //市代
        $district_id = isset($data[2]['id']) ? $data[2]['id'] : null;

        if (!$province_id || !$city_id || !$district_id) {
            return;
        }

        $userModel = new User();
        $provinces = $userModel->field("id,grade")->where(['area_id'=>$province_id])->count("id");
        $citys = $userModel->field("id,grade")->where(['area_id'=>$city_id])->count("id");
        $districts = $userModel->field("id,grade")->where(['area_id'=>$district_id])->count("id");  

        if($districts==0 && $citys==0 && $provinces==0){
            //找不到代理
            return;
        }
        //区级代理
        if($districts>0){ 
            $rate = 0.3/100;
            $profit = bcmul($orderInfo['order_amount'], $rate, 2);
            $avg_profit = bcdiv($profit, $districts, 2);  //区级区域销售平均奖励
            $disLst = $userModel->field("id,grade")->where(['area_id'=>$district_id])->select();
            foreach ($disLst as $key => $val) {
                $this->addDate(4,$val['id'],$val['grade'],$rate,$orderInfo['order_id'],$orderInfo['goods_id'],$orderInfo['product_id'],$orderInfo['order_amount'],$orderInfo['nums'], $avg_profit,$userInfo['id'],$userInfo['grade']);
            }  
        }
        //市级代理
        if($citys>0){ 
            $rate = 0.3/100;
            $profit = bcmul($orderInfo['order_amount'], $rate, 2);
            $avg_profit = bcdiv($profit, $citys, 2);  //市级代理销售平均奖励
            $cityLst = $userModel->field("id,grade")->where(['area_id'=>$city_id])->select();
            foreach ($cityLst as $key => $val) {
                $this->addDate(5,$val['id'],$val['grade'],$rate,$orderInfo['order_id'],$orderInfo['goods_id'],$orderInfo['product_id'],$orderInfo['order_amount'],$orderInfo['nums'], $avg_profit,$userInfo['id'],$userInfo['grade']);
            }  
        }
        
        //省级代理
        if($provinces>0){ 
            $rate = 0.4/100;
            $profit = bcmul($orderInfo['order_amount'], $rate, 2);
            $avg_profit = bcdiv($profit, $provinces, 2);  //省级代理销售平均奖励
            $proLst = $userModel->field("id,grade")->where(['area_id'=>$province_id])->select();
            foreach ($proLst as $key => $val) {
                $this->addDate(6,$val['id'],$val['grade'],$rate,$orderInfo['order_id'],$orderInfo['goods_id'],$orderInfo['product_id'],$orderInfo['order_amount'],$orderInfo['nums'], $avg_profit,$userInfo['id'],$userInfo['grade']);
            }  
        }
        return true;         

    }

    private function addDate($type,$receipt_id,$receipt_grade,$rate,$order_id,$goods_id,$product_id,$amount,$nums,$money,$contribute_id,$contribute_grade){

        $data = [
            'type'              => $type,
            'receipt_id'        => $receipt_id,
            'receipt_grade'     => $receipt_grade,
            'rate'              => $rate,
            'order_id'          => $order_id,
            'goods_id'          => $goods_id,
            'product_id'        => $product_id,
            'amount'            => $amount,
            'price'             => $amount,
            'nums'              => $nums,
            'money'             => $money,
            'contribute_id'     => $contribute_id,
            'contribute_grade'  => $contribute_grade,
            'ctime'             => time(),
        ];
        $this->insert($data);
        return $this->id;
    }

}
