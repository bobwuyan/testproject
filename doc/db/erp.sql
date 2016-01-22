/*
SQLyog Ultimate v8.32 
MySQL - 5.6.13 : Database - new_beubeu
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`new_beubeu` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `new_beubeu`;

/*Table structure for table `beu_brand` */

DROP TABLE IF EXISTS `beu_brand`;

CREATE TABLE `beu_brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '品牌名字',
  `englishname` varchar(255) DEFAULT NULL COMMENT '英文名',
  `logoimgpath` varchar(255) DEFAULT NULL COMMENT '品牌图片路径，http完整地址',
  `telephone` varchar(255) DEFAULT NULL COMMENT '电话',
  `address` varchar(255) DEFAULT NULL COMMENT '地址',
  `fax` varchar(255) DEFAULT NULL COMMENT '传真',
  `website` varchar(255) DEFAULT NULL COMMENT '网站地址，http完整',
  `companyname` varchar(255) DEFAULT NULL COMMENT '公司名',
  `model_type` varchar(20) NOT NULL COMMENT '简版后台默认包含模特',
  `status` tinyint(4) NOT NULL COMMENT '10为正常显示，11 为不显示,12为已删除',
  `createdate` datetime DEFAULT NULL COMMENT '创建时间',
  `code_start` int(11) NOT NULL,
  `code_end` int(11) NOT NULL,
  `angle` tinyint(1) NOT NULL DEFAULT '1' COMMENT '角度 默认为1只有正面 2为有背面',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=455 DEFAULT CHARSET=utf8 COMMENT='品牌表';

/*Table structure for table `beu_clothes` */

DROP TABLE IF EXISTS `beu_clothes`;

CREATE TABLE `beu_clothes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stylee` int(11) DEFAULT NULL COMMENT '风格',
  `season` int(11) DEFAULT NULL COMMENT '季节',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '衣服名',
  `brandid` int(11) NOT NULL COMMENT '品牌ID',
  `color` int(11) DEFAULT '0' COMMENT '颜色，对应beubeu_config  type=2是颜色',
  `colorimage` varchar(255) DEFAULT '' COMMENT '上传的颜色图片',
  `mbcolorimage` varchar(500) DEFAULT NULL,
  `level` int(11) DEFAULT '0' COMMENT '层次，beubeu_config ,type=3是层次',
  `clothescategory` int(11) DEFAULT '0' COMMENT '品类一级目录，对应beubeu_config表中type=8',
  `clothescategory2` int(11) DEFAULT '0' COMMENT '品类二级目录，对应beubeu_config表中type=4',
  `relatedclothes` int(11) DEFAULT '0' COMMENT '相关衣服，相同款式不同色彩，衣服ID号以逗号分隔',
  `differenttry` int(11) DEFAULT '0' COMMENT '不同穿法字段,衣服ID号以逗号分隔',
  `thumbnail` varchar(255) DEFAULT '' COMMENT '列表页显示图',
  `brandnumber` varchar(255) DEFAULT '' COMMENT '品牌衣服款色号,默认模特的款号',
  `brandnumber2` varchar(255) DEFAULT '' COMMENT '衣服款号2',
  `brandnumber3` varchar(255) DEFAULT '' COMMENT '衣服款号3',
  `material` int(11) DEFAULT '0' COMMENT '质地，对应category表中style=6，单选',
  `underwear` tinyint(4) DEFAULT '0' COMMENT '是否穿内衣，0是穿内衣，1是脱内衣',
  `underpants` int(11) DEFAULT '0' COMMENT '0为穿内裤，1为脱内裤',
  `modelgender` int(11) DEFAULT '0' COMMENT '模特类型,是否男女模.对应b_category style为9的.',
  `label` int(11) DEFAULT '0' COMMENT '标签属性ID，指新品等，对应b_category的style为10的类型',
  `foottype` int(11) DEFAULT '0' COMMENT '脚部类型，对应b_category中type15',
  `supportfoot` varchar(50) DEFAULT '' COMMENT '支持不同脚的ID，以逗号分隔，如果存在就表示该衣服支持该脚',
  `imagescontent` varchar(6000) DEFAULT '' COMMENT 'json格式{一维：customImagecontent：自定义图片（二维：{"customimage序列1-2  (三维：{customImagecontent序列1-50})}）、staticImagecontent：静态图（二维{staticImagecontent序列1-50}）、detailImagecontent：细节图（二维：{detailImagecontent序列1-50}）、graphicmodel：立体图{二维：graphicmodel序列1-50}、collocationmap：搭',
  `masks` text COMMENT '遮罩JSON参数',
  `price` float DEFAULT '0' COMMENT '价钱',
  `discountprice` float DEFAULT '0' COMMENT '折扣后价格',
  `date_add` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `video` varchar(500) DEFAULT '' COMMENT '视频json格式保存，{"touch":{"videourl":"","videoimage":"http://"},"ipad":{"videourl":"","videoimage":"http://"}}',
  `buyurl` varchar(1024) DEFAULT '' COMMENT '购买地址，也需序列化',
  `mask` text COMMENT '遮罩 json',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '条码',
  `specialclothes` text COMMENT '特殊衣服（neckline：领子 / back:后摆 / pants_tripped:裤绊 / side:侧面）',
  `status` int(11) DEFAULT '3' COMMENT '状态 0为添加衣服完毕、1为衣服图片上传完毕、2遮罩选择完毕、3未审核、8为已下架、9为综合管理、10上架但不在列表显示，11为已上架在列表显示',
  `singlespace` int(11) DEFAULT '0' COMMENT '空单 0.空单 1.一张图 2.二张图',
  `showneckline` int(11) DEFAULT '0' COMMENT '0为显示领子，1为不显示，默认为0显示领子',
  `cardigan` tinyint(4) DEFAULT '0' COMMENT '开衫组合,默认为0：否，1为是',
  `special` tinyint(4) DEFAULT '0' COMMENT '特殊组合默认为0：否，1为是',
  `square` varchar(150) NOT NULL DEFAULT '' COMMENT '单品方形图',
  `easy` tinyint(11) DEFAULT '0' COMMENT '宽松衣服默认为0：否，1为是',
  `longsleeve` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否长袖连身衣 默认1长袖 否则短袖',
  `codetobarcode` varchar(200) DEFAULT NULL COMMENT '条码标示，重命名软件用',
  `mask3` text COMMENT '遮罩图片地址 3.0版使用 保存正背面',
  `mask_type3` tinyint(1) DEFAULT '1' COMMENT '遮罩图片状态 3.0版使用 1遮罩图做显示处理 0做删除处理',
  `open_close` tinyint(1) NOT NULL DEFAULT '0' COMMENT '衣服属于开穿或闭合 0未设置 1开穿 2闭合',
  `tmall_line` varchar(200) DEFAULT NULL COMMENT '天猫卡线位置 clothes_line衣服卡线，sleeves_line袖长卡线，skirt_line裙卡线，pants_line裤卡线',
  `isSYorXY` tinyint(1) NOT NULL DEFAULT '0' COMMENT '判断是否上下衣0上衣 1下衣 2连衣裙',
  `clothespicchange` tinyint(1) NOT NULL DEFAULT '0' COMMENT '修改图片标识，字段临时添加，稍后可删除',
  PRIMARY KEY (`id`),
  KEY `brandid` (`brandid`),
  KEY `clothescategory` (`clothescategory`),
  KEY `clothescategory2` (`clothescategory2`),
  KEY `modelgender` (`modelgender`)
) ENGINE=InnoDB AUTO_INCREMENT=71223 DEFAULT CHARSET=utf8 COMMENT='衣服表';

/*Table structure for table `beu_user_config` */

DROP TABLE IF EXISTS `beu_user_config`;

CREATE TABLE `beu_user_config` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL COMMENT '权限名',
  `rand` int(2) NOT NULL COMMENT '权限级别，值越小权限越高 1为总管理员，2-4为管理，5-40为品牌，41为普通用户',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Table structure for table `beu_users` */

DROP TABLE IF EXISTS `beu_users`;

CREATE TABLE `beu_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '密码，注意是md5(''beubeu''.md5($password))，md5双重加密 ',
  `pwd` varchar(50) NOT NULL COMMENT '未加密的密码',
  `title` varchar(50) DEFAULT NULL COMMENT '用户别名',
  `permissions` varchar(512) DEFAULT NULL COMMENT '用户权限，JSON格式，实际结果是数组，只保存权限二级，对应beu_category 102',
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `endtime` timestamp NULL DEFAULT NULL COMMENT '到期时间',
  `type` tinyint(4) DEFAULT NULL COMMENT '用户类型，1为总管理员，2-4为管理，5-40为品牌，41为普通用户,7为子屏,8为天猫帐号',
  `sub_sum` tinyint(2) NOT NULL DEFAULT '0' COMMENT '最大开启子帐号数量 只有用户类型为5的帐号才有此功能',
  `brandid` varchar(255) DEFAULT NULL COMMENT '品牌ID，JSON数组一维格式，KEY无意义，VALUE为ID',
  `touchid` varchar(255) DEFAULT NULL COMMENT '触摸屏ID，JSON数组一维格式，KEY无意义，VALUE为ID',
  `account` int(11) DEFAULT NULL COMMENT '对应的账户类型 beu_useraccount.id',
  `istotalaccount` int(11) DEFAULT '0' COMMENT '是非品牌总管理 1.是 0.否',
  `title_index` varchar(555) DEFAULT NULL COMMENT '品牌后台首页title',
  `product_name` varchar(555) DEFAULT NULL COMMENT '首页-产品名称',
  `tabloid` text COMMENT '首页-产品摘要',
  `agreement_number` varchar(555) DEFAULT NULL COMMENT '首页-协议编号',
  `page_notes` varchar(555) DEFAULT NULL COMMENT '首页-首页备注',
  `ip_limit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否限制IP访问，1限制 0不限制 默认1',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '帐号是否禁用 默认1开启 否则禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=341 DEFAULT CHARSET=utf8;

/*Table structure for table `erp_brandcategory` */

DROP TABLE IF EXISTS `erp_brandcategory`;

CREATE TABLE `erp_brandcategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brandid` int(11) NOT NULL COMMENT '品牌编号',
  `name` varchar(100) NOT NULL COMMENT '分类名',
  PRIMARY KEY (`id`),
  KEY `FK_erp_brandcategory` (`brandid`),
  CONSTRAINT `FK_erp_brandcategory` FOREIGN KEY (`brandid`) REFERENCES `beu_brand` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `erp_clothes_image` */

DROP TABLE IF EXISTS `erp_clothes_image`;

CREATE TABLE `erp_clothes_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clothesid` int(11) NOT NULL DEFAULT '0' COMMENT '衣服ID',
  `clothesimageid` int(11) NOT NULL DEFAULT '0' COMMENT '衣服图片表的ID',
  `tablename` varchar(20) NOT NULL COMMENT '对应衣服图片表名',
  PRIMARY KEY (`id`),
  KEY `FK_erp_clothes_image` (`clothesid`),
  KEY `clothesimageid` (`clothesimageid`),
  CONSTRAINT `clothesimageid` FOREIGN KEY (`clothesimageid`) REFERENCES `erp_image` (`id`),
  CONSTRAINT `FK_erp_clothes_image` FOREIGN KEY (`clothesid`) REFERENCES `beu_clothes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `erp_clothes_order` */

