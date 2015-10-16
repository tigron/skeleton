<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use Skeleton\Core\Web\Module;

class Web_Module_Index extends Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = false;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'index.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
	}
}
