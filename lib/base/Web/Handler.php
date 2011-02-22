<?php
/**
 * HTTP request Handler
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once LIB_PATH . '/base/Web/Module.php';
require_once LIB_PATH . '/base/Web/Media.php';
require_once LIB_PATH . '/base/Web/Session.php';
require_once LIB_PATH . '/base/Log.php';
require_once LIB_PATH . '/model/Language.php';

class Web_Handler {
	/**
	 * Handle the request and send it to the correct module
	 *
	 * @access public
	 */
	public static function run() {
		/**
		 * Record the start time in microseconds
		 */
		$start = microtime(true);

		/**
		 * Start the session
		 */
		Web_Session::start();

		/**
		 * Determine the request type
		 */
		$query_string = explode('?', $_SERVER['REQUEST_URI']);
		$request_parts = explode('/', $query_string[0]);
		if (isset($query_string[1])) {
			$request_parts[count($request_parts)-1] = $request_parts[count($request_parts)-1] . '?' . $query_string[1];
		}

		foreach ($request_parts as $key => $part) {
			if (strpos($part, '?') !== false) {
				$request_parts[$key] = substr($part, 0, strpos($part, '?'));
				$part = substr($part, 0, strpos($part, '?'));
			}

			if ($part == '') {
				unset($request_parts[$key]);
			}
		}

		// reorganize request_parts array
		$request_parts = array_merge($request_parts, array());

		/**
		* Get the config
		*/
		$config = Config::Get();
		/**
		 * Define the application
		 */
		$applications = $config->applications;
		if (!isset($applications[$_SERVER['SERVER_NAME']])) {
			echo '404';
			return;
		}

		$application = $applications[$_SERVER['SERVER_NAME']];

		define('APP_NAME',		$application);
		define('APP_PATH',		realpath(ROOT_PATH . '/app/' . $application));
		define('MEDIA_PATH',	APP_PATH . '/media');
		define('MODULE_PATH',	APP_PATH . '/module');
		define('TEMPLATE_PATH',	APP_PATH . '/template');

		/**
		 * Check if this is an image or a media type
		 */
		Web_Media::Detect($request_parts);

		/**
		 * Set the language to the template
		 */
		require_once LIB_PATH . '/base/Web/Template.php';

		/**
		 * Set language
		 */
		// Set the language to something sensible if it isn't set yet
		if (!isset($_SESSION['language'])) {
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				$languages = Language::get_all();

				foreach ($languages as $language) {
					if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], $language->name_short) !== false) {
						$language = $language;
						$_SESSION['language'] = $language;
					}
				}
			}

			if (!isset($_SESSION['language'])) {
				$language = Language::get_by_name_short($config->default_language);
				$_SESSION['language'] = $language;
			}
		}

		// If the user explicitly asked for a language, try to set it
		if (isset($_GET['language'])) {
			try {
				$language = Language::get_by_name_short($_GET['language']);
				$_SESSION['language'] = $language;
			} catch (Exception $e) {
				$_SESSION['language'] = Language::get_by_name_short($config->default_language);
			}
		}

		// If the user explicitly asked for a language in the URI, try to set it
		if (isset($request_parts[0]) AND strlen($request_parts[0]) == 2) {
			try {
				$language = Language::get_by_name_short($request_parts[0]);
				$_SESSION['language'] = $language;
				array_shift($request_parts);
			} catch (Exception $e) { }
		}

		Language::set($_SESSION['language']);

		$template = Web_Template::Get();
		$template->assign('language', $_SESSION['language']);

		/**
		 * Look for the Module, try to match routes
		 */
		if (count($request_parts) == 0) {
			// If no module was requested, default to 'index'
			$request_parts[] = 'index';
		} else {
			// Check if there is a route defined for the request
			$route = '';
			foreach($request_parts as $key => $request_part) {
				$route .= '/' . $request_part;
				if (strpos($route, '/') == 0) {
					$route = substr($route, 1);
				}

				// Check if the request is defined by a route
				if (array_key_exists($route, $config->routes)) {
					$_GET['system']['route'] = $route;
					$variables = array_slice($request_parts, $key+1);

					$variable_match = null;
					foreach ($config->routes[$route]['variables'] as $variable_possibility) {
						if (count($variables) == substr_count($variable_possibility, '$')) {
							$variable_match = $variable_possibility;
							$_GET['system']['variables'] = $variable_match;
							break;
						}
					}

					if ($variable_match === null) {
						throw new Exception('Route matches but no variable match found');
					}

					// Replace all the variables passed through the URI by the ones defined in the pattern
					$variable_parts = explode('/', $variable_match);
					foreach($variable_parts as $key => $variable_part) {
						$_GET[str_replace('$', '', $variable_part)] = $variables[$key];
					}

					$request_parts = explode('/', $config->routes[$route]['target']);

					break;
				}
			}
		}

		$last_part = $request_parts[count($request_parts)-1];
		if (strpos($last_part,'?')) {
			$last_part = substr($last_part, 0, strpos($last_part, '?'));
			$request_parts[count($request_parts)] = $last_part;
		}

		header('Content-type: text/html; charset=utf-8');

		if (file_exists(strtolower(MODULE_PATH . '/' . implode('/', $request_parts) . '.php'))) {
			// Does the module exist on itself?
			require_once(MODULE_PATH . '/' . implode('/', $request_parts) . '.php');

			$classname = 'Module_' . implode('_', $request_parts);
			$module = new $classname();
			$module->accept_request();
		} elseif (file_exists(strtolower(MODULE_PATH . '/' . implode('/', $request_parts) . '/index.php'))) {
			// If not, is the module the user asked for actually a directory?
			require_once(MODULE_PATH . '/' . implode('/', $request_parts) . '/index.php');

			$classname = 'Module_' . implode('_', $request_parts) . '_Index';
			$module = new $classname();
			$module->accept_request();
		} else {
			header("HTTP/1.0 404 Not Found");
			require_once MODULE_PATH . '/404.php';

			$module = new Module_404();
			$module->accept_request();
		}

		// Record debug information
		if ($config->debug == true) {
			$database = Database::get();
			$queries = substr_count($database->getDebugOutput(), 'execute(');
			$execution_time = microtime(true) - $start;

			Log::log_request('Request: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' -- IP: ' . $_SERVER['REMOTE_ADDR'] . ' -- Queries: ' . $queries . ' -- Time: ' . $execution_time);
		}
	}
}
?>