DROP TABLE IF EXISTS `erp_clothes_order`;

CREATE TABLE `erp_clothes_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clothesid` int(11) NOT NULL COMMENT '衣服ID号',
  `brandnumber` varchar(25) NOT NULL COMMENT '衣服款号',
  `pcount` int(11) NOT NULL DEFAULT '0' COMMENT '产品图数量',
  `mcount` int(11) NOT NULL DEFAULT '0' COMMENT '模特图数量',
  `dcount` int(11) NOT NULL DEFAULT '0' COMMENT '细节图数量',
  `description` varchar(50) DEFAULT NULL COMMENT '备注',
  `orderid` int(11) DEFAULT NULL COMMENT '订单号',
  `brandcategoryid` int(11) NOT NULL DEFAULT '0' COMMENT '品牌自定义分类ID',
  PRIMARY KEY (`id`),
  KEY `FK_erp_clothes_order` (`orderid`),
  KEY `FK_erp_clothes_order33` (`clothesid`),
  KEY `categoryid` (`brandcategoryid`),
  CONSTRAINT `categoryid` FOREIGN KEY (`brandcategoryid`) REFERENCES `erp_brandcategory` (`id`),
  CONSTRAINT `FK_erp_clothes_order` FOREIGN KEY (`orderid`) REFERENCES `erp_order` (`id`),
  CONSTRAINT `FK_erp_clothes_order33` FOREIGN KEY (`clothesid`) REFERENCES `beu_clothes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `erp_image` */

DROP TABLE IF EXISTS `erp_image`;

CREATE TABLE `erp_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'dscn文件名',
  `status` int(11) NOT NULL COMMENT '审核状态：0为待审核、1为合格、2为不合格',
  `addtime` datetime NOT NULL COMMENT '图片上传时间',
  `updatetime` datetime DEFAULT NULL COMMENT '图片修改时间',
  `istop` tinyint(4) DEFAULT '0' COMMENT '主图：0为否，1为主图',
  `type` tinyint(4) DEFAULT NULL COMMENT '图片类型：0为灰模图、1为立体图、2为静态图、3为真人模特图、4为细节图',
  `url` varchar(100) DEFAULT NULL COMMENT '图片地址',
  `isshow` tinyint(4) DEFAULT NULL COMMENT '显示状态:0为显示，1为删除',
  `isback` tinyint(4) DEFAULT NULL COMMENT '正背面：0为正面，1为背面',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `erp_order` */

