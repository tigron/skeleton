<?php
/**
 * Module 404
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Module_404 extends Web_Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = false;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $tpl
	 */
	public $template = null;

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		echo '404';
	}
}
?>
