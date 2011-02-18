<?php
/**
 * Translation class
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Translation {

	/**
	 * Translation
	 *
	 * @access private
	 * @var Translation $translation
	 */
	private static $translation = null;

	/**
	 * Language
	 *
	 * @access private
	 * @var Language $language
	 */
	private $language = null;

	/**
	 * Strings
	 *
	 * @access private
	 * @var array $strings
	 */
	private $strings = array();


	/**
	 * Constructor
	 *
	 * @access public
	 * @param Language $language
	 */
	public function __construct(Language $language) {
		$this->language = $language;
		$this->reload_po_file($language);
		$this->load_strings();
	}

	/**
	 * Translate a string
	 *
	 * @access public
	 * @param string $string
	 * @return string $string
	 */
	public function translate_string($string) {
		if (!isset($this->strings[$string]) OR $this->strings[$string] == '') {
			return '[NT]' . $string;
		}

		return $this->strings[$string];
	}

	/**
	 * Set the language for the translation
	 *
	 * @access public
	 * @param Language $language
	 * @return Translation $translation
	 */
	public static function set_language(Language $language) {
		self::$translation = new Translation($language);
		return self::$translation;
	}

	/**
	 * Get a translation object
	 *
	 * @access public
	 * @return Translation $translation
	 */
	public static function get() {
		if (!isset(self::$translation)) {
			throw new Exception('Language not set');
		}
		
		return self::$translation;
	}

	/**
	 * Translate a string
	 *
	 * @access public
	 * @return string $translated_string
	 * @param string $string
	 */
	public static function translate($string) {
		$translation = Translation::Get();
		return $translation->translate_string($string);
	}

	/**
	 * Translate a plural string
	 *
	 * @access public
	 * @return string $translated_string
	 * @param string $string
	 */
	public static function translate_plural($string) {
		$translation = Translation::Get();
		return $translation->translate_string($string);
	}

	/**
	 * Read the po files
	 *
	 * @access public
	 */
	private function reload_po_file() {
		$application = APP_NAME;
		if (file_exists(PO_PATH . '/' . $this->language->name_short . '/' . $application . '.po') AND file_exists(TMP_PATH . '/languages/' . $this->language->name_short . '/' . $application . '.php')) {
			$po_file_modified = filemtime(PO_PATH . '/' . $this->language->name_short . '/' . $application . '.po');
			$array_modified = filemtime(TMP_PATH . '/languages/' . $this->language->name_short . '/' . $application . '.php');

			if ($array_modified >= $po_file_modified) {
				return;
			}
		}

		$po_strings = Util::load_po(PO_PATH . '/' . $this->language->name_short . '/' . $application . '.po');

		if (!file_exists(TMP_PATH . '/languages/' . $this->language->name_short)) {
			mkdir(TMP_PATH . '/languages/' . $this->language->name_short, 0755, true);
		}

		file_put_contents(TMP_PATH . '/languages/' . $this->language->name_short . '/' . $application . '.php', '<?php $strings = ' . var_export($po_strings, true) . '?>');
	}

	/**
	 * Load the strings
	 *
	 * @access private
	 */
	private function load_strings() {
		if (file_exists(TMP_PATH . '/languages/' . $this->language->name_short . '/' . APP_NAME . '.php')) {
			require_once TMP_PATH . '/languages/' . $this->language->name_short . '/' . APP_NAME . '.php';
			$this->strings = $strings;
		}
	}
}
?>
