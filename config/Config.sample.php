<?php
/**
 * Configuration Class
 *
 * Implemented as singleton (only one instance globally).
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Config {
	/**
	 * Config array
	 *
	 * @var array
	 * @access private
	 */
	protected $config_data = [];

	/**
	 * Config object
	 *
	 * @var Config
	 * @access private
	 */
	private static $config = null;

	/**
	 * Private (disabled) constructor
	 *
	 * @access private
	 */
	public function __construct() {
		$this->config_data = array_merge($this->read(), $this->config_data);
	}

	/**
	 * Get config vars as properties
	 *
	 * @param string name
	 * @return mixed
	 * @throws Exception When accessing an unknown config variable, an Exception is thrown
	 * @access public
	 */
	public function __get($name) {
		if (!array_key_exists($name, $this->config_data)) {
			throw new Exception('Attempting to read unkown config key: '.$name);
		}

		return $this->config_data[$name];
	}
	/**
	 * Get function, returns a Config object
	 *
	 * @return Config
	 * @access public
	 */
	public static function Get() {
		if (!isset(self::$config)) {
			try {
				self::$config = \Skeleton\Core\Application::Get()->config;
			} catch (Exception $e) {
				return new Config();
			}
		}
		return self::$config;
	}

	/**
	 * Check if config var exists
	 *
	 * @param string key
	 * @return bool $isset
	 * @access public
	 */
	public function __isset($key) {
		if (!isset($this->config_data) OR $this->config_data === null) {
			$this->read();
		}

		if (isset($this->config_data[$key])) {
			return true;
		}

		return false;
	}

	/**
	 * Read config file
	 *
	 * Populates the $this->config var, now the config is just in this function
	 * but it could easily be replaced with something else
	 *
	 * @access private
	 */
	private function read() {
		return [
			/**
			 * APPLICATION SPECIFIC CONFIGURATION
			 *
			 * The following configuration items needs to be overwritten in the application config file
			 */

			/**
			 * The hostname to listen on
			 */
			'hostnames' => [],

			/**
			 * Routes
			 */
			'routes' => [],

			/**
			 * Default language. Used for sending mails when the language is not given
			 */
			'default_language' => 'en',

			/**
			 * Default module
			 */
			'module_default' => 'index',

			/**
			 * 404_module
			 */
			'module_404' => '404',

			/**
			 * GENERAL CONFIGURATION
			 *
			 * These configuration items can be overwritten by application specific configuration.
			 * However they are probably the same for all applications.
			 */

			/**
			 * Setting debug to true will enable debug output and error display.
			 * Error email is not affected.
			 */
			'debug' => true,
			'errors_from' => 'errors@example.com',
			'errors_to' => 'errors@example.com',

			/**
			 * Database
			 */
			'database' => 'mysqli://username:password@localhost/database',

			/**
			 * Translation base language that the templates will be made up in
			 * Do not change after creation of your project!
			 */
			'base_language' => 'en',

			/**
			 * The default language that will be shown to the user if it can not be guessed
			 */
			'default_language' => 'en',
		];
	}
}
