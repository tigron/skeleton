<?php
/**
 * Configuration class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Config {
	/**
	 * Config array
	 *
	 * @var array
	 * @access private
	 */
	private $config_data = null;

	/**
	 * Config object
	 *
	 * @var Config
	 * @access private
	 */
	private static $config = false;

	/**
	 * Private (disabled) constructor
	 *
	 * @access private
	 */
	private function __construct() { }

	/**
	 * Get function, returns a Config object
	 *
	 * @return Config
	 * @access public
	 */
	public static function Get() {
		if (!isset(self::$config) OR self::$config == false) {
			self::$config = new Config;
		}
		return self::$config;
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
		if (!isset($this->config_data) OR $this->config_data === null) {
			$this->read();
		}

		if (!isset($this->config_data[$name])) {
			throw new Exception('Attempting to read unkown config key: '.$name);
		}
		return $this->config_data[$name];
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
		$this->config_data = array(
			/**
			 * Error handling settings
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

			/**
			 * Items per page
			 */
			'items_per_page' => 20,

			/**
			 * Picture formats
			 *
			 * Array containing all possible picture formats
			 */
			'picture_formats' => array(
				'format_name'	=> array(
					'height' => 600,
					'width' => 800
				),
			),

			/**
			 * Archive mailbox
			 */
			'archive_mailbox' => '',

		);
	}
}
