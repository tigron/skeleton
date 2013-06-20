<?php
/**
 * Template class
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once LIB_PATH . '/base/Translation.php';
require_once LIB_PATH . '/base/Web/Template/Twig/Extension/Default.php';
require_once LIB_PATH . '/base/Web/Template/Twig/Extension/I18n/Tigron.php';

class Exception_Template_Syntax extends Exception {}

class Web_Template {
	/**
	 * Template object
	 *
	 * @var mixed $template
	 * @access private
	 */
	private static $template = null;

	/**
	 * Twig object
	 *
	 * @var Twig $twig
	 * @access private
	 */
	private $twig = null;

	/**
	 * Parameters
	 *
	 * @var array $parameters
	 * @access private
	 */
	private $parameters = array();

	/**
	 * Environment variables
	 *
	 * @var array $environment
	 * @access private
	 */
	private $environment = array();

	/**
	 * Unique ID within the template
	 *
	 * @var int $unique_id
	 * @access private
	 */
	private $unique_id = 1;

	/**
	 * Surrounding
	 *
	 * @var bool $surrounding
	 */
	public $surrounding = true;
	
	/**
	 * App path
	 *
	 * @access private
	 * @var string $app_path
	 */
	private $app_path = '';
	
	/**
	 * App name
	 *
	 * @access private
	 * @var string $app_name
	 */
	private $app_name = '';

	/**
	 * Constructor
	 */
	public function __construct($app_name = null, $app_path = null) {
		if ($app_name === null) {
			$this->app_name = APP_NAME;
		} else {
			$this->app_name = $app_name;
		}
		if ($app_path === null) {
			$this->app_path = APP_PATH;
		} else {
			$this->app_path = $app_path;
		}

		Twig_Autoloader::register();
		
		$loader_paths = array();
		if (file_exists($this->app_path . '/macro')) {
			$loader_paths[] = $this->app_path . '/macro';
		}

		if (file_exists($this->app_path . '/template')) {
			$loader_paths[] = $this->app_path . '/template';
		}

		$loader = new Twig_Loader_Filesystem($loader_paths);

		$this->twig = new Twig_Environment(
			$loader,
			array(
				'debug' => false,
				'cache' => TMP_PATH . '/twig/' . $this->app_name,
				'auto_reload' => true,
			)
		);

		

		$this->twig->addExtension(new Template_Twig_Extension_Default());
		$this->twig->addExtension(new Twig_Extensions_Extension_Text());
		$this->twig->addExtension(new Twig_Extension_Debug());
		$this->twig->addExtension(
			new Twig_Extensions_Extension_I18n_Tigron()
		);

		if (file_exists($this->app_path . '/macro/base.macro')) {
			try {
				$this->twig->addGlobal('base', $this->twig->loadTemplate('base.macro'));
			} catch (Twig_Error_Syntax $e) {
				throw new Exception_Template_Syntax($e->getmessage());
			}
		}

		if (file_exists($this->app_path . '/macro/form.macro')) {
			try {
				$this->twig->addGlobal('form', $this->twig->loadTemplate('form.macro'));
			} catch (Twig_Error_Syntax $e) {
				throw new Exception_Template_Syntax($e->getmessage());
			}
		}
	}

	/**
	 * Get assigned parameters
	 *
	 * @return array $parameters
	 */
	public function get_assigned() {
		return $this->parameters;
	}

	/**
	 * Get a unique ID within the template
	 *
	 * @return int $unique
	 */
	public function get_unique() {
		return $this->unique_id++;
	}

	/**
	 * Add a global variable to the template
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function add_env($name, $value) {
		$this->environment[$name] =  $value;
	}

	/**
	 * Assign variables to the template
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function assign($key, $value) {
		$this->parameters[$key] = $value;
	}

	/**
	 * Display the template
	 *
	 * @param string $template
	 * @access public
	 */
	public function display($template) {
		Translation::configure(Language::Get(), $this->app_name);
		$html = '';
		$variables = array_merge(
			array(
				'post' => $_POST,
				'get' => $_GET,
				'cookie' => $_COOKIE,
				'server' => $_SERVER,
				'session' => $_SESSION,
				'template' => $this
			),
			$this->environment
		);

		$this->twig->addGlobal('env', $variables);
		echo $this->render($template);
	}
	
	/**
	 * Render the template
	 *
	 * @access public
	 * @return string $html
	 */
	public function render($template, $reverse_rewrite = true) {
		try {
			$twig_template = $this->twig->loadTemplate($template);
			$html = $twig_template->render($this->parameters);
			if ($reverse_rewrite) {
				$html = Util::rewrite_reverse_html($html);
			}
		} catch (Twig_Error_Syntax $e) {
			throw new Exception_Template_Syntax($e->getmessage());
		}	
		return $html;
	}

	/**
	 * Get function, returns Template object
	 *
	 * @return Twig
	 * @access public
	 */
	public static function get() {
		if (!isset(self::$template) OR self::$template == null) {
			self::$template = new self();
		}

		return self::$template;
	}
}
?>
