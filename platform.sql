-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Oct 31, 2018 at 08:53 AM
-- Server version: 5.6.38
-- PHP Version: 7.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `cqpplatform`
--

-- --------------------------------------------------------

--
-- Table structure for table `ca_active`
--

CREATE TABLE `ca_active` (
  `id` int(11) NOT NULL,
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `email` int(11) DEFAULT NULL COMMENT '邮箱状态 0未验证 1已验证',
  `phone` int(11) DEFAULT NULL COMMENT '手机状态 0未验证 1已验证',
  `recover` varchar(50) DEFAULT NULL COMMENT '找回密码code',
  `createtime` datetime DEFAULT NULL COMMENT '验证码生成时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_admin`
--

CREATE TABLE `ca_admin` (
  `uid` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `level` int(11) NOT NULL COMMENT '1-管理员 2-超级管理员',
  `lastlogindate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ca_admin`
--

INSERT INTO `ca_admin` (`uid`, `username`, `password`, `level`, `lastlogindate`) VALUES
(1, 'admin', 'admin', 2, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ca_announce`
--

CREATE TABLE `ca_announce` (
  `id` int(11) NOT NULL,
  `title` varchar(45) DEFAULT NULL,
  `content` text,
  `status` varchar(45) DEFAULT NULL,
  `author` varchar(45) DEFAULT NULL,
  `pubdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_buyrecord`
--

CREATE TABLE `ca_buyrecord` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `subname` varchar(50) NOT NULL COMMENT '商品名',
  `method` varchar(20) NOT NULL COMMENT '支付方式 cash/credit',
  `type` int(10) DEFAULT NULL COMMENT '购买类型 0新购/1续费',
  `price` float(9,2) NOT NULL COMMENT '支付价格',
  `purchasetime` datetime NOT NULL COMMENT '支付时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_cards`
--

CREATE TABLE `ca_cards` (
  `id` int(11) NOT NULL,
  `card` varchar(50) NOT NULL COMMENT '充值卡号',
  `cash` float(9,2) NOT NULL COMMENT '人民币额度',
  `credit` int(11) NOT NULL COMMENT '积分额度',
  `expiretime` datetime NOT NULL COMMENT '过期时间',
  `uid` int(11) DEFAULT NULL COMMENT '使用者uid',
  `usetime` datetime DEFAULT NULL COMMENT '使用时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_chargerecord`
--

CREATE TABLE `ca_chargerecord` (
  `id` int(11) NOT NULL,
  `orderid` varchar(50) NOT NULL COMMENT '订单号',
  `uid` int(11) NOT NULL COMMENT '用户iD',
  `method` varchar(20) NOT NULL COMMENT '支付方式',
  `type` varchar(20) NOT NULL COMMENT '购买类型 cash充值 未来增加更多',
  `count` float(9,2) NOT NULL COMMENT '订单金额',
  `status` int(11) NOT NULL COMMENT '状态 1已支付 0未支付',
  `date` datetime NOT NULL COMMENT '订单创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_coupon`
--

CREATE TABLE `ca_coupon` (
  `id` int(11) NOT NULL,
  `coupon` varchar(50) NOT NULL COMMENT '券代码',
  `sid` varchar(20) NOT NULL COMMENT '绑定订阅产品，all全部可用',
  `discount` float(3,2) NOT NULL COMMENT '折扣 0.01~0.99 (0.1折~9.9折)',
  `begintime` datetime NOT NULL COMMENT '生效时间',
  `expiretime` datetime NOT NULL COMMENT '过期时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_loginrecord`
--

CREATE TABLE `ca_loginrecord` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT 'uid',
  `logindate` datetime NOT NULL COMMENT '登录时间',
  `ip` varchar(20) NOT NULL COMMENT '登录Ip地址',
  `location` varchar(50) NOT NULL COMMENT '地址'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_mysubscribe`
--

CREATE TABLE `ca_mysubscribe` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT '用户uid',
  `sid` int(11) NOT NULL COMMENT '产品id',
  `sname` varchar(50) NOT NULL COMMENT '产品名',
  `status` varchar(10) NOT NULL COMMENT '状态 正常/停用 管理员专用',
  `groupnum` int(12) DEFAULT NULL COMMENT '群号',
  `starttime` datetime NOT NULL COMMENT '购买时间',
  `expiretime` datetime NOT NULL COMMENT '过期时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_price`
--

CREATE TABLE `ca_price` (
  `id` int(11) NOT NULL,
  `sid` varchar(10) NOT NULL COMMENT '产品Id',
  `pname` varchar(20) NOT NULL COMMENT '方案名',
  `method` varchar(10) NOT NULL COMMENT '支付类型 余额/积分',
  `status` varchar(10) NOT NULL COMMENT '状态 启用/停用',
  `price` float(9,2) NOT NULL COMMENT '价格',
  `time` int(11) NOT NULL COMMENT '天数',
  `comment` varchar(20) DEFAULT NULL COMMENT '备注'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_purchasemethod`
--

CREATE TABLE `ca_purchasemethod` (
  `id` int(11) NOT NULL,
  `mname` varchar(50) NOT NULL,
  `secureid` text NOT NULL COMMENT '支付接口ID',
  `securekey` text NOT NULL COMMENT '支付接口秘钥',
  `thirdkey` text COMMENT '第三方参数值（如有赞云店铺ID）'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ca_purchasemethod`
--

INSERT INTO `ca_purchasemethod` (`id`, `mname`, `secureid`, `securekey`, `thirdkey`) VALUES
(1, '有赞云支付', '9da7b85373086ce5fd', '7046f2195fc6d69919c6609c55b315db', '40575835');

-- --------------------------------------------------------

--
-- Table structure for table `ca_regsetting`
--

CREATE TABLE `ca_regsetting` (
  `id` int(11) NOT NULL,
  `regallow` int(11) NOT NULL COMMENT '开放注册 0开放/1邀请/2关闭',
  `groupid` int(11) NOT NULL COMMENT '用户组ID',
  `emailcheck` int(11) NOT NULL COMMENT '邮箱验证 0启用 1不启用',
  `showpolicy` int(11) NOT NULL COMMENT '显示条款 0显示/1不显示',
  `policyname` varchar(50) DEFAULT NULL COMMENT '条款文件名',
  `logincap` int(11) NOT NULL COMMENT '登录验证码 0显示、1不显示',
  `regcap` int(11) NOT NULL COMMENT '注册验证码 0显示、1不显示',
  `findcap` int(11) NOT NULL COMMENT '找回密码验证码 0显示、1不显示'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ca_regsetting`
--

INSERT INTO `ca_regsetting` (`id`, `regallow`, `groupid`, `emailcheck`, `showpolicy`, `policyname`, `logincap`, `regcap`, `findcap`) VALUES
(1, 0, 1, 0, 0, 'policy.html', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ca_settings`
--

CREATE TABLE `ca_settings` (
  `id` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `subtitle` varchar(50) DEFAULT NULL,
  `url` varchar(50) DEFAULT NULL COMMENT '网站URL',
  `seo` varchar(50) DEFAULT NULL,
  `sitedesc` varchar(100) DEFAULT NULL,
  `qq` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ca_settings`
--

INSERT INTO `ca_settings` (`id`, `title`, `subtitle`, `url`, `seo`, `sitedesc`, `qq`) VALUES
(1, 'SubPotential', '一站式管理平台', 'http://localhost:8080', '紫旭,机器人,qq机器人,SubPotential', '帮助您在线构建您的订阅管理', '644752622');

-- --------------------------------------------------------

--
-- Table structure for table `ca_sublist`
--

CREATE TABLE `ca_sublist` (
  `sid` int(11) NOT NULL COMMENT '产品id',
  `sname` varchar(50) NOT NULL COMMENT '产品名',
  `des` text NOT NULL COMMENT '描述',
  `skey` varchar(50) NOT NULL COMMENT '秘钥',
  `renewdiscount` float(3,2) NOT NULL COMMENT '续费折扣',
  `status` varchar(10) NOT NULL COMMENT '状态 启用/停用',
  `bindgroup` varchar(10) NOT NULL COMMENT '绑定QQ群 是/否',
  `adddate` datetime NOT NULL COMMENT '添加时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_templet`
--

CREATE TABLE `ca_templet` (
  `id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL COMMENT '类型',
  `content` text NOT NULL COMMENT '邮件内容'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ca_templet`
--

INSERT INTO `ca_templet` (`id`, `type`, `content`) VALUES
(1, 'verifyemail', '<div style=\"margin: -15px; padding: 8vh 0 2vh;color: #a6aeb3; background-color: #f7f9fa; text-align: center; font-family:NotoSansHans-Regular,\'Microsoft YaHei\',Arial,sans-serif; -webkit-font-smoothing: antialiased;\"><div style=\"width: 750px; max-width: 85%; margin: 0 auto; background-color: #fff; -webkit-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);-moz-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);\"><div style=\"text-align: center; padding: 50px 10%; font-size: 16px; line-height: 16px;\">{webtitle}</div><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\"><img src=\"{url}/static/assets/images/warn.png\" style=\"width: 15%;\"></h1><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\">您好！</h1><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\">您申请了邮箱验证，请点击下方的按钮进行验证</p><div><a href=\"{activelink}\" style=\"display: inline-block;    margin-bottom: 0; font-weight: normal; text-align: center; vertical-align: middle; cursor: pointer; background-image: none; border: 1px solid #35c8e6; white-space: nowrap; padding: 7px 0; font-size: 16px; line-height: 2; border-radius: 3px; min-width: 128px; color: #fff; background-color: #35C8E6; -webkit-user-select: none; -moz-user-select: none; user-select: none; outline: none; text-decoration: none;\" target=\"_blank\">立即验证</a></div><div style=\"width: 500px; max-width: 90%;margin: 110px auto; font-size: 14px;\"><div style=\"margin: 8px 0;\">如果按钮无效，请将以下链接复制到浏览器地址栏完成激活。</div><div><a href=\"{activelink}\" style=\"color: #35c8e6; word-break: break-all\" target=\"_blank\">{activelink}</a></div><div><br></div><div>验证链接2小时内有效！</div></div><div style=\"padding-bottom: 40px;font-size: 14px;\"><div style=\"padding-bottom: 40px;font-size: 14px;\">如果您没有进行该操作，请忽略此邮件！</div></div></div></div>'),
(2, 'register', '<div style=\"margin: -15px; padding: 8vh 0 2vh;color: #a6aeb3; background-color: #f7f9fa; text-align: center; font-family:NotoSansHans-Regular,\'Microsoft YaHei\',Arial,sans-serif; -webkit-font-smoothing: antialiased;\"><div style=\"width: 750px; max-width: 85%; margin: 0 auto; background-color: #fff; -webkit-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);-moz-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);\"><div style=\"text-align: center; padding: 50px 10%; font-size: 16px; line-height: 16px;\">{webtitle}</div><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\"><img src=\"{url}/static/assets/images/smile.png\" style=\"width: 15%;\"></h1><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\">{username}，欢迎您！</h1><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\">感谢您的加入，您现在只需最后一步就可以完成您的注册啦！</p><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\">请点击下方的按钮进行</p><div><a href=\"{activelink}\" style=\"display: inline-block;    margin-bottom: 0; font-weight: normal; text-align: center; vertical-align: middle; cursor: pointer; background-image: none; border: 1px solid #35c8e6; white-space: nowrap; padding: 7px 0; font-size: 16px; line-height: 2; border-radius: 3px; min-width: 128px; color: #fff; background-color: #35C8E6; -webkit-user-select: none; -moz-user-select: none; user-select: none; outline: none; text-decoration: none;\" target=\"_blank\">激活</a></div><div style=\"width: 500px; max-width: 90%;margin: 110px auto; font-size: 14px;\"><div style=\"margin: 8px 0;\">如果按钮无效，请将以下链接复制到浏览器地址栏完成激活。</div><div><a href=\"{activelink}\" style=\"color: #35c8e6; word-break: break-all\" target=\"_blank\">{activelink}</a></div><div><br></div><div>激活链接2小时内有效！</div></div><div style=\"padding-bottom: 40px;font-size: 14px;\"><div style=\"padding-bottom: 40px;font-size: 14px;\">如果您没有进行该操作，请忽略此邮件！</div></div></div></div>'),
(3, 'charge', '<div style=\"margin: -15px; padding: 8vh 0 2vh;color: #a6aeb3; background-color: #f7f9fa; text-align: center; font-family:NotoSansHans-Regular,\'Microsoft YaHei\',Arial,sans-serif; -webkit-font-smoothing: antialiased;\"><div style=\"width: 750px; max-width: 85%; margin: 0 auto; background-color: #fff; -webkit-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);-moz-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);\"><div style=\"text-align: center; padding: 50px 10%; font-size: 16px; line-height: 16px;\">{webtitle}</div><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\"><img src=\"{url}/static/assets/images/ok.png\" style=\"width: 18%;\"></h1><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\">充值成功！</h1><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\">您通过{method}充值的￥{count} 已经成功到账，您目前的可用现金余额为 ￥{cash}</p><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\">真诚感谢您的支持</p><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\"><br></p><div style=\"padding-bottom: 40px;font-size: 14px;\"><div style=\"padding-bottom: 40px;font-size: 14px;\">如果您没有进行该操作，请忽略此邮件！</div></div></div></div>'),
(4, 'subscribe', '<div style=\"margin: -15px; padding: 8vh 0 2vh;color: #a6aeb3; background-color: #f7f9fa; text-align: center; font-family:NotoSansHans-Regular,\'Microsoft YaHei\',Arial,sans-serif; -webkit-font-smoothing: antialiased;\"><div style=\"width: 750px; max-width: 85%; margin: 0 auto; background-color: #fff; -webkit-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);-moz-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);\"><div style=\"text-align: center; padding: 50px 10%; font-size: 16px; line-height: 16px;\">{webtitle}</div><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\"><img src=\"{url}/static/assets/images/ok.png\" style=\"width: 18%;\"></h1><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\">感谢您的购买！</h1><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\">感谢您最近在{webtitle}上的购买</p><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\"><span style=\"font-size: 1rem;\">以下产品已经加入到您的订阅列表中</span></p><p style=\"text-align: center; width: 750px; max-width: 90%; margin: 32px auto; padding: 0px;\">&nbsp;&nbsp;&nbsp;&nbsp;{subname} - {pricename}</p><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\"><br></p><div style=\"padding-bottom: 40px;font-size: 14px;\"><div style=\"padding-bottom: 40px;font-size: 14px;\">如果您没有进行该操作，请忽略此邮件！</div></div></div></div>'),
(5, 'recovery', '<div style=\"margin: -15px; padding: 8vh 0 2vh;color: #a6aeb3; background-color: #f7f9fa; text-align: center; font-family:NotoSansHans-Regular,\'Microsoft YaHei\',Arial,sans-serif; -webkit-font-smoothing: antialiased;\"><div style=\"width: 750px; max-width: 85%; margin: 0 auto; background-color: #fff; -webkit-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);-moz-box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);box-shadow: 0 2px 16px 0 rgba(118,133,140,0.22);\"><div style=\"text-align: center; padding: 50px 10%; font-size: 16px; line-height: 16px;\">{webtitle}</div><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\"><img src=\"{url}/static/assets/images/warn.png\" style=\"width: 15%;\"></h1><h1 style=\"margin:32px auto; max-width: 95%; color: #0e2026;\">您好！</h1><p style=\"width: 750px; max-width: 90%; margin: 32px auto;padding: 0;\">您申请了找回密码，请点击下方的按钮进行验证</p><div><a href=\"{activelink}\" style=\"display: inline-block;    margin-bottom: 0; font-weight: normal; text-align: center; vertical-align: middle; cursor: pointer; background-image: none; border: 1px solid #35c8e6; white-space: nowrap; padding: 7px 0; font-size: 16px; line-height: 2; border-radius: 3px; min-width: 128px; color: #fff; background-color: #35C8E6; -webkit-user-select: none; -moz-user-select: none; user-select: none; outline: none; text-decoration: none;\" target=\"_blank\">立即验证</a></div><div style=\"width: 500px; max-width: 90%;margin: 110px auto; font-size: 14px;\"><div style=\"margin: 8px 0;\">如果按钮无效，请将以下链接复制到浏览器地址栏完成验证。</div><div><a href=\"{activelink}\" style=\"color: #35c8e6; word-break: break-all\" target=\"_blank\">{activelink}</a></div><div><br></div><div>验证链接2小时内有效！</div></div><div style=\"padding-bottom: 40px;font-size: 14px;\"><div style=\"padding-bottom: 40px;font-size: 14px;\">如果您没有进行该操作，请忽略此邮件！</div></div></div></div>');

-- --------------------------------------------------------

--
-- Table structure for table `ca_ticket`
--

CREATE TABLE `ca_ticket` (
  `tid` varchar(20) NOT NULL COMMENT '工单编号',
  `uid` int(11) NOT NULL COMMENT '发布者UID',
  `tname` varchar(50) NOT NULL COMMENT '工单标题',
  `phone` varchar(11) NOT NULL COMMENT '手机号码',
  `status` int(11) NOT NULL COMMENT '工单状态 0等待分配 1已受理 2处理中 3待您处理 4待您评价 5已结束',
  `rate` int(11) DEFAULT NULL COMMENT '评价 1~5星 0尚未评价',
  `agent` int(11) DEFAULT NULL COMMENT '接单的客服UID',
  `createtime` datetime NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_ticketreply`
--

CREATE TABLE `ca_ticketreply` (
  `id` int(11) NOT NULL,
  `tid` varchar(20) NOT NULL COMMENT '关联工单ID',
  `uid` int(11) NOT NULL COMMENT '回复者用户ID',
  `content` text NOT NULL COMMENT '回复内容',
  `type` int(11) NOT NULL COMMENT '类型 0用户回复 1管理员回复',
  `replytime` datetime NOT NULL COMMENT '回复时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ca_ticketsetting`
--

CREATE TABLE `ca_ticketsetting` (
  `id` int(11) NOT NULL,
  `uid` text NOT NULL COMMENT '可以处理工单的UID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ca_ticketsetting`
--

INSERT INTO `ca_ticketsetting` (`id`, `uid`) VALUES
(1, '39,42');

-- --------------------------------------------------------

--
-- Table structure for table `ca_ugroup`
--

CREATE TABLE `ca_ugroup` (
  `id` int(11) NOT NULL,
  `groupname` varchar(50) NOT NULL COMMENT '用户组名',
  `groupcolor` varchar(20) DEFAULT NULL COMMENT '用户组颜色',
  `groupdiscount` float(9,2) NOT NULL COMMENT '专享折扣',
  `changeusernameprice` int(11) NOT NULL COMMENT '修改用户名价格'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ca_ugroup`
--

INSERT INTO `ca_ugroup` (`id`, `groupname`, `groupcolor`, `groupdiscount`, `changeusernameprice`) VALUES
(1, '普通用户', '', 1.00, 25),
(2, '高级用户', '#CD00CD', 0.95, 6);

-- --------------------------------------------------------

--
-- Table structure for table `ca_user`
--

CREATE TABLE `ca_user` (
  `uid` int(11) NOT NULL COMMENT '用户uid',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(50) NOT NULL COMMENT '密码',
  `email` varchar(50) NOT NULL COMMENT '邮箱',
  `avatar` text NOT NULL COMMENT '头像链接',
  `phone` int(11) DEFAULT NULL COMMENT '手机号',
  `cash` float(9,2) NOT NULL COMMENT '余额',
  `credit` int(11) NOT NULL COMMENT '积分',
  `groupid` int(11) NOT NULL COMMENT '用户组ID',
  `expiretime` datetime NOT NULL COMMENT '用户组到期时间',
  `referee` int(11) DEFAULT NULL COMMENT '推荐人',
  `regdate` datetime NOT NULL COMMENT '注册时间',
  `lastlogindate` datetime DEFAULT NULL COMMENT '最后登录时间',
  `status` int(11) NOT NULL COMMENT '状态 0正常1未验证邮箱 2封禁'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ca_active`
--
ALTER TABLE `ca_active`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_admin`
--
ALTER TABLE `ca_admin`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `ca_announce`
--
ALTER TABLE `ca_announce`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_buyrecord`
--
ALTER TABLE `ca_buyrecord`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_cards`
--
ALTER TABLE `ca_cards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_chargerecord`
--
ALTER TABLE `ca_chargerecord`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_coupon`
--
ALTER TABLE `ca_coupon`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_loginrecord`
--
ALTER TABLE `ca_loginrecord`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_mysubscribe`
--
ALTER TABLE `ca_mysubscribe`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_price`
--
ALTER TABLE `ca_price`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_purchasemethod`
--
ALTER TABLE `ca_purchasemethod`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_regsetting`
--
ALTER TABLE `ca_regsetting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_settings`
--
ALTER TABLE `ca_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_sublist`
--
ALTER TABLE `ca_sublist`
  ADD PRIMARY KEY (`sid`),
  ADD UNIQUE KEY `sid` (`sid`);

--
-- Indexes for table `ca_templet`
--
ALTER TABLE `ca_templet`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_ticket`
--
ALTER TABLE `ca_ticket`
  ADD PRIMARY KEY (`tid`);

--
-- Indexes for table `ca_ticketreply`
--
ALTER TABLE `ca_ticketreply`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_ticketsetting`
--
ALTER TABLE `ca_ticketsetting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_ugroup`
--
ALTER TABLE `ca_ugroup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ca_user`
--
ALTER TABLE `ca_user`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ca_active`
--
ALTER TABLE `ca_active`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ca_admin`
--
ALTER TABLE `ca_admin`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ca_announce`
--
ALTER TABLE `ca_announce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ca_buyrecord`
--
ALTER TABLE `ca_buyrecord`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ca_cards`
--
ALTER TABLE `ca_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ca_chargerecord`
--
ALTER TABLE `ca_chargerecord`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ca_coupon`
--
ALTER TABLE `ca_coupon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ca_loginrecord`
--
ALTER TABLE `ca_loginrecord`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ca_mysubscribe`
--
ALTER TABLE `ca_mysubscribe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ca_price`
--
ALTER TABLE `ca_price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ca_purchasemethod`
--
ALTER TABLE `ca_purchasemethod`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ca_templet`
--
ALTER TABLE `ca_templet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ca_ticketreply`
--
ALTER TABLE `ca_ticketreply`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ca_ugroup`
--
ALTER TABLE `ca_ugroup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ca_user`
--
ALTER TABLE `ca_user`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户uid', AUTO_INCREMENT=3;
