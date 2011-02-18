<?php
/**
 * Logging class
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Log {
	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($id = null) { }

	/**
	 * Log a request
	 *
	 * @access public
	 * @param string $string
	 */
	public static function log_request($string) {
		if (is_dir(TMP_PATH . '/log/') == false) {
			mkdir(TMP_PATH . '/log/');
		}

		file_put_contents(TMP_PATH . '/log/request.log', '[' . date('d/m/Y H:i:s') . '] ' . $string . "\n", FILE_APPEND);
	}
}
?>
