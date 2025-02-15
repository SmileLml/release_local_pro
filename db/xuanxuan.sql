-- DROP TABLE IF EXISTS `zt_im_chat`;
CREATE TABLE IF NOT EXISTS `zt_im_chat` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `gid` char(40) NOT NULL DEFAULT '',
  `name` varchar(60) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT 'group',
  `admins` varchar(255) NOT NULL DEFAULT '',
  `committers` varchar(255) NOT NULL DEFAULT '',
  `subject` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `public` enum('0', '1') NOT NULL DEFAULT '0',
  `createdBy` varchar(30) NOT NULL DEFAULT '',
  `createdDate` datetime NULL,
  `ownedBy` varchar(30) NOT NULL DEFAULT '',
  `editedBy` varchar(30) NOT NULL DEFAULT '',
  `editedDate` datetime NULL,
  `mergedDate` datetime NULL,
  `lastActiveTime` datetime NULL,
  `lastMessage` int(11) unsigned NOT NULL DEFAULT 0,
  `lastMessageIndex` int(11) unsigned NOT NULL DEFAULT 0,
  `dismissDate` datetime NULL,
  `pinnedMessages` text NOT NULL DEFAULT '',
  `mergedChats` text NOT NULL DEFAULT '',
  `adminInvite` enum('0','1') NOT NULL DEFAULT '0',
  `avatar` text NOT NULL DEFAULT '',
  `archiveDate` datetime NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `public` (`public`),
  KEY `createdBy` (`createdBy`),
  KEY `editedBy` (`editedBy`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_chatuser`;
CREATE TABLE IF NOT EXISTS `zt_im_chatuser` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cgid` char(40) NOT NULL DEFAULT '',
  `user` mediumint(8) NOT NULL DEFAULT 0,
  `order` smallint(5) NOT NULL DEFAULT 0,
  `star` enum('0', '1') NOT NULL DEFAULT '0',
  `hide` enum('0', '1') NOT NULL DEFAULT '0',
  `mute` enum('0', '1') NOT NULL DEFAULT '0',
  `freeze` enum('0', '1') NOT NULL DEFAULT '0',
  `join` datetime NULL,
  `quit` datetime NULL,
  `category` varchar(40) NOT NULL DEFAULT '',
  `lastReadMessage` int(11) unsigned NOT NULL DEFAULT 0,
  `lastReadMessageIndex` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `cgid` (`cgid`),
  KEY `user` (`user`),
  KEY `order` (`order`),
  KEY `star` (`star`),
  KEY `hide` (`hide`),
  UNIQUE KEY `chatuser` (`cgid`, `user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_client`;
CREATE TABLE IF NOT EXISTS `zt_im_client` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version` char(30) NOT NULL DEFAULT '',
  `desc` varchar(100) NOT NULL DEFAULT '',
  `changeLog` text NOT NULL,
  `strategy` varchar(10) NOT NULL DEFAULT '',
  `downloads` text NOT NULL,
  `createdDate` datetime NOT NULL,
  `createdBy` varchar(30) NOT NULL DEFAULT '',
  `editedDate` datetime NOT NULL,
  `editedBy` varchar(30) NOT NULL DEFAULT '',
  `status` enum('released','wait') NOT NULL DEFAULT 'wait',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_message`;
CREATE TABLE IF NOT EXISTS `zt_im_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` char(40) NOT NULL DEFAULT '',
  `cgid` char(40) NOT NULL DEFAULT '',
  `user` varchar(30) NOT NULL DEFAULT '',
  `date` datetime NULL,
  `index` int(11) unsigned NOT NULL DEFAULT 0,
  `type` enum('normal', 'broadcast', 'notify', 'bulletin', 'botcommand') NOT NULL DEFAULT 'normal',
  `content` text NOT NULL DEFAULT '',
  `contentType` enum('text', 'plain', 'emotion', 'image', 'file', 'object', 'code') NOT NULL DEFAULT 'text',
  `data` text NOT NULL DEFAULT '',
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mgid` (`gid`),
  KEY `mcgid` (`cgid`),
  KEY `muser` (`user`),
  KEY `mtype` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_message_backup`;
CREATE TABLE IF NOT EXISTS `zt_im_message_backup` (
  `id` int(11) unsigned NOT NULL,
  `gid` char(40) NOT NULL DEFAULT '',
  `cgid` char(40) NOT NULL DEFAULT '',
  `user` varchar(30) NOT NULL DEFAULT '',
  `date` datetime NULL,
  `index` int(11) unsigned NOT NULL DEFAULT 0,
  `type` enum('normal', 'broadcast', 'notify') NOT NULL DEFAULT 'normal',
  `content` text NOT NULL DEFAULT '',
  `contentType` enum('text', 'plain', 'emotion', 'image', 'file', 'object', 'code') NOT NULL DEFAULT 'text',
  `data` text NOT NULL DEFAULT '',
  `deleted` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_message_index`;
CREATE TABLE IF NOT EXISTS `zt_im_message_index` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `tableName` char(64) NOT NULL,
  `start` int(11) unsigned NOT NULL,
  `end` int(11) unsigned NOT NULL,
  `startDate` datetime NULL,
  `endDate` datetime NULL,
  `chats` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tableName` (`tableName`),
  KEY `start` (`start`),
  KEY `end` (`end`),
  KEY `startDate` (`startDate`),
  KEY `endDate` (`endDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_chat_message_index`;
CREATE TABLE IF NOT EXISTS `zt_im_chat_message_index` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `gid` char(40) NOT NULL,
  `tableName` char(64) NOT NULL,
  `start` int(11) unsigned NOT NULL,
  `end` int(11) unsigned NOT NULL,
  `startIndex` int(11) unsigned NOT NULL,
  `endIndex` int(11) unsigned NOT NULL,
  `startDate` datetime NULL,
  `endDate` datetime NULL,
  `count` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chattable` (`gid`,`tableName`),
  KEY `start` (`start`),
  KEY `end` (`end`),
  KEY `startDate` (`startDate`),
  KEY `endDate` (`endDate`),
  KEY `chatstartindex` (`gid`,`startIndex`),
  KEY `chatendindex` (`gid`,`endIndex`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_messagestatus`;
CREATE TABLE IF NOT EXISTS `zt_im_messagestatus` (
  `user` mediumint(8) NOT NULL DEFAULT 0,
  `message` int(11) unsigned NOT NULL,
  `status` enum('waiting','sent','readed','deleted') NOT NULL DEFAULT 'waiting',
  UNIQUE KEY `user` (`user`,`message`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_queue`;
CREATE TABLE IF NOT EXISTS `zt_im_queue` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `type` char(30) NOT NULL,
  `content` text NOT NULL,
  `addDate` datetime NOT NULL,
  `processDate` datetime NOT NULL,
  `result` text NOT NULL,
  `status` char(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_conference`;
CREATE TABLE IF NOT EXISTS `zt_im_conference` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `rid` char(40) NOT NULL DEFAULT '',
  `cgid` char(40) NOT NULL DEFAULT '',
  `status` enum ('closed', 'open', 'notStarted') NOT NULL DEFAULT 'closed',
  `participants` text NOT NULL,
  `subscribers` text NOT NULL,
  `invitee` text NOT NULL,
  `openedBy` mediumint(8) NOT NULL DEFAULT 0,
  `openedDate` datetime NULL,
  `topic` text NOT NULL,
  `startTime` datetime NULL,
  `endTime` datetime NULL,
  `password` char(20) NOT NULL DEFAULT '',
  `type` enum('default','periodic','scheduled') NOT NULL DEFAULT 'default',
  `number` char(20) NOT NULL DEFAULT '',
  `note` text NOT NULL,
  `sentNotify` tinyint(1) NOT NULL DEFAULT 0,
  `reminderTime` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_conferenceaction`;
CREATE TABLE IF NOT EXISTS `zt_im_conferenceaction` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `rid` char(40) NOT NULL DEFAULT '',
  `type` enum('create','invite','join','leave','close','publish') NOT NULL DEFAULT 'create',
  `data` text NOT NULL DEFAULT '',
  `user` mediumint(8) NOT NULL DEFAULT 0,
  `date` datetime NULL,
  `device` char(40) NOT NULL DEFAULT 'default',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_conferenceuser`;
CREATE TABLE IF NOT EXISTS `zt_im_conferenceuser` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `conference` mediumint(8) NOT NULL DEFAULT 0,
  `user` mediumint(8) NOT NULL DEFAULT 0,
  `hide` enum('0', '1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `conferenceuser` (`conference`, `user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `zt_im_userdevice`;
CREATE TABLE IF NOT EXISTS `zt_im_userdevice` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `user` mediumint(8) NOT NULL DEFAULT 0,
  `device` char(40) NOT NULL DEFAULT 'default',
  `deviceID` char(40) NOT NULL DEFAULT '',
  `token` char(64) NOT NULL DEFAULT '',
  `validUntil` datetime NULL,
  `lastLogin` datetime NULL,
  `lastLogout` datetime NULL,
  `online` tinyint(1) NOT NULL DEFAULT 0,
  `version` char(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `lastLogin` (`lastLogin`),
  KEY `lastLogout` (`lastLogout`),
  UNIQUE KEY `userdevice` (`user`, `device`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

