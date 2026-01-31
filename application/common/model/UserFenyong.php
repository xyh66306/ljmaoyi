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
            ->where(['a.order_id'=>$order_id, 'c.pay_status'=>2, 'c.user_id'=>$user_id,'a.is_gift'=>2])
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
        $fatherInfo = [];
        if($userInfo['pid']>0){
            $fatherInfo = Db::name('user')->where(['id'=>$userInfo['pid']])->find();
        }

        $userGradeLog = new UserGradeLog();



        foreach($order_items as $key => $v) {


            if($v['goods_cat_id'] == 4 || $v['goods_cat_id'] == 1){
                if($userInfo['pid'] > 0 ){
                    $rate = 0;
                    if($fatherInfo['grade'] ==2){
                        $rate = 0.08;
                    }elseif($fatherInfo['grade'] ==3){
                        $rate = 0.12;
                    }
                    $profit = bcmul($v['payed'], $rate, 2);
                    $this->addDate(1,$fatherInfo['id'],$fatherInfo['grade'],$rate,$order_id,$v['goods_id'],$v['product_id'],$v['payed'],$v['nums'],$profit,$userInfo['id'],$userInfo['grade']);
                }
                //添加一个6返1标识
                $tags = "liuyi".$v['goods_id'];
                Db::name("order_items")->where(['id'=>$v['id']])->update(['is_fenyong'=>1,'tags'=>$tags]);
                $this->liufanyi($v,$tags);
            }

            if($v['goods_cat_id'] == 4){
                if($userInfo['grade'] == 1 || $v['price'] ==198){
                    $userGradeLog->authGrade($userInfo['id'],2);
                }elseif($userInfo['grade'] == 2 || $v['price'] ==1980){
                    $userGradeLog->authGrade($userInfo['id'],3);
                }
            }
        }   

    }
    
    /***
     * 六单购买返佣
     * $tags 六返一标识
     * $is_fenyong 1返佣2跳出
     * $used 1使用2未使用
     */
    public function liufanyi($v,$tags){

        #订单详细
        $orderItemsCount = Db::name('order_items')->alias('a')
            ->join('order c', 'c.order_id = a.order_id', 'LEFT')
            ->where(['a.is_fenyong'=>1,'a.is_gift'=>2,'a.is_gift'=>2,'a.tags'=>$tags,'c.used'=>1])
            ->field('a.*, c.user_id,c.ctime')
            ->order("c.ctime asc,a.id asc")
            ->count();
        if($orderItemsCount<6){
            return;
        }
        $contributer = Db::name('user')->where(['id'=>$v['user_id']])->find();
         //受益人
        $shouyierOrderInfo = Db::name('order_items')->alias('a')
            ->join('order c', 'c.order_id = a.order_id', 'LEFT')
            ->where(['a.is_fenyong'=>1,'a.is_gift'=>2,'a.is_gift'=>2,'a.tags'=>$tags,'c.used'=>1])
            ->field('a.*, c.user_id,c.ctime')
            ->order("c.ctime asc,a.id asc")
            ->find();      

        $shouyier = Db::name('user')->where(['id'=>$shouyierOrderInfo['user_id']])->find();

        $res = $this->addDate(3,$shouyier['id'],$shouyier['grade'],1,$v['order_id'],$v['goods_id'],$v['product_id'],$v['payed'],1,$v['payed'],$contributer['id'],$contributer['grade']);
        if($res){

            Db::name('order_items')->where(['id'=>$shouyierOrderInfo['id']])->update(['is_fenyong'=>2]);    //收益人订单修改成2

            $orderItemsCount = Db::name('order_items')->alias('a')
            ->join('order c', 'c.order_id = a.order_id', 'LEFT')
            ->where(['a.is_fenyong'=>1,'a.is_gift'=>2,'a.is_gift'=>2,'a.tags'=>$tags,'c.used'=>1])
            ->field('a.*, c.user_id,c.ctime')
            ->order("c.ctime asc,a.id asc")
            ->select();
            foreach($orderItemsCount as $key=>$val){ 
                Db::name('order_items')->where(['id'=>$val['id']])->update(['used'=>2]);        //公排订单座位修改成2
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


    /**
     * 代理统计
     * $area_id  代理ID
     * $money  金额
     */
    public function dailiTongji($area_id,$money){

        $nianyue = date('Y-m');

        $areaModel = new Area();
        $areaData = $areaModel->getParents($area_id);

        if(empty($areaData)){
            return;
        }
        $province_id = isset($areaData[0]['id']) ? $areaData[0]['id'] : null;
        $city_id = isset($areaData[1]['id']) ? $areaData[1]['id'] : null;
        $district_id = isset($areaData[2]['id']) ? $areaData[2]['id'] : null;
        if(!$province_id || !$city_id || !$district_id){
            return;
        }
        $this->addDailiTongji($district_id,$nianyue,$money);
        $this->addDailiTongji($city_id,$nianyue,$money);
        $this->addDailiTongji($province_id,$nianyue,$money);

    }

    private function addDailiTongji($area_id,$nianyue,$money){ 
        
        $info = Db::name("daili_tongji")->where(['area_id'=>$area_id,'nianyue'=>$nianyue])->find();
        $time = time();

        if($info){
            $money = bcadd($info['money'],$money,2);
            $data = [
                'money'         => $money,
                'utime'         => $time,
            ];
            Db::name("daili_tongji")->where(['id'=>$info['id']])->update($data);
        }else{
            $data = [
                'area_id'       => $area_id,
                'nianyue'       => $nianyue,
                'money'         => $money,
                'ctime'         => $time,
                'utime'         => $time,
            ];
            Db::name("daili_tongji")->insert($data);
        }

    }

}
