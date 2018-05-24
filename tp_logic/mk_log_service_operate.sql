

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for mk_log_service_operate
-- ----------------------------
DROP TABLE IF EXISTS `mk_log_service_operate`;
CREATE TABLE `mk_log_service_operate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(30) DEFAULT NULL COMMENT '流水号',
  `class` varchar(100) DEFAULT NULL COMMENT '执行的类的名称',
  `function` varchar(50) DEFAULT NULL COMMENT '方法名',
  `error` varchar(100) DEFAULT NULL COMMENT '错误详情',
  `ip` varchar(20) DEFAULT NULL,
  `args` text COMMENT '请求参数',
  `result` text COMMENT '返回参数',
  `run_time` varchar(15) DEFAULT NULL COMMENT '运行时间',
  `time` varchar(20) DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='操作日志表';
