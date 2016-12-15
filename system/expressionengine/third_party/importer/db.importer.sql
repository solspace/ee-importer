CREATE TABLE IF NOT EXISTS `exp_importer_profiles` (
	`profile_id`				int unsigned		NOT NULL AUTO_INCREMENT,
	`site_id`					int unsigned				 DEFAULT 1,
	`name`						varchar(255)		NOT NULL DEFAULT '',
	`instructions`				text,
	`content_type`				varchar(100)		NOT NULL DEFAULT 'channel_entries',
	`datatype`					varchar(255)		NOT NULL DEFAULT 'xml',
	`hash`						varchar(32)			NOT NULL DEFAULT '',
	`last_import`				int(10) unsigned			 DEFAULT 0,
	PRIMARY KEY					(`profile_id`),
	KEY							`site_id` (`site_id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;

CREATE TABLE IF NOT EXISTS `exp_importer_profile_settings` (
	`setting_id`				int unsigned		NOT NULL AUTO_INCREMENT,
	`profile_id`				int unsigned				 DEFAULT 1,
	`setting`					varchar(255)		NOT NULL DEFAULT '',
	`value`						mediumtext,
	PRIMARY KEY					(`setting_id`),
	KEY							`site_id` (`profile_id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;

CREATE TABLE IF NOT EXISTS `exp_importer_log` (
	`log_id`					int unsigned		NOT NULL AUTO_INCREMENT,
	`profile_id`				int unsigned				 DEFAULT 1,
	`member_id`					int unsigned				 DEFAULT 1,
	`batch_hash` 				varchar(13)			NOT NULL DEFAULT '',
	`details`					mediumtext,
	`date`						int(10) unsigned			 DEFAULT 0,
	PRIMARY KEY					(`log_id`),
	KEY							`profile_id` (`profile_id`),
	KEY							`member_id` (`member_id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;

CREATE TABLE IF NOT EXISTS `exp_importer_batches` (
	`batch_id`					bigint unsigned		NOT NULL AUTO_INCREMENT,
	`profile_id`				int unsigned		NOT NULL DEFAULT 1,
	`batch_hash`				varchar(13)			NOT NULL DEFAULT '',
	`details`					mediumtext,
	`batch_date`				int(10) unsigned			 DEFAULT 0,
	`finished`					char(1)				NOT NULL DEFAULT 'n',
	PRIMARY KEY					(`batch_id`),
	KEY							`profile_id` (`profile_id`),
	KEY							`batch_hash` (`batch_hash`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;

CREATE TABLE IF NOT EXISTS `exp_importer_batch_data` (
	`profile_id`				int unsigned		NOT NULL DEFAULT 1,
	`batch_hash`				varchar(13)			NOT NULL DEFAULT '',
	`batch_number`				int unsigned		NOT NULL DEFAULT 1,
	`batch_data`				mediumtext,
	`finished`					char(1)				NOT NULL DEFAULT 'n',
	`batch_date`				int(10) unsigned			 DEFAULT 0,
	KEY							`profile_id` (`profile_id`),
	KEY							`batch_hash` (`batch_hash`),
	KEY							`batch_number` (`batch_number`)
) CHARACTER SET utf8 COLLATE utf8_general_ci ;;