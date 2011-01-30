ALTER TABLE `{prefix}comment` ADD `comment_status` INT( 3 ) NOT NULL DEFAULT '0';
ALTER TABLE `{prefix}identifier` ADD `identifier_status` INT( 3 ) NOT NULL DEFAULT '0',
ADD `identifier_allow_comment` CHAR( 1 ) NOT NULL DEFAULT 'Y',
ADD `identifier_moderate_comment` CHAR( 1 ) NOT NULL DEFAULT 'N';