-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;

DROP TABLE IF EXISTS `app_account`;
CREATE TABLE `app_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_bin NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `balance` decimal(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `app_accountTransaction`;
CREATE TABLE `app_accountTransaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `amount` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '>0: deposit\n<0: withdrawal',
  `account_id` int(11) NOT NULL,
  `note` text COLLATE utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_r_accountTransaction_r_account1` (`account_id`),
  KEY `fk_r_accountTransaction_system_user1` (`user_id`),
  CONSTRAINT `app_accountTransaction_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `app_account` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `app_accountTransaction_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `system_user` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `app_additionalCost`;
CREATE TABLE `app_additionalCost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `app_additionalCostOption`;
CREATE TABLE `app_additionalCostOption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `race_id` int(11) NOT NULL COMMENT '	',
  `cost_id` int(11) NOT NULL,
  `name` varchar(30) COLLATE utf8_bin NOT NULL,
  `price` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_r_additionalCostOptions_r_race1` (`race_id`),
  KEY `fk_r_additionalCostOptions_r_additionalCost1` (`cost_id`),
  CONSTRAINT `app_additionalCostOption_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `app_race` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_r_additionalCostOptions_r_additionalCost1` FOREIGN KEY (`cost_id`) REFERENCES `app_additionalCost` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `app_allowed`;