DROP TABLE IF EXISTS `erp_order`;

CREATE TABLE `erp_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordername` varchar(50) NOT NULL COMMENT '订单名',
  `description` varchar(100) DEFAULT NULL COMMENT '备注',
  `barcodecount` int(11) NOT NULL DEFAULT '0' COMMENT '发货量：一个订单包含的款号数量',
  `addtime` datetime NOT NULL COMMENT '添加时间',
  `orderlogid` int(11) DEFAULT NULL COMMENT '订单日志id号',
  PRIMARY KEY (`id`),
  KEY `FK_erp_order` (`orderlogid`),
  CONSTRAINT `FK_erp_order` FOREIGN KEY (`orderlogid`) REFERENCES `erp_orderlog` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `erp_orderlog` */

DROP TABLE IF EXISTS `erp_orderlog`;

CREATE TABLE `erp_orderlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderlog` text COMMENT '订单日志内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `erp_poster` */

DROP TABLE IF EXISTS `erp_poster`;

CREATE TABLE `erp_poster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '文件名',
  `url` varchar(255) NOT NULL COMMENT '文件地址',
  `addtime` int(11) NOT NULL COMMENT '上传日期',
  `brandid` int(11) NOT NULL COMMENT '品牌ID号',
  `status` int(11) NOT NULL COMMENT '0为可用，1为删除',
  PRIMARY KEY (`id`),
  KEY `FK_erp_poster` (`brandid`),
  CONSTRAINT `FK_erp_poster` FOREIGN KEY (`brandid`) REFERENCES `beu_brand` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
