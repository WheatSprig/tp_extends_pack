/*
Date: 2018-01-21 12:25:43
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for mk_log_service_queue
-- ----------------------------
DROP TABLE IF EXISTS `mk_log_service_queue`;
CREATE TABLE `mk_log_service_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(200) DEFAULT NULL,
  `args` text,
  `result` varchar(20) DEFAULT NULL,
  `error` varchar(100) DEFAULT NULL,
  `time` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
