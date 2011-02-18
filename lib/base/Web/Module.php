<?php
/**
 * Module startup and handling
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

abstract class Web_Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = true;

	/**
	 * Accept request and dispatch it to the module
	 *
	 * @access public
	 */
	public function accept_request() {
		if ($this->login_required AND !isset($_SESSION['user'])) {
			Web_Session::Redirect('/');
		}

		$template = Web_Template::Get();

		if (isset($_POST['action']) AND is_callable(array($this, 'display_'.$_POST['action']))) {
			$template->assign('action', $_POST['action']);
			call_user_func_array(array($this, 'display_'.$_POST['action']), array());
		} else {
			$this->display();
		}

		if ($this->template != null) {
			$template = Web_Template::Get();
			$template->display($this->template);
		}
	}

	/**
	 * Display method
	 *
	 * All requests will be handled by this method
	 *
	 * @access public
	 */
	abstract public function display();
}
?>
