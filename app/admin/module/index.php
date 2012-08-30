<?php
/**
 * Module Index
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Module_Index extends Web_Module {
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
	 * @var string $template
	 */
	public $template = 'index.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Web_Template::Get();
		$options = array (
						array('id' => 1, 'name' => 'option 1'),
						array('id' => 2, 'name' => 'option 2'),
						array('id' => 3, 'name' => 'option 3')
		);
		$template->assign('dummy_options', $options);
	}

	/**
	 * Demo action with redirect
	 *
	 * @access public
	 */
	public function display_demo_redirect() {
		Web_Session::redirect('/index?action=i_have_been_redirected');
	}
}
?>
