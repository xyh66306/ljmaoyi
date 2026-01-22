<?php
namespace addons\UserInvite;	// 注意命名空间规范

use myxland\addons\Addons;
use think\Db;
use app\common\model\User as UserModel;

/**
 * 用户自动升级会员等级插件
 */
class UserInvite extends Addons
{
    // 该插件的基础信息
    public $info = [
        'name' => 'UserInvite',	// 插件标识
        'title' => '用户邀请变动',	// 插件名称
        'description' => '用户邀请变动插件',	// 插件简介
        'status' => 0,	// 状态
        'author' => 'dwF',
        'version' => '1.0'
    ];

    /**
	 * 安装方法必须实现
	 */
	public function install(){
		return true;
	}

	/**
	 * 卸载方法必须实现
	 */
	public function uninstall(){
		return true;
	}

	/**
	*必须实现配置函数
	*/
	public function config(){

	}

    public function orderpayed(){
        return true;
    }

	/**
	 * 用户注册后增加用户邀请记录表数据
	 *
	 * @param string $user_id		//当前用户ID
	 * @param string $user_grade	//当前用户等级
	 * @param string $parent_id		//推荐人用户ID，父级
	 * @return void
	 */
	public function addUserInviteLog($params)
	{
		$user_id 	= empty($params['user_id']) ? 0 : $params['user_id'];
		$parent_id 	= empty($params['parent_id']) ? 0 : $params['parent_id'];


		if(empty($user_id)) {
			recordLogs('UserInvite', 'UID为空');
			return true;
		}


        Db::startTrans();
        try {
            $data = [];
            $userModel = new UserModel();

            $userInfo = $userModel->where(['id'=>$user_id])->find();


            #如果邀请表没有记录，但是注册时有父级，这是通过分享注册或者注册时填写推荐人的场景来的
            if (empty($userInfo) && $parent_id>0) {
                recordLogs('UserInvite', '用户不存在');
            } elseif (!empty($userInfo) && $parent_id==0) {

                #拼接所有父级
                $new_sparent_id         = 'A'.$user_id.'A';
                $userInfo->pid          = 0;
                $userInfo->sparent_id   = $new_sparent_id;
                $userInfo->save($data);

            } elseif (!empty($userInfo) && !empty($parent_id)) {

                #已注册过，通过邀请场景过来的
                #找出推荐人的所有父级
                $parent_pids = $userModel->where(['id'=>$parent_id])->value('sparent_id');
                if (empty($parent_pids)) {
                    recordLogs('UserInvite', 'UID: '.$user_id.' 的推荐人的邀请记录数据为空');
                    return true;
                }
                #拼接所有父级
                $new_sparent_id = $parent_pids . ',' . 'A'.$user_id.'A';
                $userInfo->pid          = $parent_id;
                $userInfo->sparent_id   = $new_sparent_id;
                $userInfo->save($data);

            }

            Db::commit();

        } catch (\Exception $e) {
            Db::rollback();
            recordLogs('UserInvite', 'UID: '.$user_id.' 添加邀请记录失败'.$e->getMessage());
        }
        $this->sonLst($user_id);
		return true;
	}


    public function sonLst($uid){

        $lists = Db::name("user")->field("id,pid")->where("pid",$uid)->select()->toArray();
         if(empty($lists)){
            return true;
        }
        foreach($lists as $k=>$v){
            $params['user_id'] = $v["id"];            
            $params['parent_id'] = $v["pid"]; 
            $this->addUserInviteLog($params);         
        }



    }


}