CREATE TABLE `app_allowed` (
  `applicant_id` int(11) NOT NULL,
  `applier_id` int(11) NOT NULL,
  PRIMARY KEY (`applicant_id`,`applier_id`),
  KEY `fk_app_allowed_system_user1` (`applier_id`),
  CONSTRAINT `fk_app_allowed_system_user1` FOREIGN KEY (`applier_id`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_app_allowed_system_user2` FOREIGN KEY (`applier_id`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Kdo koho může přihlašovat';


DROP TABLE IF EXISTS `app_backer`;
CREATE TABLE `app_backer` (
  `user_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_r_member_system_user10` FOREIGN KEY (`user_id`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `app_category`;
CREATE TABLE `app_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `defaultPrice` decimal(6,2) NOT NULL DEFAULT '30.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `app_entry`;
CREATE TABLE `app_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `presentedCategory_id` int(11) NOT NULL DEFAULT '0',
  `racer_id` int(11) DEFAULT '0',
  `racerName` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'In case of "anonymous" application\n',
  `datetime` datetime NOT NULL,
  `SINumber` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `account_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FKrace` (`presentedCategory_id`),
  KEY `fk_r_entry_r_race2category1` (`presentedCategory_id`),
  KEY `fk_r_entry_r_account1` (`account_id`),
  KEY `racer_id` (`racer_id`),
  CONSTRAINT `app_entry_ibfk_3` FOREIGN KEY (`racer_id`) REFERENCES `system_user` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `app_entry_ibfk_4` FOREIGN KEY (`account_id`) REFERENCES `app_account` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `app_entry_ibfk_5` FOREIGN KEY (`presentedCategory_id`) REFERENCES `app_race2category` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `app_member`;
CREATE TABLE `app_member` (
  `user_id` int(11) NOT NULL,
  `SI` varchar(10) COLLATE utf8_czech_ci DEFAULT NULL,
  `licence` char(1) COLLATE utf8_czech_ci NOT NULL DEFAULT 'C',
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_r_member_system_user1` FOREIGN KEY (`user_id`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `app_race`;
CREATE TABLE `app_race` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `url` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `type` varchar(40) COLLATE utf8_czech_ci DEFAULT '',
  `organizer` varchar(30) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `place` varchar(60) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `start` varchar(40) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `begin` date NOT NULL,
  `end` date DEFAULT NULL,
  `deadline` date NOT NULL,
  `web` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `note` text COLLATE utf8_czech_ci,
  `tag_id` int(11) NOT NULL DEFAULT '0',
  `status` int(4) NOT NULL DEFAULT '0',
  `emails` tinyint(1) NOT NULL DEFAULT '0',
  `manager_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`,`begin`),
  KEY `fk_r_race__user1` (`manager_id`),
  KEY `fk_r_race_r_tag1` (`tag_id`),
  CONSTRAINT `fk_r_race_r_tag1` FOREIGN KEY (`tag_id`) REFERENCES `app_tag` (`id`),
  CONSTRAINT `fk_r_race__user1` FOREIGN KEY (`manager_id`) REFERENCES `system_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `app_race2category`;
CREATE TABLE `app_race2category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `race_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `price` decimal(6,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`race_id`,`category_id`),
  KEY `fk_r_race2category_r_race1` (`race_id`),
  KEY `fk_r_race2category_r_category1` (`category_id`),
  CONSTRAINT `app_race2category_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `app_race` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_r_race2category_r_category1` FOREIGN KEY (`category_id`) REFERENCES `app_category` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='mají vlastní ID pro snadnější udržení integrity';


DROP TABLE IF EXISTS `app_selectedOption`;
CREATE TABLE `app_selectedOption` (
  `entry_id` int(11) NOT NULL,
  `cost_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`entry_id`,`cost_id`,`option_id`),
  KEY `fk_r_selectedOptions_r_entry1` (`entry_id`),
  KEY `fk_r_selectedOptions_r_additionalCost1` (`cost_id`),
  KEY `fk_r_selectedOptions_r_additionalCostOptions1` (`option_id`),
  CONSTRAINT `app_selectedOption_ibfk_1` FOREIGN KEY (`entry_id`) REFERENCES `app_entry` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `app_selectedOption_ibfk_4` FOREIGN KEY (`cost_id`) REFERENCES `app_additionalCost` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `app_selectedOption_ibfk_5` FOREIGN KEY (`option_id`) REFERENCES `app_additionalCostOption` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `app_tag`;
CREATE TABLE `app_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `color` varchar(6) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `con_sideblock`;
CREATE TABLE `con_sideblock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `position` enum('left','right') COLLATE utf8_czech_ci NOT NULL,
  `order` tinyint(2) NOT NULL,
  `content` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `con_staticPage`;
CREATE TABLE `con_staticPage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_bin NOT NULL,
  `content` text COLLATE utf8_bin NOT NULL,
  `content_src` text COLLATE utf8_bin NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `lastModDate` date NOT NULL,
  `lastModUser` int(11) NOT NULL,
  `url` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_UNIQUE` (`url`),
  KEY `fk_con_staticPage_con_staticPage1` (`parent_id`),
  KEY `fk_con_staticPage__user1` (`lastModUser`),
  CONSTRAINT `con_staticPage_ibfk_1` FOREIGN KEY (`lastModUser`) REFERENCES `system_user` (`id`),
  CONSTRAINT `fk_con_staticPage_con_staticPage1` FOREIGN KEY (`parent_id`) REFERENCES `con_staticPage` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `doc_directory`;
CREATE TABLE `doc_directory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8_czech_ci NOT NULL,
  `url` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `fk_d_directory_d_directory1` (`parent_id`),
  CONSTRAINT `doc_directory_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `doc_directory` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `doc_file`;
CREATE TABLE `doc_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `directory_id` int(11) NOT NULL,
  `filetype_id` int(11) NOT NULL,
  `file` varchar(255) CHARACTER SET latin2 NOT NULL,
  `name` varchar(255) CHARACTER SET latin2 NOT NULL,
  `size` int(11) NOT NULL,
  `published` datetime NOT NULL,
  `author_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filetype_id` (`filetype_id`,`directory_id`,`name`),
  KEY `fk_d_file_d_filetype1` (`filetype_id`),
  KEY `fk_d_file_d_directory1` (`directory_id`),
  KEY `fk_d_file__user1` (`author_id`),
  CONSTRAINT `doc_file_ibfk_1` FOREIGN KEY (`directory_id`) REFERENCES `doc_directory` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_d_file_d_filetype1` FOREIGN KEY (`filetype_id`) REFERENCES `doc_filetype` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_d_file__user1` FOREIGN KEY (`author_id`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `doc_filetype`;
CREATE TABLE `doc_filetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` varchar(30) CHARACTER SET latin2 NOT NULL,
  `extension` varchar(10) CHARACTER SET latin2 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queueID` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `created` int(11) NOT NULL,
  `data` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `forum_post`;
CREATE TABLE `forum_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text COLLATE utf8_czech_ci NOT NULL,
  `author` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `IP` varchar(15) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `datetime` datetime NOT NULL,
  `thread_id` int(11) NOT NULL,
  `lft` int(11) NOT NULL DEFAULT '1',
  `rgt` int(11) NOT NULL DEFAULT '1',
  `depth` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_f_post_f_thread1` (`thread_id`),
  CONSTRAINT `forum_post_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `forum_thread` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `forum_thread`;
CREATE TABLE `forum_thread` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(40) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `url` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `topic_id` (`topic_id`,`name`),
  UNIQUE KEY `url` (`url`),
  KEY `fk_f_thread_f_topic1` (`topic_id`),
  CONSTRAINT `fk_f_thread_f_topic1` FOREIGN KEY (`topic_id`) REFERENCES `forum_topic` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `forum_topic`;
CREATE TABLE `forum_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `url` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `desc` varchar(255) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `gallery_directory`;
CREATE TABLE `gallery_directory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `url` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `gallery_gallery`;
CREATE TABLE `gallery_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `url` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `desc` text COLLATE utf8_czech_ci,
  `published` datetime NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '1',
  `directory_id` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `fk_g_gallery_g_directory` (`directory_id`),
  CONSTRAINT `gallery_gallery_ibfk_1` FOREIGN KEY (`directory_id`) REFERENCES `gallery_directory` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `gallery_photo`;
CREATE TABLE `gallery_photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gallery_id` int(11) NOT NULL DEFAULT '0',
  `author_id` int(11) NOT NULL DEFAULT '0',
  `file` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `desc` text COLLATE utf8_czech_ci NOT NULL,
  `published` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_g_photo_g_gallery1` (`gallery_id`),
  KEY `fk_g_photo__user1` (`author_id`),
  CONSTRAINT `fk_g_photo__user1` FOREIGN KEY (`author_id`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `gallery_photo_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery_gallery` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `link_category`;
CREATE TABLE `link_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `link_link`;
CREATE TABLE `link_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `URL` text COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(70) COLLATE utf8_czech_ci DEFAULT NULL,
  `desc` text COLLATE utf8_czech_ci,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_l_link_l_category1` (`category_id`),
  CONSTRAINT `fk_l_link_l_category1` FOREIGN KEY (`category_id`) REFERENCES `link_category` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `org_brief`;
CREATE TABLE `org_brief` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL DEFAULT '0',
  `published` datetime NOT NULL,
  `text` text COLLATE utf8_czech_ci NOT NULL,
  `text_src` text COLLATE utf8_czech_ci NOT NULL,
  `author_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_org_brief_org_event1` (`event_id`),
  KEY `fk_org_brief__user1` (`author_id`),
  KEY `published` (`published`),
  CONSTRAINT `fk_org_brief__user1` FOREIGN KEY (`author_id`) REFERENCES `system_user` (`id`),
  CONSTRAINT `org_brief_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `org_event` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `org_event`;
CREATE TABLE `org_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_bin NOT NULL,
  `start` date NOT NULL,
  `end` date DEFAULT NULL COMMENT 'Pouze u vícedenních akcí',
  `url` varchar(20) COLLATE utf8_bin NOT NULL,
  `legacy` tinyint(1) NOT NULL DEFAULT '0',
  `manager_id` int(11) NOT NULL,
  `visibility` enum('all','logged','manager') COLLATE utf8_bin NOT NULL DEFAULT 'manager',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `manager_id` (`manager_id`),
  CONSTRAINT `org_event_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `system_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tabulka seskupující více závodů dohromady.';


DROP TABLE IF EXISTS `org_event2user`;
CREATE TABLE `org_event2user` (
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`event_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `org_information`;
CREATE TABLE `org_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_bin NOT NULL,
  `html` tinyint(1) NOT NULL COMMENT 'Zda bude daný parametr možno editovat v HTML.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Možné druhy informací o závodě.';


DROP TABLE IF EXISTS `org_informationValues`;
CREATE TABLE `org_informationValues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `race_id` int(11) NOT NULL,
  `information_id` int(11) NOT NULL,
  `value` text COLLATE utf8_bin NOT NULL,
  `detailsOrder` int(11) DEFAULT NULL COMMENT 'Pokud je *Order sloupec prázdný v daném výpisu se nezobrazuje.',
  `instructionsOrder` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `race_id` (`race_id`,`information_id`),
  KEY `fk_org_informationValues_org_race1` (`race_id`),
  KEY `fk_org_informationValues_org_information1` (`information_id`),
  CONSTRAINT `org_informationValues_ibfk_1` FOREIGN KEY (`information_id`) REFERENCES `org_information` (`id`),
  CONSTRAINT `org_informationValues_ibfk_2` FOREIGN KEY (`race_id`) REFERENCES `org_race` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Spoj patra ISA hier. pro běž. uživ. a reg-ného -- reg jeNULL';


DROP TABLE IF EXISTS `org_race`;
CREATE TABLE `org_race` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `name` varchar(40) COLLATE utf8_czech_ci NOT NULL COMMENT 'Název je jen krátký název do menu.',
  `url` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Genrováno z názvu',
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_id` (`event_id`,`url`),
  KEY `fk_org_race_org_event1` (`event_id`),
  CONSTRAINT `org_race_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `org_event` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Tabulka závodu na dané události.';


DROP TABLE IF EXISTS `public_article`;
CREATE TABLE `public_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `url` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `perex` text COLLATE utf8_czech_ci,
  `perex_src` text COLLATE utf8_czech_ci,
  `text` text COLLATE utf8_czech_ci NOT NULL,
  `text_src` text COLLATE utf8_czech_ci NOT NULL,
  `published` datetime NOT NULL DEFAULT '2006-01-01 00:00:00',
  `public` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `fk_p_article__user1` (`author_id`),
  CONSTRAINT `public_article_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='tzn. nemůže být jen perex.';


DROP TABLE IF EXISTS `public_article2tag`;
CREATE TABLE `public_article2tag` (
  `tag_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  PRIMARY KEY (`tag_id`,`article_id`),
  KEY `fk_p_article_2_tag_p_tag1` (`tag_id`),
  KEY `fk_p_article_2_tag_p_article1` (`article_id`),
  CONSTRAINT `public_article2tag_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `public_tag` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `public_article2tag_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `public_article` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `public_brief`;
CREATE TABLE `public_brief` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `published` datetime NOT NULL DEFAULT '2006-01-01 00:00:00',
  `author_id` int(11) NOT NULL DEFAULT '0',
  `text` text COLLATE utf8_czech_ci NOT NULL,
  `text_src` text COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_p_brief__user1` (`author_id`),
  KEY `published` (`published`),
  CONSTRAINT `fk_p_brief__user1` FOREIGN KEY (`author_id`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `public_comment`;
CREATE TABLE `public_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL DEFAULT '0',
  `text` text COLLATE utf8_czech_ci NOT NULL,
  `author` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `posted` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_p_comment_p_article1` (`article_id`),
  CONSTRAINT `public_comment_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `public_article` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `public_event`;
CREATE TABLE `public_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `summary` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `location` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  `url` varchar(1024) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `public_tag`;
CREATE TABLE `public_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  `url` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `survey_answer`;
CREATE TABLE `survey_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL DEFAULT '0',
  `text` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_s_answer_s_survey1` (`survey_id`),
  CONSTRAINT `survey_answer_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `survey_survey` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `survey_survey`;
CREATE TABLE `survey_survey` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `survey_survey2user`;
CREATE TABLE `survey_survey2user` (
  `survey_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `answer_id` int(11) NOT NULL,
  PRIMARY KEY (`survey_id`,`user_id`),
  KEY `fk_s_survey2user_s_survey1` (`survey_id`),
  KEY `fk_s_survey2user__user1` (`user_id`),
  KEY `fk_s_survey2user_s_answer1` (`answer_id`),
  CONSTRAINT `fk_s_survey2user_s_answer1` FOREIGN KEY (`answer_id`) REFERENCES `survey_answer` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_s_survey2user_s_survey1` FOREIGN KEY (`survey_id`) REFERENCES `survey_survey` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_s_survey2user__user1` FOREIGN KEY (`user_id`) REFERENCES `system_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `system_acl`;
CREATE TABLE `system_acl` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` tinyint(3) NOT NULL DEFAULT '0',
  `privilege_id` tinyint(4) unsigned DEFAULT '0',
  `resource_id` tinyint(4) unsigned DEFAULT '0',
  `allowed` enum('Y','N') NOT NULL,
  `assertion` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `privilege_id` (`privilege_id`),
  KEY `resource_id` (`resource_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `system_acl_ibfk_1` FOREIGN KEY (`privilege_id`) REFERENCES `system_privilege` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `system_acl_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `system_resource` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `system_acl_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `system_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_privilege`;
CREATE TABLE `system_privilege` (
  `id` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_resource`;
CREATE TABLE `system_resource` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `assertion` varchar(50) DEFAULT NULL,
  `assertionDesc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_role`;
CREATE TABLE `system_role` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `parent_id` tinyint(3) DEFAULT NULL,
  `table` varchar(50) DEFAULT NULL COMMENT 'Name of the role table, NULL for normal roles',
  `desc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `system_role_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `system_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `system_token`;
CREATE TABLE `system_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `datetime` datetime NOT NULL,
  `type_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`),
  KEY `fk_system_token_system_tokenType1` (`type_id`),
  KEY `fk_system_token_system_user1` (`user_id`),
  CONSTRAINT `fk_system_token_system_tokenType1` FOREIGN KEY (`type_id`) REFERENCES `system_tokenType` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_system_token_system_user1` FOREIGN KEY (`user_id`) REFERENCES `system_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `system_tokenType`;
CREATE TABLE `system_tokenType` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `system_user`;
CREATE TABLE `system_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(20) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `password` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'MD5 hash - figure out transition to more secure version',
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `surname` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `sex` enum('M','F') COLLATE utf8_czech_ci NOT NULL DEFAULT 'M',
  `lastLog` datetime NOT NULL DEFAULT '2006-01-01 00:00:00',
  `lastIP` varchar(15) CHARACTER SET utf8 DEFAULT '127.0.0.1',
  `phone` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `address` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_czech_ci DEFAULT NULL,
  `IM` varchar(30) COLLATE utf8_czech_ci DEFAULT NULL,
  `other` text COLLATE utf8_czech_ci,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0: deactivated, 1: active',
  `account_id` int(11) DEFAULT NULL,
  `registration` char(4) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `registration_UNIQUE` (`registration`),
  KEY `fk_system_user_r_account1` (`account_id`),
  CONSTRAINT `system_user_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `app_account` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Spoj patra ISA hier. pro běž. uživ. a reg-ného -- reg jeNULL';


DROP TABLE IF EXISTS `system_user2role`;
CREATE TABLE `system_user2role` (
  `user_id` int(11) NOT NULL,
  `role_id` tinyint(3) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  KEY `system_user2role_ibfk_1` (`user_id`),
  CONSTRAINT `system_user2role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `system_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `system_user2role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `system_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `transport_car`;
CREATE TABLE `transport_car` (
  `id` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `registration` varchar(20) COLLATE utf8_bin NOT NULL COMMENT 'SPZ',
  `owner_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_t_car__user1` (`owner_id`),
  CONSTRAINT `fk_t_car__user1` FOREIGN KEY (`owner_id`) REFERENCES `system_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `transport_car2driver`;
CREATE TABLE `transport_car2driver` (
  `car_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  PRIMARY KEY (`car_id`),
  KEY `fk_t_car2driver_t_car1` (`car_id`),
  KEY `fk_t_car2driver__user1` (`driver_id`),
  CONSTRAINT `fk_t_car2driver_t_car1` FOREIGN KEY (`car_id`) REFERENCES `transport_car` (`id`),
  CONSTRAINT `fk_t_car2driver__user1` FOREIGN KEY (`driver_id`) REFERENCES `system_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Auto by mělo mít >= 1 řidiče (nemusí to být vlastník).';


DROP TABLE IF EXISTS `transport_demand`;
CREATE TABLE `transport_demand` (
  `id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `note` text COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `fk_t_demand_t_event1` (`event_id`),
  KEY `fk_t_demand__user1` (`customer_id`),
  CONSTRAINT `fk_t_demand_t_event1` FOREIGN KEY (`event_id`) REFERENCES `transport_event` (`id`),
  CONSTRAINT `fk_t_demand__user1` FOREIGN KEY (`customer_id`) REFERENCES `system_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Poptávky po dopravě na danou událost.';


DROP TABLE IF EXISTS `transport_event`;
CREATE TABLE `transport_event` (
  `id` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8_bin NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Události, na něž lze řešit dopravu přes systém.\n';


DROP TABLE IF EXISTS `transport_message`;
CREATE TABLE `transport_message` (
  `id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `author_id` int(11) NOT NULL,
  `text` text COLLATE utf8_bin NOT NULL,
  `supply_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_t_message_t_supply1` (`supply_id`),
  KEY `fk_t_message__user1` (`author_id`),
  CONSTRAINT `fk_t_message_t_supply1` FOREIGN KEY (`supply_id`) REFERENCES `transport_supply` (`id`),
  CONSTRAINT `fk_t_message__user1` FOREIGN KEY (`author_id`) REFERENCES `system_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `transport_realization`;
CREATE TABLE `transport_realization` (
  `demand_id` int(11) NOT NULL,
  `supply_id` int(11) NOT NULL,
  `confirmed` datetime DEFAULT NULL,
  `realized` int(11) NOT NULL DEFAULT '0' COMMENT 'Množství z poptávky, které bylo realizováno',
  PRIMARY KEY (`demand_id`),
  KEY `fk_t_realization_t_demand1` (`demand_id`),
  KEY `fk_t_realization_t_supply1` (`supply_id`),
  CONSTRAINT `fk_t_realization_t_demand1` FOREIGN KEY (`demand_id`) REFERENCES `transport_demand` (`id`),
  CONSTRAINT `fk_t_realization_t_supply1` FOREIGN KEY (`supply_id`) REFERENCES `transport_supply` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Jdnu poptku řesit jen njdnou. Jdna nabdka uspkojí víc popt.';


DROP TABLE IF EXISTS `transport_supply`;
CREATE TABLE `transport_supply` (
  `id` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `unitPrice` decimal(8,2) NOT NULL DEFAULT '0.00',
  `note` text COLLATE utf8_bin,
  `created` datetime NOT NULL,
  `distance` decimal(8,2) NOT NULL,
  `event_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL COMMENT 'U neuskutečněných není povinné, jinak je nutno doplnit.',
  PRIMARY KEY (`id`),
  KEY `fk_t_supply_t_event1` (`event_id`),
  KEY `fk_t_supply_t_car1` (`car_id`),
  KEY `fk_t_supply__user1` (`driver_id`),
  CONSTRAINT `fk_t_supply_t_car1` FOREIGN KEY (`car_id`) REFERENCES `transport_car` (`id`),
  CONSTRAINT `fk_t_supply_t_event1` FOREIGN KEY (`event_id`) REFERENCES `transport_event` (`id`),
  CONSTRAINT `fk_t_supply__user1` FOREIGN KEY (`driver_id`) REFERENCES `system_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Omezení: Jen auto, jež má daného řidiče.';


-- 2013-03-27 00:02:36
