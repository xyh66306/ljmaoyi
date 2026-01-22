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



    /**
     * 极差返佣
     * 邀请返佣
     * @param array $params
     * @return void
     */
    public function inviteRebateLog($order_id,$user_id)
    {

        $userModel              = new User();
        $Balance                = new Balance();
        #用户邀请信息
        $userInfo = $userModel->field("id,grade,pid,sparent_id,ident,area_id,ident_area_id")->where(['id'=>$user_id])->find();

        #订单详细
        $order_items = Db::name('order_items')->alias('a')
            ->join('order c', 'c.order_id = a.order_id', 'LEFT')
            ->join('goods g', 'g.id = a.goods_id', 'LEFT')
            ->where(['a.order_id'=>$order_id, 'c.pay_status'=>2, 'c.user_id'=>$user_id])
            ->where('c.payed', '>', 0)
            ->where('a.is_fenyong', 2)
            ->field('a.*, c.user_id, c.ctime as create_time,c.pay_status,c.payed,g.goods_cat_id,g.is_recommend,g.is_hot,g.is_parity')
            ->select()
            ->toArray();





        $billPaymentsRel = new BillPaymentsRel();
        $payment_id = $billPaymentsRel->where("source_id",$order_id)->value("payment_id");

        if(empty($order_items)) {
            return true;
        }


        #会员升级标识
        //总贡献值 =
        //is_parity 福利区
        //is_recommend 消费区
        //is_hot 会员区

        $userContributeLog = new UserContributeLog();
        $userPoint = new UserPointLog();

        Db::startTrans();
        try{
            $data = [];
            foreach($order_items as $key => $v) {
                
                //平价区不参与极差分润
                //上级分佣信息

                $rate_result = $this->getRebate($user_id, $userInfo);

                //按照地区奖励
                // $rate_result = $this->getFatherByArea($user_id, $userInfo);


                if(!empty($rate_result)) {
                    foreach($rate_result as $k2 => $v2) {

                        if(!$v2['user_id']){
                            continue;
                        }
                        if($v2['user_id']==$user_id){
                            continue;
                        }
                        // $profit = 0;
                        // if($v['price'] <= $v['costprice']) {
                        //     break;
                        // } else {
                        //     $profit = bcmul(bcmul(bcsub($v['price'], $v['costprice'], 2), ($v2['rate'] / 100), 2),$v['nums'],2);
                        //     if($profit==0){
                        //         continue;
                        //     }
                        // }


                        // $profit = bcmul(bcmul(bcsub($v['price'], $v['costprice'], 2), ($v2['rate'] / 100), 2),$v['nums'],2);
                        $profit = bcmul(bcmul($v['price'], ($v2['rate'] / 100), 2),$v['nums'],2);


                        if($profit==0){
                            continue;
                        }


                        $type = isset($v2['type']) ? $v2['type'] : 2;

                        $data[] = [
                            'type'              => $type,              #邀请分佣
                            'receipt_id'        => $v2['user_id'],     #收益人
                            'receipt_grade'     => empty($v2['grade']) ? 0 : $v2['grade'],       #收益人等级
                            'rate'              => $v2['rate'],         #收益人分享比例
                            'order_id'          => $v['order_id'],     #订单号
                            'goods_id'          => $v['goods_id'],     #商品ID
                            'product_id'        => $v['product_id'],   #产品ID
                            'amount'            => $v['amount'],       #交易总额
                            'price'             => $v['price'],        #产品销售价
                            'cust_price'        => $v['costprice'],    #产品成本价
                            'nums'              => $v['nums'],         #数量
                            'money'             => $profit,            #差价，分佣
                            'vip_price'         => $v['price'],        #购买者VIP价格
                            'contribute_id'     => $userInfo['id'],      #买家ID，贡献人
                            'contribute_grade'  => $userInfo['grade'], #贡献人等级
                            'ctime'             => $v['create_time'],   //time(),
                            'expire'            => 1
                        ];
                        $Balance->change($v2['user_id'],$Balance::TYPE_DISTRIBUTION,$profit,$payment_id);
                    }
                }

                $contribution = $v['amount'];
                $conRate      = 0;
                if($v['is_recommend'] == 1 && $v['is_parity'] == 2 && $v['is_hot'] == 2){
                    //消费区100%
                    $conRate      = config("wobeis.fugouRate");
                }elseif($v['is_recommend'] == 2 && $v['is_parity'] == 2 && $v['is_hot'] == 1){
                    //会员区50%
                    $conRate      = config("wobeis.baodanRate");
                } else if($v['is_recommend'] == 2 && $v['is_parity'] == 1 && $v['is_hot'] == 2){
                    //福利区 25%
                    $conRate      = config("wobeis.pinjiaRate");
                }
                if($contribution == 0){
                    return;
                }
                $ppids_arr = $this->getGxRebate($user_id, $userInfo,$contribution,$conRate);

                if($ppids_arr){
                    foreach($ppids_arr as $k3=>$v3){
                        if(!$v3['user_id']){
                            continue;
                        }
                        $data[] = [
                            'type'              => 1,                  #贡献值
                            'receipt_id'        => $v3['user_id'],     #收益人
                            'receipt_grade'     => $v3['grade'],       #收益人等级
                            'rate'              => $v3['rate'],         #收益人分享比例
                            'order_id'          => $v['order_id'],     #订单号
                            'goods_id'          => $v['goods_id'],     #商品ID
                            'product_id'        => $v['product_id'],   #产品ID
                            'amount'            => $v['amount'],       #交易总额
                            'price'             => $v['price'],        #产品销售价
                            'cust_price'        => $v['costprice'],    #产品成本价
                            'nums'              => $v['nums'],         #数量
                            'money'             => $v3['contribute'],  #贡献值
                            'vip_price'         => $v['price'],        #购买者VIP价格
                            'contribute_id'     => $userInfo['id'],      #买家ID，贡献人
                            'contribute_grade'  => $userInfo['grade'], #贡献人等级
                            'ctime'             => $v['create_time'],   //time(),
                            'expire'            => 1
                        ];
                        
                        // $userPoint->setPoint($v3['user_id'],$v3['contribute'],$userPoint::POINT_TYPE_REBATE);
                        $userContributeLog->setPoint($v3['user_id'],$v3['contribute']);
                        $contribute = Db::name('user')->where(['id' => $v3['user_id']])->value("contribute");
                        $this->authGrade($v3['user_id'],$contribute);
                    }
                }                
                //全国代理
                if($v['is_parity'] ==2){
                    $goldLst = $this->goldRebate($contribution);
                    if($goldLst){
                        foreach($goldLst as $v4){
                            if(!$v4['user_id']){
                                continue;
                            }
                            $data[] = [
                                'type'              => 3,                  #代理
                                'receipt_id'        => $v4['user_id'],     #收益人
                                'receipt_grade'     => $v4['grade'],       #收益人等级
                                'rate'              => $v4['rate'],         #收益人分享比例
                                'order_id'          => $v['order_id'],     #订单号
                                'goods_id'          => $v['goods_id'],     #商品ID
                                'product_id'        => $v['product_id'],   #产品ID
                                'amount'            => $v['amount'],       #交易总额
                                'price'             => $v['price'],        #产品销售价
                                'cust_price'        => $v['costprice'],    #产品成本价
                                'nums'              => $v['nums'],         #数量
                                'money'             => $v4['money'],        #贡献值
                                'vip_price'         => $v['price'],        #购买者VIP价格
                                'contribute_id'     => $userInfo['id'],      #买家ID，贡献人
                                'contribute_grade'  => $userInfo['grade'], #贡献人等级
                                'ctime'             => $v['create_time'],   //time(),
                                'expire'            => 1
                            ];
                            $Balance->change($v4['user_id'],$Balance::TYPE_DAILI,$v4['money'],$payment_id);
                        }
                    }
                }

            }

            if(!empty($data)) {
                $res = Db::name('user_fenyong')->insertAll($data);
            }

            if($res) {
                Db::name('order_items')->where('order_id', $order_id)->update(['is_fenyong' => 1]);
            }
            Db::commit();
        } catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            recordLogs("inviteRebateLog",$e->getMessage());
        }
        return true;
    }

    /***
     * 按地区
     */
    public function getFatherByArea($user_id, $userInfo){
       
      $result =  $data = [];  
      $lesseq = config("wobeis.lesseq");

      $invite_rebate = config("wobeis.invite_rebate");

      $areaModel = new Area();
      $city_id       = $areaModel->where("id",$userInfo['area_id'])->value("parent_id");
      $province_id   = $areaModel->where("id",$city_id)->value("parent_id");

    //   $areaInfo = get_area($userInfo['area_id']);

        $fatherInfo   = Db::name("user")->field("id,grade,pid")->where("id",$userInfo['pid'])->find();

        // 如果自身是省级代理
        if($userInfo['pid'] && $userInfo['grade']==6){
            $result[] =  $this->getInfoByPingji($userInfo,2);
        }

        // 如果自身是市级代理
        if($userInfo['grade']==5){

            $provinceInfo = $this->getinfoByAgent($province_id);
            if($fatherInfo && $fatherInfo['id'] != $provinceInfo['id']){
                $result[] = $this->getInfoByPingji($userInfo,2);          
            }elseif($provinceInfo){
                $data['user_id'] = $provinceInfo['id'];
                $data['grade']   = $provinceInfo['grade'];
                $data['rate']    = $invite_rebate['grade6'];
                $data['type']    = 4;
                $result[] = $data;
            }

        }

        // 如果自身是区县代理
        if($userInfo['grade']==4){

            $unRate = 0;
            $cityInfo = $this->getinfoByAgent($city_id);

            if($cityInfo){
                $data['user_id'] = $cityInfo['id'];
                $data['grade']   = $cityInfo['grade'];
                $data['rate']    = $invite_rebate['grade5'];
                $data['type']    = 4;
                $result[] = $data;
            } else {
                $unRate = $invite_rebate['grade5'];
            }    
            
            $provinceInfo = $this->getinfoByAgent($province_id);
            if($provinceInfo){
                $data['user_id'] = $provinceInfo['id'];
                $data['grade']   = $provinceInfo['grade'];
                $data['rate']    = $invite_rebate['grade6'] + $unRate;
                $data['type']    = 4;
                $result[] = $data;
            }

            if($fatherInfo && $fatherInfo['id'] != $provinceInfo['id'] && $fatherInfo['id'] != $cityInfo['id']){
                $result[] =  $this->getInfoByPingji($userInfo,2);
            }

        }        

        $fatherdata = [];


        // 如果自身是代理商
        if($userInfo['grade']==3){

            $unRate = 0;
            //父亲 
            // if($fatherInfo){
            //     $fatherdata['user_id'] = $fatherInfo['id'];
            //     $fatherdata['grade']   = $fatherInfo['grade'];
            //     if($fatherInfo['grade']==1){
            //         $fatherdata['rate']    = $lesseq;
            //         $unRate                =  $invite_rebate['grade3'] + $invite_rebate['grade2'];
            //     }elseif($fatherInfo['grade']==2){
            //         $fatherdata['rate']    = $lesseq;                    
            //         $unRate                = $invite_rebate['grade3'];
            //     }elseif($fatherInfo['grade']==3){
            //         $fatherdata['rate']    = $lesseq;
            //         $fatherdata['rate']    = $invite_rebate['grade3'];
            //     }elseif($fatherInfo['grade']==4){
            //         $fatherdata['rate']    =$invite_rebate['grade3']+$invite_rebate['grade4'];
            //     }elseif($fatherInfo['grade']==5){
            //         $fatherdata['rate']    = $invite_rebate['grade3']+$invite_rebate['grade4']+$invite_rebate['grade5'];
            //     }elseif($fatherInfo['grade']==6){
            //         $fatherdata['rate']    = $invite_rebate['grade3']+$invite_rebate['grade4']+$invite_rebate['grade5']+$invite_rebate['grade6'];
            //     }else{
            //         $fatherdata['rate']    = 0;
            //     }
            //     $result[] = $fatherdata;
            // }

            //地区代理
            $areaInfo = $this->getinfoByAgent($userInfo['area_id']);
            if($areaInfo){
                $data['user_id'] = $areaInfo['id'];
                $data['grade']   = $areaInfo['grade'];
                $data['rate']    = $invite_rebate['grade4'];
                $data['type']    = 4;
                $result[] = $data;
            } else {
                $unRate = $invite_rebate['grade4'];
            }     
            

            //市级代理
            $cityInfo = $this->getinfoByAgent($city_id);
            if($cityInfo){
                $data['user_id'] = $cityInfo['id'];
                $data['grade']   = $cityInfo['grade'];
                $data['rate']    = $invite_rebate['grade5'] + $unRate;
                $data['type']    = 4;
                $result[] = $data;
            } else{
                $unRate = $invite_rebate['grade5'] + $unRate;
            }             
            //省级代理
            $provinceInfo = $this->getinfoByAgent($province_id);
            if($provinceInfo){
                $data['user_id'] = $provinceInfo['id'];
                $data['grade']   = $provinceInfo['grade'];
                $data['rate']    = $invite_rebate['grade6'] + $unRate;
                $data['type']    = 4;
                $result[] = $data;
            }

            if($fatherInfo && $fatherInfo['id'] != $provinceInfo['id'] && $fatherInfo['id'] !=$cityInfo['id'] && $fatherInfo['id'] != $areaInfo['id']){
                $result[] =  $this->getInfoByPingji($userInfo,2);
            }

        } 
        
        // 如果自身是健康大使
        if($userInfo['grade']==2){
            $unRate = 0;
            //父亲 
            if($fatherInfo){
                $fatherdata['user_id'] = $fatherInfo['id'];
                $fatherdata['grade']   = $fatherInfo['grade'];
                $fatherdata['type']    = 2;
                if($fatherInfo['grade']==1){
                    $fatherdata['rate']    = $lesseq;
                    $unRate                =  $invite_rebate['grade3'] + $invite_rebate['grade2']-$lesseq;
                }elseif($fatherInfo['grade']==2){
                    $fatherdata['rate']    = $lesseq;                    
                    $unRate                = $invite_rebate['grade3'];
                }elseif($fatherInfo['grade']==3){
                    $fatherdata['rate']    = $invite_rebate['grade3'];
                }elseif($fatherInfo['grade']==4){
                    $fatherdata['rate']    =$invite_rebate['grade3']+$invite_rebate['grade4'];
                }elseif($fatherInfo['grade']==5){
                    $fatherdata['rate']    = $invite_rebate['grade3']+$invite_rebate['grade4']+$invite_rebate['grade5'];
                }elseif($fatherInfo['grade']==6){
                    $fatherdata['rate']    = $invite_rebate['grade3']+$invite_rebate['grade4']+$invite_rebate['grade5']+$invite_rebate['grade6'];
                }else{
                    $fatherdata['rate']    = 0;
                }                
                $result[] = $fatherdata;
            }

 
            //地区代理
            if($fatherdata['grade']<4){
                $areaInfo = $this->getinfoByAgent($userInfo['area_id']);
                if($areaInfo){
                    $data['user_id'] = $areaInfo['id'];
                    $data['rate']    = $invite_rebate['grade4'];
                    $data['grade']   = $areaInfo['grade'];
                    $data['type']    = 4;
                    $result[] = $data;
                    $unRate = 0;
                } else {
                    $unRate = $invite_rebate['grade4'] + $unRate;
                } 
            }

            //市级代理
            if($fatherdata['grade']<5){
                $cityInfo = $this->getinfoByAgent($city_id);
                if($cityInfo){
                    $data['user_id'] = $cityInfo['id'];
                    $data['grade']   = $cityInfo['grade'];
                    $data['rate']    = $invite_rebate['grade5'] + $unRate;
                    $data['type']    = 4;
                    $result[] = $data;
                    $unRate = 0;
                } else{
                    $unRate = $invite_rebate['grade5'] + $unRate;
                } 
            }  

            //省级代理
            if($fatherdata['grade']<6){
                $provinceInfo = $this->getinfoByAgent($province_id);
                if($provinceInfo){
                    $data['user_id'] = $provinceInfo['id'];
                    $data['grade']   = $provinceInfo['grade'];
                    $data['rate']    = $invite_rebate['grade6'] + $unRate;
                    $data['type']    = 4;
                    $result[] = $data;
                }
            }
        }

        // 如果自身是普通会员
        if($userInfo['grade']==1){
            $unRate = 0;
            //父亲
            if($fatherInfo){
                $fatherdata['user_id'] = $fatherInfo['id'];
                $fatherdata['grade']   = $fatherInfo['grade'];
                $fatherdata['type']    = 2;
                if($fatherInfo['grade']==1){
                    $fatherdata['rate']    = config("wobeis.v0Reward");
                    $unRate                = $invite_rebate['grade2'] - config("wobeis.v0Reward");
                }elseif($fatherInfo['grade']==2){
                    $fatherdata['rate']    = $invite_rebate['grade2'];
                    // $unRate             =  $invite_rebate['grade3'];
                }elseif($fatherInfo['grade']==3){
                    $fatherdata['rate']    = $invite_rebate['grade2']+$invite_rebate['grade3'];
                }elseif($fatherInfo['grade']==4){
                    $fatherdata['rate']    = $invite_rebate['grade2']+$invite_rebate['grade3']+$invite_rebate['grade4'];
                }elseif($fatherInfo['grade']==5){
                    $fatherdata['rate']    = $invite_rebate['grade2']+$invite_rebate['grade3']+$invite_rebate['grade4']+$invite_rebate['grade5'];
                }elseif($fatherInfo['grade']==6){
                    $fatherdata['rate']    = $invite_rebate['grade2']+$invite_rebate['grade3']+$invite_rebate['grade4']+$invite_rebate['grade5']+$invite_rebate['grade6'];
                }else{
                    $fatherdata['rate']    = 0;
                }
                $result[] = $fatherdata;

                if($fatherInfo['pid'] && $fatherInfo['grade']==2){

                    $ppfather = Db::name("user")->field("id,grade,pid")->where("id",$fatherInfo['pid'])->find();

                    if($ppfather['grade']==3){
                        $ppfatherData['rate']    = $invite_rebate['grade3'];
                        $ppfatherData['user_id'] = $ppfather['id'];
                        $ppfatherData['grade'] = $ppfather['grade'];
                        $ppfatherData['type']    = 2;
                        $unRate                  = 0;
                        $result[] = $ppfatherData;
                    } else {
                        $unRate                  = $unRate + $invite_rebate['grade3'];
                    }
                }
            }

            //地区代理
            if($fatherdata['grade']<4){
                $areaInfo = $this->getinfoByAgent($userInfo['area_id']);
                if($areaInfo){
                    $data['user_id'] = $areaInfo['id'];
                    $data['grade']   = $areaInfo['grade'];
                    $data['rate']    = $invite_rebate['grade4'] + $unRate;
                    $data['type']    = 4;
                    $result[] = $data;
                    $unRate = 0;
                } else {
                    $unRate = $invite_rebate['grade4'] + $unRate;
                } 
            }


            //市级代理
            if($fatherdata['grade']<5){
                $cityInfo = $this->getinfoByAgent($city_id);
                if($cityInfo){
                    $data['user_id'] = $cityInfo['id'];
                    $data['grade']   = $cityInfo['grade'];
                    $data['rate']    = $invite_rebate['grade5'] + $unRate;
                    $data['type']    = 4;
                    $result[] = $data;
                    $unRate = 0;
                } else{
                    $unRate = $invite_rebate['grade5'] + $unRate;
                } 
            }
  

            //省级代理
            if($fatherdata['grade']<6){
                $provinceInfo = $this->getinfoByAgent($province_id);
                if($provinceInfo){
                    $data['user_id'] = $provinceInfo['id'];
                    $data['grade']   = $provinceInfo['grade'];
                    $data['rate']    = $invite_rebate['grade6'] + $unRate;
                    $data['type']    = 4;
                    $result[] = $data;
                }

            }
        }
        return $result;

    }


    
    //获取所有父亲列表
    public function getFatherByLevelLst($user_id,$userInfo,$maxlevel){
        $lists = $this->getFatherLst($user_id,$userInfo,true);
        $newResult = [];
        $maxLeveled = 0;
        foreach($lists as $k=>$v){
            $info = Db::name("user")->field("id,pid,grade")->where("id",$v)->find();
            if($info['grade']>$maxLeveled){
                $maxLeveled = $info['grade'];
                $newResult[$k]['user_id'] = $info['id'];
                $newResult[$k]['grade'] = $info['grade'];
            }
        }
        return $newResult;
    }


    public function getInfoByPingji($userInfo,$type=''){
        $lesseq = config("wobeis.lesseq");
        $recomReward = config("wobeis.recomReward");
        $fatherInfo = Db::name("user")->field("id,pid,grade")->where("id",$userInfo['pid'])->find();
        $data['user_id'] = $fatherInfo['id'];
        $data['grade']   = $fatherInfo['grade'];
        $data['rate']    = $fatherInfo['grade']>$userInfo['grade'] ? $recomReward : $lesseq;
        $data['type']    = $type;
        return $data;
    }

    public function getinfoByAgent($cityId){

        $info = Db::name("user")->field("id,pid,grade")->where("ident_area_id",$cityId)->find();
        return $info;
    }


    //获取所有父亲列表
    public function getFatherLst($user_id,$userInfo,$isremove){

        #递归其上级都应获得什么奖励
        $userInfo['sparent_id'] = str_replace('A', '', $userInfo['sparent_id']);
        $parent_arr = explode(',', $userInfo['sparent_id']);
        if($isremove){
            unset($parent_arr[array_search($user_id, $parent_arr)]);    #去除自己
        }
        krsort($parent_arr);
        return $parent_arr;        
    }


    /***
     * 
     * $contribution 贡献值
     */
    public function getGxRebate($user_id, $userInfo,$contribution,$rate){

        $parent_arr = $this->getFatherLst($user_id,$userInfo,false);

        if(!$parent_arr){
            return;
        }
        Db::startTrans();
        try{
            $parent_str = implode(',', $parent_arr);
            $list = Db::query("SELECT `id`,`pid`,`grade`,`sparent_id` FROM `wb_user` WHERE `id` IN (".$parent_str.") ORDER BY field(id, ".$parent_str.")");
            if(empty($list)) {
                return false;
            }
            $first_result = [];
            foreach($list as $key => $v) {
                $newcontribution = sprintf("%.2f", $contribution * $rate/100);
                $first_result[$key]['user_id']  = $v['id'];
                $first_result[$key]['grade']    = $v['grade'];
                $first_result[$key]['rate']     = $rate;
                $first_result[$key]['contribute'] = $newcontribution;
                if($rate==1){
                    break;
                }
                $rate         = sprintf("%.2f", $rate/2);
                if($rate < 2){
                    $rate = 1;
                }
            } 
            Db::commit();
        } catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            recordLogs("getGxRebate",$e->getMessage());
        }
        return $first_result;   
    }

    /**
     * 无限极，获取方式
     * 获取用户自己和上级的各自分佣
     */
    public function getRebate($user_id, $userInfo)
    {



        $first_result =[];
        $parent_arr = $this->getFatherLst($user_id,$userInfo,false);
        if(!$parent_arr){
            return;
        }

        $userModel = new User();
        $father = $userModel->where("id",$userInfo['pid'])->find();

        if(!$father){
            return $first_result;
        }        
        $first_result[0]['user_id']    = $father['id'];
        $first_result[0]['grade']      = $father['grade'];
        $first_result[0]['rate']       = 30;
        return $first_result;


        try{
            $parent_str = implode(',', $parent_arr);
            $list = Db::query("SELECT `id`,`pid`,`grade`,`sparent_id` FROM `wb_user` WHERE `id` IN (".$parent_str.") ORDER BY field(id, ".$parent_str.")");
            if(empty($list)) {
                return false;
            }

            $frconfig   = config('wobeis.invite_rebate');
            $lesseq     = config('wobeis.lesseq');
            $result     = [];


            foreach($list as $key => $v) {
                $first_result = [];
                #判断自己购买
                if($v['id'] == $userInfo['id']) {
                    continue;
                    $first_result['user_id']    = $v['id'];
                    $first_result['grade']      = $v['grade'];
                    #判断自己的等级，获得相应的优惠百分比
                    switch($v['grade']) {
                        case 1:#粉丝
                            $first_result['rate'] = $frconfig['grade1'];
                            break;
                        case 2:#VIP
                            $first_result['rate'] = $frconfig['grade2'];
                            break;
                        case 3:#VIP2
                            $first_result['rate'] = bcadd( $frconfig['grade2'], $frconfig['grade3'], 2);
                            break;
                        case 4:
                            $first_result['rate'] = bcadd( bcadd($frconfig['grade2'], $frconfig['grade3'], 2), $frconfig['grade4'], 2);
                            break;
                        case 5:
                            $first_result['rate'] = $frconfig['grade2']+$frconfig['grade3']+$frconfig['grade4']+$frconfig['grade5'];
                            break; 
                        case 6:
                            $first_result['rate'] = $frconfig['grade2']+$frconfig['grade3']+$frconfig['grade4']+$frconfig['grade5']+$frconfig['grade6'];
                            break;                                                       
                    }
                    $result[] = $first_result;
                 } else {
                    #判断循环的上级是否为用户直属上级
                    if($v['id'] == $userInfo['pid']) {
                        $diffGrade = $v['grade'] - $userInfo['grade'];
                        if($diffGrade<=0){
                            # 直属上级小于等于自己等级
                            $first_result['user_id']    = $v['id'];
                            $first_result['grade']      = $v['grade'];
                            $first_result['rate']       = $lesseq;
                        }elseif($diffGrade==1){
                            $first_result['user_id']    = $v['id'];
                            $first_result['grade']      = $v['grade'];
                            $first_result['rate']       = $frconfig['grade'.$v['grade']];
                        }elseif($diffGrade==2){
                            $first_result['user_id']    = $v['id'];
                            $first_result['grade']      = $v['grade'];
                            $first_result['rate']       = bcadd($frconfig['grade'.$v['grade']], $frconfig['grade'.($v['grade']-1)], 2);
                        }elseif($diffGrade==3){
                            $first_result['user_id']    = $v['id'];
                            $first_result['grade']      = $v['grade'];
                            $first_result['rate']       = bcadd( bcadd($frconfig['grade'.$v['grade']], $frconfig['grade'.($v['grade']-1)], 2), $frconfig['grade'.($v['grade']-2)], 2);
                        }elseif($diffGrade==4){
                            # 直属上级比自己等级高3级
                            $first_result['user_id']    = $v['id'];
                            $first_result['grade']      = $v['grade'];                            
                            $first_result['rate']       = $frconfig['grade'.$v['grade']] + $frconfig['grade'.($v['grade']-1)] + $frconfig['grade'.($v['grade']-2)] + $frconfig['grade'.($v['grade']-3)];
                        }elseif($diffGrade==5){
                            $first_result['user_id']    = $v['id'];
                            $first_result['grade']      = $v['grade'];
                            $first_result['rate']       = $frconfig['grade'.$v['grade']] + $frconfig['grade'.($v['grade']-1)] + $frconfig['grade'.($v['grade']-2)] + $frconfig['grade'.($v['grade']-3)]+ $frconfig['grade'.($v['grade']-4)];
                        }
                        if(!empty($first_result)) {
                            $result[] = $first_result;
                        }
                    } else {
                        #循环的上级不是用户直属上级
                        #不是自己，判断上级等级是否小于自己的等级，小于不算（间隔）
                        if($v['grade'] <= $userInfo['grade']) {
                            continue;
                        }

                        #获取结果中已出现的最大key(等级)
                        if(!empty($result)) {
                            $grade_arr = array_column($result, 'grade');
                            $grade_count = array_count_values($grade_arr);#等级出现的次数
                            $grade_key = array_keys($grade_count);
                            $max_grade = max($grade_key);
                        } else {
                            $max_grade = 0;
                        }

                        switch($v['grade'] - $max_grade) {
                            case 0:
                                $first_result['user_id']    = $v['id'];
                                $first_result['grade']      = $v['grade'];
                                $first_result['rate']       = 0;
                                break;
                            case 1:
                                # 上级与结果中出现的最大等级高1级
                                if($v['grade'] == 2) {
                                    $first_result['user_id']    = $v['id'];
                                    $first_result['grade']      = $v['grade'];
                                    $first_result['rate']       = $frconfig['grade'.$v['grade']];

                                } else {
                                    if(empty($grade_count[$v['grade']])) {
                                        #当前上级等级未出现，第一次时
                                        $first_result['user_id']    = $v['id'];
                                        $first_result['grade']      = $v['grade'];
                                        $first_result['rate']       = $frconfig['grade'.$v['grade']];

                                    } else if(!empty($grade_count[$v['grade']]) && $grade_count[$v['grade']] == 1) {
                                        $first_result['user_id']    = $v['id'];
                                        $first_result['grade']      = $v['grade'];
                                        $first_result['rate']       = $frconfig['grade'.$v['grade'].'-1'];

                                    } else if(!empty($grade_count[$v['grade']]) && $grade_count[$v['grade']] == 2) {
                                        $first_result['user_id']    = $v['id'];
                                        $first_result['grade']      = $v['grade'];
                                        $first_result['rate']       = empty($frconfig['grade'.$v['grade'].'-2']) ? 0 : $frconfig['grade'.$v['grade'].'-2'];
                                    } else {
                                        $first_result['user_id']    = $v['id'];
                                        $first_result['grade']      = $v['grade'];
                                        $first_result['rate']       = 0;
                                    }
                                }
                                break;
                            case 2:
                                # 上级与结果中出现的最大等级高2级
                                if(empty($grade_count[$v['grade']])) {
                                    #当前上级等级未出现，第一次时
                                    $first_result['user_id']    = $v['id'];
                                    $first_result['grade']      = $v['grade'];
                                    $first_result['rate']       = $frconfig['grade'.$v['grade']]+$frconfig['grade'.($v['grade']-1)];
                                }
                                break;
                            case 3:
                                # 上级与结果中出现的最大等级高3级
                                if(empty($grade_count[$v['grade']])) {
                                    #当前上级等级未出现，第一次时
                                    $first_result['user_id']    = $v['id'];
                                    $first_result['grade']      = $v['grade'];
                                    $first_result['rate']       = $frconfig['grade'.$v['grade']]+$frconfig['grade'.($v['grade']-1)]+$frconfig['grade'.($v['grade']-2)];

                                }
                                break;
                            case 4:
                                # 上级与结果中出现的最大等级高4级
                                if(empty($grade_count[$v['grade']])) {
                                    #当前上级等级未出现，第一次时
                                    $first_result['user_id']    = $v['id'];
                                    $first_result['grade']      = $v['grade'];
                                    $first_result['rate']       = $frconfig['grade'.$v['grade']]+$frconfig['grade'.($v['grade']-1)]+$frconfig['grade'.($v['grade']-2)]+$frconfig['grade'.($v['grade']-3)];

                                }
                                break;  
                            case 5:
                                # 上级与结果中出现的最大等级高4级
                                if(empty($grade_count[$v['grade']])) {
                                    #当前上级等级未出现，第一次时
                                    $first_result['user_id']    = $v['id'];
                                    $first_result['grade']      = $v['grade'];
                                    $first_result['rate']       = $frconfig['grade'.$v['grade']]+$frconfig['grade'.($v['grade']-1)]+$frconfig['grade'.($v['grade']-2)]+$frconfig['grade'.($v['grade']-3)]+$frconfig['grade'.($v['grade']-4)];

                                }
                                break;   
                            case 6:
                                # 上级与结果中出现的最大等级高4级
                                if(empty($grade_count[$v['grade']])) {
                                    #当前上级等级未出现，第一次时
                                    $first_result['user_id']    = $v['id'];
                                    $first_result['grade']      = $v['grade'];
                                    $first_result['rate']       = $frconfig['grade'.$v['grade']]+$frconfig['grade'.($v['grade']-1)]+$frconfig['grade'.($v['grade']-2)]+$frconfig['grade'.($v['grade']-3)]+$frconfig['grade'.($v['grade']-4)]+$frconfig['grade'.($v['grade']-5)];

                                }
                                break;                                                                         
                        }
                        if(!empty($first_result)) {
                            $result[] = $first_result;
                        }
                    }
                } 
            }
        } catch (\Exception $e){
            recordLogs("getRebate",$e->getMessage());
        }
        return $result;
    }


    //城市管理员
    public function goldRebate($amount){

       $lists = Db::name("user")->field("id,grade")->where("ident",1)->select()->toArray(); 
       $count = Db::name("user")->where("ident",1)->count("id"); 
       if($count==0){
        return;
       }
       $money = $amount * 0.01/$count;
       if($money<0.01){
        return;
       }
       $first_result = [];

       foreach($lists as $k=>$v){
            $first_result[$k]['user_id']    = $v['id'];
            $first_result[$k]['grade']      = $v['grade'];
            $first_result[$k]['rate']       = 1;
            $first_result[$k]['money']      = $money;
       }
       return $first_result;

    }


    //判断是否符合升级标准
    public function authGrade($user_id,$contribute){


        $userModel = new User();
        $userInfo = $userModel->where('id',$user_id)->find();
        if($userInfo['grade']>=3){
            return;
        }

        //取所有用户等级信息
        $userGradeModel = new UserGrade();
        $list = $userGradeModel->order('id asc')->cache(3600)->select();

        //会员升级
        $id = 0;
        foreach($list as $v){
            if($v['money'] > 0 && $v['money']<= $contribute){
                $id = $v['id'];
            }
        }

        $data['grade'] = $id;
        // if($contribute>1000000 && $userInfo['grade']==2){
        //     $data['ident'] = 1;
        // }
        if($userInfo['grade']< $id){
            $userModel->save($data,['id'=>$userInfo['id']]);

            $userGradeModelLog = new UserGradeLog();
            $params["contribute"] = $userInfo['contribute'];
            $userGradeModelLog->addGradeLog($userInfo['id'],$userInfo['grade'],$id,1,2,$params);
        
        }
        

    }




}
