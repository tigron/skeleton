<?php
/**
 * Application class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */
class Application {

	/**
	 * Application
	 *
	 * @var Application $application
	 * @access private
	 */
	private static $application = null;

	/**
	 * Path
	 *
	 * @var string $path
	 * @access public
	 */
	public $path = null;

	/**
	 * Media Path
	 *
	 * @var string $media_path
	 * @access public
	 */
	public $media_path = null;

	/**
	 * Module Path
	 *
	 * @var string $module_path
	 * @access public
	 */
	public $module_path = null;

	/**
	 * Template path
	 *
	 * @var string $template_path
	 * @ccess public
	 */
	public $template_path = null;

	/**
	 * Name
	 *
	 * @var string $name
	 * @access public
	 */
	public $name = null;

	/**
	 * Hostname
	 *
	 * @var string $hostname
	 * @access public
	 */
	public $hostname = null;

	/**
	 * Relative URI to the application's base URI
	 *
	 * @var string $relative_uri
	 * @access public
	 */
	public $relative_uri = null;

	/**
	 * Language
	 *
	 * @access public
	 * @var Language $language
	 */
	public $language = null;

	/**
	 * Config object
	 *
	 * @access public
	 * @var Config $config
	 */
	public $config = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Search module
	 *
	 * @access public
	 * @param string $request_uri
	 */
	public function route($request_uri) {
		/**
		 * Remove leading slash
		 */
		if ($request_uri[0] == '/') {
			$request_uri = substr($request_uri, 1);
		}

		$request_parts = explode('/', $request_uri);
		$routes = Config::Get()->routes;

		/**
		 * We need to find the route that matches the most the fixed parts
		 */
		$matched_module = null;
		$best_matches_fixed_parts = 0;
		$route = '';

		foreach ($routes as $module => $uris) {
			foreach ($uris as $uri) {
				if (isset($uri[0]) AND $uri[0] == '/') {
					$uri = substr($uri, 1);
				}
				$parts = explode('/', $uri);

				$matches_fixed_parts = 0;
				$match = true;

				foreach ($parts as $key => $value) {
					if (!isset($request_parts[$key])) {
						$match = false;
						continue;
					}

					if ($value == $request_parts[$key]) {
						$matches_fixed_parts++;
						continue;
					}

					if (isset($value[0]) AND $value[0] == '$') {
						// This is a variable, we do not increase the fixed parts
						continue;
					}
					$match = false;
				}

				if ($match and count($parts) == count($request_parts)) {
					if ($matches_fixed_parts >= $best_matches_fixed_parts) {
						$best_matches_fixed_parts = $matches_fixed_parts;
						$route = $uri;
						$matched_module = $module;
					}
				}
			}
		}

		if ($matched_module === null) {
			throw new Exception('No matching route found');
		}

		/**
		 * We now have the correct route
		 * Now fill in the GET-parameters
		 */
		$parts = explode('/', $route);

		foreach ($parts as $key => $value) {
			if ($value[0] == '$') {
				$value = substr($value, 1);
				if (strpos($value, '[') !== false) {
					$value = substr($value, 0, strpos($value, '['));
				}
				$_GET[$value] = $request_parts[$key];
				$_REQUEST[$value] = $request_parts[$key];
			}
		}

		$filename = str_replace('web_module_', '', $matched_module);
		$filename = str_replace('_', '/', $filename);
		$filename = strtolower($filename);

		$filepath = $this->module_path . '/' . $filename . '.php';

		// Check if the file exists before actually requiring it
		if (file_exists($filepath)) {
			require_once $filepath;
		} else {
			throw new Exception('Could not find file: ' . $filepath);
		}

		$module = new $matched_module();
		return $module;
	}

	/**
	 * Bootstrap the application
	 *
	 * @access public
	 */
	public function bootstrap(Web_Module $module) {
		// FIXME: requiring the file and determining the classname should be
		// generalised
		if (file_exists($this->path . '/config/Hook.php')) {
			require_once $this->path . '/config/Hook.php';
			$classname = 'Hook_' . ucfirst($this->name);

			if (method_exists($classname, 'bootstrap')) {
				$classname::bootstrap($module);
			}
		}
	}

	/**
	 * Tear down the application
	 *
	 * @access public
	 */
	public function teardown(Web_Module $module) {
		// FIXME: requiring the file and determining the classname should be
		// generalised
		if (file_exists($this->path . '/config/Hook.php')) {
			require_once $this->path . '/config/Hook.php';
			$classname = 'Hook_' . ucfirst($this->name);

			if (method_exists($classname, 'teardown')) {
				$classname::teardown($module);
			}
		}
	}

