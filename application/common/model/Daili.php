<?php
/*
 * @Author: Xyhao
 * @Date: 2026-01-31 17:37:42
 * @Description: 安徽爱喜网络科技有限公司
 */

namespace app\common\model;

class Daili extends Common
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'ctime';
    protected $updateTime = 'utime';
    protected $table = 'daili_tongji';
    protected $pk = 'id';

    
    /**
     * 返回表格数据类型
     * @param $post
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function tableData($post)
    {
        if(isset($post['limit'])){
            $limit = $post['limit'];
        }else{
            $limit = config('paginate.list_rows');
        }
        $tableWhere = $this->tableWhere($post);

        $list = $this->with('user,promotion')->where($tableWhere['where'])->order($tableWhere['order'])->paginate($limit);
        $data = $this->tableFormat($list->getCollection());         //返回的数据格式化，并渲染成table所需要的最终的显示数据类型
        $re['code'] = 0;
        $re['msg'] = '';
        $re['count'] = $list->total();
        $re['data'] = $data;
        return $re;
    }

}