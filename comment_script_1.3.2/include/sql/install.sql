CREATE TABLE IF NOT EXISTS `{prefix}comment` (
  `comment_id` int(12) NOT NULL,
  `comment_identifier_id` int(12) NOT NULL,
  `comment_identifier` text,
  `comment_identifier_hash` varchar(32) default NULL,
  `comment_author_name` text,
  `comment_author_email` text,
  `comment_author_homepage` text,
  `comment_author_ip` varchar(20) default NULL,
  `comment_author_host` varchar(250) default NULL,
  `comment_author_user_agent` text,
  `comment_title` text,
  `comment_text` text,
  `comment_timestamp` int(10) NOT NULL,
  `comment_status` int(3) NOT NULL default '0',
  KEY `comment_id` (`comment_id`),
  KEY `comment_identifier_id` (`comment_identifier_id`)
) TYPE=MyISAM;


CREATE TABLE IF NOT EXISTS `{prefix}identifier` (
  `identifier_id` int(12) NOT NULL,
  `identifier_value` text,
  `identifier_hash` varchar(32) default NULL,
  `identifier_name` text,
  `identifier_url` text,
  `identifier_status` int(3) NOT NULL default '0',
  `identifier_allow_comment` char(1) NOT NULL default 'Y',
  `identifier_moderate_comment` char(1) NOT NULL default 'N',
  KEY `identifier_id` (`identifier_id`),
  KEY `identifier_hash` (`identifier_hash`)
) TYPE=MyISAM;


CREATE TABLE IF NOT EXISTS `{prefix}setting` (
  `setting_name` varchar(250) NOT NULL,
  `setting_value` text,
  KEY `setting_name` (`setting_name`)
) TYPE=MyISAM;