-- Workflow Analyzer database schema
-- Derived from the original 2019 deployment dump, with all personal data,
-- OAuth tokens and password hashes removed. Load with:
--   mysql -u <user> -p <database> < schema.sql

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- Registered project managers
DROP TABLE IF EXISTS `wf_user`;
CREATE TABLE `wf_user` (
  `username` varchar(100) NOT NULL,
  `password` varchar(150) NOT NULL,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Projects owned by a manager
DROP TABLE IF EXISTS `wf_projects`;
CREATE TABLE `wf_projects` (
  `proj_name` varchar(100) NOT NULL,
  `proj_mgr` varchar(100) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deadline` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(1) NOT NULL,
  `uuid` varchar(250) NOT NULL,
  PRIMARY KEY (`proj_name`),
  KEY `proj_mgr` (`proj_mgr`),
  CONSTRAINT `wf_projects_ibfk_2` FOREIGN KEY (`proj_mgr`) REFERENCES `wf_user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DELIMITER ;;
CREATE TRIGGER `wf_projects_bi` BEFORE INSERT ON `wf_projects` FOR EACH ROW
begin
SET new.uuid = uuid();
end;;
DELIMITER ;

-- Daily event that activates projects whose start date has passed
DROP EVENT IF EXISTS `projStart`;
DELIMITER ;;
CREATE EVENT `projStart` ON SCHEDULE EVERY 1 DAY STARTS '2019-04-09 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO update wf_projects set status = 1 where start_date < now();;
DELIMITER ;

-- Team members enrolled in a project, with their Microsoft Graph tokens
DROP TABLE IF EXISTS `wf_emails`;
CREATE TABLE `wf_emails` (
  `email` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `proj_name` varchar(100) NOT NULL,
  `access_token` varchar(1500) NOT NULL,
  `refresh_token` varchar(1500) NOT NULL,
  `id` varchar(500) NOT NULL,
  PRIMARY KEY (`email`),
  KEY `proj_name` (`proj_name`),
  CONSTRAINT `wf_emails_ibfk_1` FOREIGN KEY (`proj_name`) REFERENCES `wf_projects` (`proj_name`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Project e-mails fetched from Outlook
DROP TABLE IF EXISTS `wf_mails`;
CREATE TABLE `wf_mails` (
  `id` varchar(256) NOT NULL,
  `to` varchar(100) NOT NULL,
  `frm` varchar(100) NOT NULL,
  `subject` varchar(1000) NOT NULL,
  `body` mediumtext NOT NULL,
  `proj_name` varchar(100) NOT NULL,
  `log` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `proj_name` (`proj_name`),
  CONSTRAINT `wf_mails_ibfk_1` FOREIGN KEY (`proj_name`) REFERENCES `wf_projects` (`proj_name`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Modules that make up a project
DROP TABLE IF EXISTS `wf_modules`;
CREATE TABLE `wf_modules` (
  `log` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proj_name` varchar(100) NOT NULL,
  `module` varchar(100) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deadline` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `proj_name` (`proj_name`),
  CONSTRAINT `wf_modules_ibfk_2` FOREIGN KEY (`proj_name`) REFERENCES `wf_projects` (`proj_name`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Progress history per module, written by the NLP pipeline and read by the charts
DROP TABLE IF EXISTS `wf_module_progress`;
CREATE TABLE `wf_module_progress` (
  `log` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `module` varchar(100) NOT NULL,
  `proj_name` varchar(100) NOT NULL,
  `exp_progress` int(3) NOT NULL DEFAULT '0',
  `curr_progress` int(3) NOT NULL DEFAULT '0',
  KEY `proj_name` (`proj_name`),
  CONSTRAINT `wf_module_progress_ibfk_1` FOREIGN KEY (`proj_name`) REFERENCES `wf_projects` (`proj_name`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Upcoming calendar events pulled from the manager's Outlook calendar
DROP TABLE IF EXISTS `wf_calendar`;
CREATE TABLE `wf_calendar` (
  `proj_name` varchar(100) NOT NULL,
  `subject` mediumtext NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `proj_name` (`proj_name`),
  CONSTRAINT `wf_calendar_ibfk_1` FOREIGN KEY (`proj_name`) REFERENCES `wf_projects` (`proj_name`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Overall sentiment score history per project
DROP TABLE IF EXISTS `wf_sentiments`;
CREATE TABLE `wf_sentiments` (
  `score` int(11) NOT NULL,
  `proj_name` varchar(100) NOT NULL,
  `log` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `proj_name` (`proj_name`),
  CONSTRAINT `wf_sentiments_ibfk_1` FOREIGN KEY (`proj_name`) REFERENCES `wf_projects` (`proj_name`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
