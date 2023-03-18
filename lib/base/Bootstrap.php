<?php
/**
 * Bootstrap Class
 *
 * Initializes the Skeleton framework
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Bootstrap {

	/**
	 * Bootstrap
	 *
	 * @access public
	 */
	public static function boot() {
		/**
		 * Set the root path
		 */
		$root_path = realpath(dirname(__FILE__) . '/../..');

		/**
		 * Register the autoloader from Composer
		 */
		require_once $root_path . '/lib/external/packages/autoload.php';

		/**
		 * Get the config
		 */
		\Skeleton\Core\Config::include_directory($root_path . '/config');
		$config = \Skeleton\Core\Config::get();

		/**
		 * Register the autoloader
		 */
		$autoloader = new \Skeleton\Core\Autoloader();
		$autoloader->add_include_path($root_path . '/lib/model/');
		$autoloader->add_include_path($root_path . '/lib/base/');
		$autoloader->add_include_path($root_path . '/lib/component/');
		$autoloader->register();

		/**
		 * Initialize the database
		 */
		\Skeleton\Database\Config::$auto_null = false;
		\Skeleton\Database\Config::$auto_trim = false;
		\Skeleton\Database\Config::$auto_discard = false;
		$database = \Skeleton\Database\Database::get($config->database, true);

		/**
		 * Initialize migration
		 */
		\Skeleton\Database\Migration\Config::$migration_directory = $root_path . '/migration/';
		\Skeleton\Database\Migration\Config::$version_storage  = 'database';
		\Skeleton\Database\Migration\Config::$database_table  = 'db_version';

		/**
		 * Initialize the error handler
		 */
		\Skeleton\Error\Config::$debug = $config->debug;
		\Skeleton\Error\Handler::enable();

		/**
		 * Initialize the template
		 */
		\Skeleton\Template\Twig\Config::$cache_directory = $config->tmp_path . 'twig/';
		\Skeleton\Template\Twig\Config::$debug = $config->debug;
	}
}
