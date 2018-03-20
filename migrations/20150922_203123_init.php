<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use Skeleton\Database\Database;

class Migration_20150922_203123_Init extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::Get();
		$db->query("
			CREATE TABLE IF NOT EXISTS `language` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `name_local` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `name_short` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `name_ogone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id`),
			  FULLTEXT KEY `name_short` (`name_short`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		", []);

		$db->query("
			INSERT INTO `language` VALUES (1,'English','English','en','en_US'),(2,'French','Français','fr','fr_FR'),(3,'Dutch','Nederlands','nl','nl_NL');
		", []);

		$db->query("
			CREATE TABLE IF NOT EXISTS `log` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) NOT NULL,
			  `classname` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
			  `object_id` int(11) NOT NULL,
			  `content` text COLLATE utf8_unicode_ci NOT NULL,
			  `created` datetime NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `classname` (`classname`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
		", []);

		$db->query("
			CREATE TABLE IF NOT EXISTS `file` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `unique_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `mime_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `size` int(11) NOT NULL,
			  `created` datetime NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
		", []);

		$db->query("
			CREATE TABLE IF NOT EXISTS `picture` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `file_id` int(11) NOT NULL,
			  `width` int(11) NOT NULL,
			  `height` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `file_id` (`file_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
		", []);

		$db->query("
			CREATE TABLE IF NOT EXISTS `user` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
			  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `admin` tinyint(1) NOT NULL,
			  `created` datetime NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
		", []);

		$db->query("
			ALTER TABLE  `file` ADD  `md5sum` VARCHAR( 32 ) NOT NULL AFTER  `name`;
		", []);

		$db->query("
			ALTER TABLE  `file` DROP  `unique_name`;
		", []);

		$db->query("
			ALTER TABLE  `file` ADD `expiration_date` datetime NULL AFTER `size`;
		", []);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
