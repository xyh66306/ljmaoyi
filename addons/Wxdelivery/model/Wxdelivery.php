<?php

namespace addons\Wxdelivery\model;

use app\common\model\Common;
use org\Curl;
use org\Wx;
use think\Db;

class Wxdelivery extends Common
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'ctime';
    protected $updateTime = 'utime';

    public function toSend($id){
        $return = [
            'status' => false,
            'data' => '',
            'msg' => ''
        ];
        $where = [
            ['id','=', $id],
            ['status','in', [1,3]]
        ];
        $info = $this->where($where)->find();
        if(!$info){
            $return['msg'] = "没有查到对应数据";
            return $return;
        }

        $url = "https://api.weixin.qq.com/wxa/sec/order/upload_shipping_info?access_token=";
        $res = $this->curl($url, json_decode($info['senddata']));
        $re = json_decode($res, true);

        if($re['errcode'] == 0){
            $info->status = 2;
            $return['status'] = true;
            $return['msg'] = "提交成功";
        }else{
            $info->status = 3;
            if(isset($re['errmsg'])){
                $return['msg'] = $re['errmsg'];
            }else{
                $return['msg'] = "提交失败";
                $return['data'] = $re;
            }
        }
        $info->redata = $res;
        $info->save();
        
        return $return;
    }

    private function curl($url, $data){
        //去提交审核
        $wx = new Wx();
        //没有去官方请求生成
        $wx_appid = getSetting('wx_appid');
        $wx_app_secret = getSetting('wx_app_secret');
        $accessToken = $wx->getAccessToken($wx_appid, $wx_app_secret);
        if(!$accessToken)
        {
            $return['msg'] = "accessToken获取失败";
            return $return;
        }
        $curl = new Curl();
        $url = $url.$accessToken;


        $data = json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $res = $curl->post($url, $data);
        //$res = '{"errcode":0,"audit_id":"RQAAAFTv79ABQBZgFvfCJA"}';
        //校验，如果正确了，就更新状态为审核中，如果
        //$re = json_decode($res, true);

        return $res;
    }
    
}
