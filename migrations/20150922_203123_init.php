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
			CREATE TABLE IF NOT EXISTS `log` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) NOT NULL,
			  `classname` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
			  `object_id` int(11) NOT NULL,
			  `content` text COLLATE utf8_unicode_ci NOT NULL,
			  `created` datetime NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `classname` (`classname`),
			  KEY `classname_object_id` (`classname`,`object_id`)
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
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {}
}
