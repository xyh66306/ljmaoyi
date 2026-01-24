<?php
namespace app\api\controller;


use app\common\model\Goods;
use app\common\model\GoodsCat;
use app\common\model\GoodsExtendCat;
use app\common\controller\Api;
use app\common\model\User;
use think\Db;
use think\facade\Request;

class ShopIndex extends Api
{



    //分类导航
    public function goodslist(){

      $cid        = input('cid', '');
      $hykcid     = input('hykcid', '');
      $goodsType  = input('goodsType', '');
      $page       = input('page',1);
      $pageSize   = input('limit',10);
      $token      = input('token','');
      $user_id    = getUserIdByToken($token);


      $order      = input('sort', 'sort asc,price asc,id desc');
      $GoodsModel = new Goods();

      $map = [];
      $map[] = ['marketable','=',1];
      $map[] = ['is_nomal_virtual','=',1];

      $whereor=[];

      if($cid){
          $goodsCat = new GoodsCat();
          $catids = $goodsCat->getChildrenIds($cid);


          if($catids){
              $strCatid = trim($cid.$catids,',');

              $map[] = ['goods_cat_id','in',$strCatid];
              $goodsExtendCat = new GoodsExtendCat();

              $catarr = explode(',',$strCatid);
              $goods_ids = $goodsExtendCat->getGoodsIdByCat($catarr, true);
              if($goods_ids){
                  $goods_ids = $cid.','.$goods_ids;
                  $whereor[] = ['id','in',$goods_ids];
              }
          }else{
              $map[] = ['goods_cat_id','=',$cid];
          }



      }

      if($hykcid && $hykcid>0){
          $map[] = ['hyk_category1LevelId','eq',$hykcid];
      }

//        $map[] = ['goods_cat_id','neq',1];

      if($goodsType){
          if($goodsType==0){
              $map[] = ['is_recommend','=',1];
              $order = "sort asc,price asc,id desc";
          } elseif ($goodsType==1){
              $order = "sort asc,price asc,id desc";
          } elseif ($goodsType==2){
              $map[] = ['is_hot','=',1];
          }elseif($goodsType==5){
              $map[] = ['brand_id','>=',1];
              $map[] = ['brand_id','<>',16];
              $map[] = ['brand_id','<>',18];
              $order = "sort asc,price asc,id desc";
          } elseif($goodsType==6){
              $map[] = ['is_koubei','=',1];
          }elseif($goodsType==8){
              $map[] = ['is_recommend','=',1];
              $order = "sort asc,buy_count desc,id desc";
          }elseif($goodsType==11){
              $order = "buy_count desc,id desc";
          }
      }



      $recomlist = $GoodsModel->where($map)
          ->order($order)
          ->page($page,$pageSize)
          ->select();

      $count = $GoodsModel->where($map)->count();

      $userModel = new User();
      // $grade = $userModel->where('id',$user_id)->value("grade");
      $price_zd = "price";    
      // if($grade==1){
      //   $price_zd = "price";
      // }elseif($grade==2){
      //   $price_zd = "vip1_price";
      // }elseif($grade==3){
      //   $price_zd = "vip2_price";
      // }elseif($grade==4){
      //   $price_zd = "vip4_price";
      // }  

      foreach ($recomlist as $k=>$v){
          if(mb_strlen($v['name'])>11){
              $v['name'] = mb_substr($v['name'],0,11);
          }
          $recomlist[$k]['image_url'] = _sImage($v['image_id']);

          // if($v['vip4_price']>0){
          //   $recomlist[$k]['reduce_price']  = bcsub($v['price'],$v['vip4_price'],0);
          // }else{
          //   $recomlist[$k]['reduce_price']  = 0;
          // }
          $product = Db::name("products")->where("goods_id",$v['id'])->find();

          $getPriceArr = $GoodsModel->getPrice($product,$user_id);

          $recomlist[$k]['reduce_price']  = 0;
          // $recomlist[$k]['price']     = floatval($v[$price_zd]);
          $recomlist[$k]['price']     = floatval($getPriceArr['price']);
          $recomlist[$k]['mktprice']  = floatval($v['mktprice']);
          $recomlist[$k]['buy_count']  = bcadd($v['buy_count'],$v['soud_out']);
          unset($v['costprice']);
      }

      $result['status'] = true;
      $result['data'] = ['lists'=>$recomlist,'count'=>$count];
      $result['msg'] = '获取成功';
      return $result;
  }   





   /***
     * 体验活动
     */

     public function screen(){

        $result = [
          'status' => false,
          'msg' => '获取成功',
          'data' => []
        ];
        $cid = input("cid","");
        $page = input("page",1);
        $limit = input("limt",10);
        $where['marketable'] = 1;
        $where['goods_cat_id'] = 13;
        // $where['is_nomal_virtual'] = 1;
        // if($cid){
        //   $where['goods_cat_id'] = $cid;
        // }
        $lists = Db::name("goods")->field("id,image_id,name,price,brief,mktprice")->where($where)->order("sort asc,id desc")->page($page,$limit)->select()->toArray();
      
        $count = Db::name("goods")->field("id,image_id,name,price,brief,mktprice")->where($where)->count();
  
  
        if(empty($lists)){
          return $result;
        }
  
        $data = [];
        $desc = "";
        $token      = input('token','');
        $user_id    = getUserIdByToken($token);

        $GoodsModel = new Goods();

        foreach($lists as $k=>$v){

          $product = Db::name("products")->where("goods_id",$v['id'])->where("is_defalut",1)->find();
          $getPriceArr = $GoodsModel->getPrice($product,$user_id);

          if(isset($v['image_id'])){
            $v['image_url'] = _sImage($v['image_id']);
          }        
          $v['price']     = floatval($getPriceArr['price']);
          $v['mktprice']  = floatval($v["mktprice"]);
          // $desc     =  Db::name("products")->where(['goods_id'=>$v['id'],'is_defalut'=>1])->value("spes_desc");   
  
          if(!empty($product['spes_desc'])){
            $arr = explode(':', $product['spes_desc']);
            $v['title'] = $arr[1];
          } else {
            $v['title'] = "";
          }
  
          $data[$k] = $v;
        }
  
        $result['data']['list']   = $data;
        $result['data']['count']   = $count;
        $result['status'] = true;
        
        return $result;
  
      } 
  


}