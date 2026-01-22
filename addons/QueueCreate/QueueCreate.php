<?php
namespace addons\QueueCreate;	// 注意命名空间规范

use myxland\addons\Addons;
use think\Db;
use think\Exception;
use think\Queue;

/**
 * 创建think-queue任务的钩子
 */
class QueueCreate extends Addons
{
    // 该插件的基础信息
    public $info = [
        'name' => 'QueueCreate',	// 插件标识
        'title' => '创建think-queue任务',	// 插件名称
        'description' => '创建think-queue任务',	// 插件简介
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

    /**
     * 创建任务
     *
     * $jobName = '', $queueName = '', $params = []
     * @return void
     */
    public function queue($params)
    {
        $jobName 	= !empty($params['jobName']) ? $params['jobName'] : '';
		$queueName 	= !empty($params['queueName']) ? $params['queueName'] : 'default';
        $data       = !empty($params['data']) ? $params['data'] : '';

        // 1.当前任务将由哪个类来负责处理。
        //   当轮到该任务时，系统将生成一个该类的实例，并调用其 fire 方法
        $jobHandlerClassName  = $jobName;

        // 2.当前任务归属的队列名称，如果为新队列，会自动创建
        $jobQueueName  	  = $queueName;

        // 3.当前任务所需的业务数据 . 不能为 resource 类型，其他类型最终将转化为json形式的字符串
        //   ( jobData 为对象时，存储其public属性的键值对 )
        $jobData       	  = $data ;

        // 4.将该任务推送到消息队列，等待对应的消费者去执行
        $isPushed = Queue::push( $jobHandlerClassName , $jobData , $jobQueueName );

        // database 驱动时，返回值为 1|false  ;   redis 驱动时，返回值为 随机字符串|false

        if($isPushed  !== false ){
           return true;
        } else {
            recordLogs('queue', '创建任务失败，任务：'.$jobName.'，队列：'.$queueName);
            return false;
        }

    }
}
