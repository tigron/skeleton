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
				'cache' => TMP_PATH . '/cache/' . APP_NAME,
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

		$twig_template = $this->twig->loadTemplate('header.tpl');
		echo Util::reverse_rewrite($twig_template->render($this->parameters));

		$twig_template = $this->twig->loadTemplate($template);
		echo Util::reverse_rewrite($twig_template->render($this->parameters));

		$twig_template = $this->twig->loadTemplate('footer.tpl');
		echo Util::reverse_rewrite($twig_template->render($this->parameters));
	}
}
?>
