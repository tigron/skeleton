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
		$application = APP_NAME;
		if (is_callable(array($this, 'pre_' . $application))) {
			call_user_func_array(array($this, 'pre_' . $application), array());
		}

		$template = Web_Template::Get();
		$module = get_class($this);
		$module = str_replace('module_', '', strtolower($module));
		$template->assign('module', $module);

		if (isset($_REQUEST['action']) AND is_callable(array($this, 'display_'.$_REQUEST['action']))) {
			$template->assign('action', $_REQUEST['action']);
			call_user_func_array(array($this, 'display_'.$_REQUEST['action']), array());
		} else {
			$this->display();
		}

		if ($this->template != null) {
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
