<?php
/*
 * @Author: Xyhao
 * @Date: 2026-01-24 17:57:16
 * @Description: 安徽爱喜网络科技有限公司
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
        $userInfo->viptime  = time();
        $userInfo->save();

        return true;
    }
}
