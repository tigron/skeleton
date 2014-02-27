<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Index extends Web_Module {

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