	/**
	 * Get
	 *
	 * Try to fetch the current application
	 *
	 * @access public
	 * @return Application $application
	 */
	public static function get() {
		if (self::$application === null) {
			throw new Exception('No application set');
		}

		return self::$application;
	}

	/**
	 * Set
	 *
	 * @access public
	 * @param Application $application
	 */
	public static function set(Application $application) {
		self::$application = $application;
	}

	/**
	 * Detect
	 *
	 * @param string $hostname
	 * @param string $request_uri
	 * @access public
	 * @return Application $application
	 */
	public static function detect($hostname, $request_uri) {
		// If we already have a cached application, return that one
		if (self::$application !== null) {
			return Application::get();
		}

		// If multiple host headers have been set, use the last one
		if (strpos($hostname, ', ') !== false) {
			list($hostname, $discard) = array_reverse(explode(', ', $hostname));
		}

		// Find matching applications
		$applications = self::get_all();
		$matched_applications = [];

		// Regular matches
		foreach ($applications as $application) {
			if (in_array($hostname, $application->config->hostnames)) {
				$matched_applications[] = $application;
			}
		}

		// If we don't have any matched applications, try to match wildcards
		if (count($matched_applications) === 0) {
			foreach ($applications as $application) {
				$wildcard_hostnames = $application->config->hostnames;
				foreach ($wildcard_hostnames as $key => $wildcard_hostname) {
					if (strpos($wildcard_hostname, '*') === false) {
						unset($wildcard_hostnames[$key]);
					}
				}

				if (count($wildcard_hostnames) == 0) {
					continue;
				}

				foreach ($wildcard_hostnames as $wildcard_hostname) {
					if (fnmatch($wildcard_hostname, $hostname)) {
						$matched_applications[] = $application;
					}
				}
			}
		}

		// Set required variables in the matched Application objects
		foreach ($matched_applications as $key => $application) {
			 // Set the relative request URI according to the application
			if (isset($application->config->base_uri)) {
				$application->request_relative_uri = str_replace($application->config->base_uri, '', $request_uri);
			} else {
				$application->request_relative_uri = $request_uri;
			}

			$application->hostname = $hostname;
			$matched_applications[$key] = $application;
		}

		// Now that we have matching applications, see if one matches the
		// request specifically. Otherwise, simply return the first one.
		$matched_applications_sorted = [];
		foreach ($matched_applications as $application) {
			if (isset($application->config->base_uri)) {
				if (strpos($request_uri, $application->config->base_uri) === 0) {
					$matched_applications_sorted[strlen($application->config->base_uri)] = $application;
				}
			} else {
				$matched_applications_sorted[0] = $application;
			}
		}

		// Sort the matched array by key, so the most specific one is at the end
		ksort($matched_applications_sorted);

		if (count($matched_applications_sorted) > 0) {
			// Get the most specific one
			$application = array_pop($matched_applications_sorted);
			Application::set($application);
			return Application::get();
		}

		throw new Exception('No application found for ' . $hostname);
	}

	/**
	 * Get all
	 *
	 * @access public
	 * @return array $applications
	 */
	public static function get_all() {
		$application_directories = scandir(ROOT_PATH . '/app');
		$application = [];
		foreach ($application_directories as $application_directory) {
			if ($application_directory[0] == '.') {
				continue;
			}

			if (file_exists(ROOT_PATH . '/app/' . $application_directory . '/config/Config.php')) {
				require_once ROOT_PATH . '/app/' . $application_directory . '/config/Config.php';
				$classname = 'Config_' . ucfirst($application_directory);
				$config = new $classname;
			} else {
				$config = new Config();
			}

			$app_path = realpath(ROOT_PATH . '/app/' . $application_directory);
			$application = new Application();
			$application->media_path = $app_path . '/media/';
			$application->module_path = $app_path . '/module/';
			$application->template_path = $app_path . '/template/';
			$application->path = $app_path;
			$application->name = $application_directory;
			$application->config = $config;
			$application->language = Language::get_by_name_short($config->default_language);
			$applications[] = $application;
		}
		return $applications;
	}

	/**
	 * Get application by name
	 *
	 * @access public
	 * @param string $name
	 * @return Application $application
	 */
	public static function get_by_name($name) {
		$applications = self::get_all();
		foreach ($applications as $application) {
			if ($application->name == $name) {
				return $application;
			}
		}

		throw new Exception('Application ' . $name . ' does not exists.');
	}
}
