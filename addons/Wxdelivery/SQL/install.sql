CREATE TABLE IF NOT EXISTS `jshop_wxdelivery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(20) NOT NULL,
  `delivery_id` varchar(20) NOT NULL,
  `status` tinyint(2) NOT NULL COMMENT '状态，1待提交，2提交成功，3提交失败',
  `senddata` text NOT NULL,
  `redata` text NOT NULL,
  `ctime` bigint(20) NOT NULL,
  `utime` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信发货单表';
