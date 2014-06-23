/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50508
Source Host           : localhost:3306
Source Database       : rat

Target Server Type    : MYSQL
Target Server Version : 50508
File Encoding         : 65001

Date: 2014-06-20 15:04:54
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `rat_app`
-- ----------------------------
DROP TABLE IF EXISTS `rat_app`;
CREATE TABLE `rat_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `app_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_app
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_attachment`
-- ----------------------------
DROP TABLE IF EXISTS `rat_attachment`;
CREATE TABLE `rat_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) DEFAULT NULL,
  `respone_id` int(11) DEFAULT NULL,
  `user_list_id` int(11) DEFAULT NULL,
  `file_ids` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_attachment
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_attachment_employer`
-- ----------------------------
DROP TABLE IF EXISTS `rat_attachment_employer`;
CREATE TABLE `rat_attachment_employer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employer_id` int(11) NOT NULL,
  `file_ids` varchar(100) DEFAULT NULL,
  `request_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_attachment_employer
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_department`
-- ----------------------------
DROP TABLE IF EXISTS `rat_department`;
CREATE TABLE `rat_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_department
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_dropdown_lable`
-- ----------------------------
DROP TABLE IF EXISTS `rat_dropdown_lable`;
CREATE TABLE `rat_dropdown_lable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `lable` varchar(100) NOT NULL,
  `dropdown_type` enum('position','role','project type') DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_time` int(11) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_dropdown_lable
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_employer`
-- ----------------------------
DROP TABLE IF EXISTS `rat_employer`;
CREATE TABLE `rat_employer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `gender` enum('Female','Male') NOT NULL DEFAULT 'Male',
  `avatar` varchar(100) DEFAULT NULL,
  `status` enum('Single','Married') NOT NULL,
  `address` varchar(200) NOT NULL,
  `telephone` varchar(100) NOT NULL,
  `mobile` varchar(100) NOT NULL,
  `company_email` varchar(100) NOT NULL,
  `personal_email` varchar(100) NOT NULL,
  `yahoo` varchar(100) NOT NULL,
  `skype` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `position_sub_level` varchar(100) NOT NULL COMMENT 'dropdownlableId',
  `position_id` int(11) NOT NULL COMMENT 'dropdownlableId',
  `user_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_time` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_employer
-- ----------------------------
INSERT INTO `rat_employer` VALUES ('1', 'SD0001', 'Thieu Le Quang', 'Thieu', 'Le', '0000-00-00', 'Male', null, 'Single', '648/111 CMT8 , Qu?n 3, TP H? Chí Minh', '0986684184', '0986684184', 'thieu.lequang@harveynash.vn', 'quangthieuagu@gmail.com', '', '', null, '', '0', '1', null, '0', null);

-- ----------------------------
-- Table structure for `rat_file`
-- ----------------------------
DROP TABLE IF EXISTS `rat_file`;
CREATE TABLE `rat_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(100) DEFAULT NULL,
  `type` enum('cv','other') DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `upload_by` int(11) DEFAULT NULL,
  `upload_time` int(11) DEFAULT NULL,
  `file_ext` varchar(100) DEFAULT NULL,
  `file_size` float DEFAULT NULL,
  `full_path` varchar(100) DEFAULT NULL,
  `commnet` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_file
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_function`
-- ----------------------------
DROP TABLE IF EXISTS `rat_function`;
CREATE TABLE `rat_function` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text,
  `link` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_function
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_permission`
-- ----------------------------
DROP TABLE IF EXISTS `rat_permission`;
CREATE TABLE `rat_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `function_id` int(11) DEFAULT NULL,
  `can_it` enum('view','edit','delete','all') DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_time` int(11) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_permission
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_project`
-- ----------------------------
DROP TABLE IF EXISTS `rat_project`;
CREATE TABLE `rat_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(100) NOT NULL,
  `status_id` int(11) NOT NULL COMMENT 'dropdownid',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `type_id` int(11) NOT NULL COMMENT 'dropdownid',
  `effort` int(11) DEFAULT NULL COMMENT 'Total hours to completed',
  `custommer_name` varchar(100) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `FOREIGN_KEY_STATUS_ID` (`status_id`),
  KEY `FOREIGN_KEY_TYPE_ID` (`type_id`),
  KEY `PROJECTNAME` (`name`),
  CONSTRAINT `FOREIGN_KEY_STATUS_ID` FOREIGN KEY (`status_id`) REFERENCES `rat_dropdown_lable` (`id`),
  CONSTRAINT `FOREIGN_KEY_TYPE_ID` FOREIGN KEY (`type_id`) REFERENCES `rat_dropdown_lable` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_project
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_project_member`
-- ----------------------------
DROP TABLE IF EXISTS `rat_project_member`;
CREATE TABLE `rat_project_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) NOT NULL,
  `employerId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `effortPercent` int(11) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_project_member
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_request_close`
-- ----------------------------
DROP TABLE IF EXISTS `rat_request_close`;
CREATE TABLE `rat_request_close` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fromId` int(11) NOT NULL COMMENT 'userId',
  `toId` int(11) NOT NULL COMMENT 'userId',
  `created_date` int(11) DEFAULT NULL,
  `requestType` int(11) DEFAULT NULL,
  `comment` text,
  `reponse_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_request_close
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_request_open`
-- ----------------------------
DROP TABLE IF EXISTS `rat_request_open`;
CREATE TABLE `rat_request_open` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fromId` int(11) NOT NULL COMMENT 'userId',
  `toId` int(11) NOT NULL COMMENT 'userId',
  `created_date` int(11) DEFAULT NULL,
  `requestType` int(11) DEFAULT NULL,
  `comment` text,
  `reponse_date` int(11) DEFAULT NULL,
  `status` enum('waiting','feedbacked','confirm','closed','confirmed') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_request_open
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_request_watching`
-- ----------------------------
DROP TABLE IF EXISTS `rat_request_watching`;
CREATE TABLE `rat_request_watching` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `requestId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_request_watching
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_respone_close`
-- ----------------------------
DROP TABLE IF EXISTS `rat_respone_close`;
CREATE TABLE `rat_respone_close` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestId` int(11) NOT NULL,
  `fromId` int(11) NOT NULL,
  `toId` int(11) NOT NULL,
  `created_time` int(11) NOT NULL,
  `status` enum('waiting','confirmed','feedbacked') DEFAULT NULL,
  `reponse_time` int(11) DEFAULT NULL,
  `comment` text,
  `fileIds` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_respone_close
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_respone_open`
-- ----------------------------
DROP TABLE IF EXISTS `rat_respone_open`;
CREATE TABLE `rat_respone_open` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestId` int(11) NOT NULL,
  `fromId` int(11) NOT NULL,
  `toId` int(11) NOT NULL,
  `created_time` int(11) NOT NULL,
  `status` enum('waiting','confirmed','feedbacked') DEFAULT NULL,
  `reponse_time` int(11) DEFAULT NULL,
  `comment` text,
  `fileIds` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_respone_open
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_respone_open_close`
-- ----------------------------
DROP TABLE IF EXISTS `rat_respone_open_close`;
CREATE TABLE `rat_respone_open_close` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestId` int(11) NOT NULL,
  `fromId` int(11) NOT NULL,
  `toId` int(11) NOT NULL,
  `created_time` int(11) NOT NULL,
  `status` enum('waiting','confirmed','feedbacked') DEFAULT NULL,
  `reponse_time` int(11) DEFAULT NULL,
  `comment` text,
  `fileIds` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_respone_open_close
-- ----------------------------

-- ----------------------------
-- Table structure for `rat_user`
-- ----------------------------
DROP TABLE IF EXISTS `rat_user`;
CREATE TABLE `rat_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) DEFAULT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `employerId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rat_user
-- ----------------------------
INSERT INTO `rat_user` VALUES ('1', 'Thieu Le Quang', 'Thieu', 'Le', 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'quangthieuagu@gmail.com', '1', '1', '13333', '1');
