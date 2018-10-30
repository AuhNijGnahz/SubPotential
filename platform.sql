-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Oct 30, 2018 at 08:51 AM
-- Server version: 5.6.38
-- PHP Version: 7.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `cqpplatform`
--

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
-- Indexes for table `ca_user`
--
ALTER TABLE `ca_user`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ca_user`
--
ALTER TABLE `ca_user`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户uid', AUTO_INCREMENT=3;
