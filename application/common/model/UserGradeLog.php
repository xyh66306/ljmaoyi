<?php
/**
 * Created by 安徽正一沃古有限公司.
 * User: XYH
 * Date: 2021-07-19
 * Time: 15:41
 */

namespace app\common\model;


class UserGradeLog extends Common
{

    //时间自动存储
    protected $autoWriteTimestamp = true;
    protected $createTime = 'ctime';


    public function authGrade($uid,$grade){
        
        $user               = new  User();
        $userInfo           =  $user->get($uid);
        if($userInfo->grade >= $grade){
            return false;
        }
        if($userInfo->grade == 1 && $grade==2){
            $userInfo->grade = $grade;
        }
        if($userInfo->grade <= 2 && $grade==3){
            $userInfo->grade = $grade;
        }
        if($userInfo->grade <= 3 && $grade==4){
            $userInfo->grade = $grade;
        }        
        $this->addGradeLog($uid,$userInfo->grade,$grade,1);
        return true;    


    }

    //添加等级记录
    public function addGradeLog($uid, $old_grade,$current_grade,$type,$params=[])
    {
        $data['user_id']            = $uid;
        $data['old_grade']          = $old_grade;
        $data['current_grade']      = $current_grade;
        $data['endtime']            = '';
        $data['params']             = json_encode($params);
        $data['type']               = $type;
         $this->save($data);

        $user               = new  User();
        $userInfo           =  $user->get($uid);
        $userInfo->grade    = $current_grade;
        $userInfo->save();

        return true;
    }

    /***
     * V1 8%
     * V2 9%
     * V3 10%
     * L1 12%
     * $gradeMoney 会员升级金额
     */
    public function rewards($uid,$gradeMoney){

        $userModel               = new  User();

        $userInfo           =  $userModel->get($uid);

        if($userInfo->pid==0){
            return false;
        }
        //获取父亲信息
        $fatherInfo              = $userModel->get($userInfo->pid);
        if(empty($fatherInfo)){
            return false;
        }


        $jiangli = 0;
        $rate = 0;
        if($fatherInfo->grade == 2){
            $rate = 0.07;
        } elseif($fatherInfo->grade == 3){
            $rate = 0.08;
        } elseif($fatherInfo->grade == 4){
            $rate = 0.9;
        } elseif($fatherInfo->grade == 5){
            $rate = 0.10;
        }
        $jiangli = $gradeMoney*$rate;
        if($jiangli>0){
            $balanceModel = new Balance();
            $balanceModel->change($fatherInfo->id,$balanceModel::TYPE_SHARERECHARGE,$jiangli,$uid);
        }


        //获取爷爷信息
        if($fatherInfo->pid==0){
            return true;
        }
        $grandInfo              = $userModel->get($fatherInfo->pid);
        if(empty($grandInfo)){
            return true;
        }
        if($grandInfo->grade <= $fatherInfo->grade){
            return true;           
        }

        $grand_rate = 0;
        if($grandInfo->grade == 2){
            $grand_rate = 0.07-$rate;
        } elseif($grandInfo->grade == 3){
            $grand_rate = 0.08-$rate;
        } elseif($grandInfo->grade == 4){
            $grand_rate = 0.09-$rate;
        }elseif($fatherInfo->grade == 5){
            $grand_rate = 0.10-$rate;
        }
        if($grand_rate<=0){
            return true;
        }
        $jiangli = $gradeMoney*$grand_rate;
        if($jiangli>0){
            $balanceModel->change($grandInfo->id,$balanceModel::TYPE_SHARERECHARGE,$jiangli,$uid);
        }



        //获取祖父信息
        if($grandInfo->pid==0){
            return true;
        }
        $zufuInfo              = $userModel->get($grandInfo->pid);
        if(empty($zufuInfo)){
            return true;
        }
        if($zufuInfo->grade <= $grandInfo->grade){
            return true;           
        }

        $zf_rate = 0;
        if($zufuInfo->grade == 2){
            $zf_rate = 0.07-$rate -$grand_rate;
        } elseif($zufuInfo->grade == 3){
            $zf_rate = 0.08-$rate  -$grand_rate;
        } elseif($zufuInfo->grade == 4){
            $zf_rate = 0.09-$rate   -$grand_rate;
        }elseif($zufuInfo->grade == 5){
            $zf_rate = 0.10-$rate - $grand_rate;
        }
        if($zf_rate<=0){
            return true;
        }
        $jiangli = $gradeMoney*$zf_rate;
        if($jiangli>0){
            $balanceModel->change($zufuInfo->id,$balanceModel::TYPE_SHARERECHARGE,$jiangli,$uid);
        }        

        return true;
    }


}
