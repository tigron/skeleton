<?php
/**
 * Template class
 * Extends the Smarty object
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once EXT_PATH . '/twig/lib/Twig/Autoloader.php';
require_once LIB_PATH . '/base/Translation.php';

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
	 * Constructor
	 */
	public function __construct() {
		Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem(
			array(
				APP_PATH . '/template',
				APP_PATH . '/macro',
			)
		);

		$this->twig = new Twig_Environment(
			$loader,
			array(
				'cache' => TMP_PATH . '/twig/' . APP_NAME,
				'auto_reload' => true,
			)
		);

		$this->twig->addExtension(new Twig_Extensions_Extension_Tigron());
		$this->twig->addExtension(
			new Twig_Extensions_Extension_I18n(
				array(
					'function_translation' => 'Translation::translate',
					'function_translation_plural' => 'Translation::translate_plural',
				)
			)
		);

		$this->twig->addGlobal('base', $this->twig->loadTemplate('base.macro'));
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
		Translation::set_language(Language::Get());

		$variables = array_merge(
			array(
				'post' => $_POST,
				'get' => $_GET,
				'cookie' => $_COOKIE,
				'server' => $_SERVER,
				'session' => $_SESSION,
				'template' => $this,
				'now' => time()
			),
			$this->environment
		);

		$this->twig->addGlobal('env', $variables);

		if ($this->surrounding) {
			$twig_template = $this->twig->loadTemplate('header.twig');
			echo Util::rewrite_reverse_html($twig_template->render($this->parameters));
		}

		$twig_template = $this->twig->loadTemplate($template);
		echo Util::rewrite_reverse_html($twig_template->render($this->parameters));

		if ($this->surrounding) {
			$twig_template = $this->twig->loadTemplate('footer.twig');
			echo Util::rewrite_reverse_html($twig_template->render($this->parameters));
		}
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
