<?php
/**
 * Hooks
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 */

class Hook_Admin {
	/**
	 * Bootstrap the application
	 *
	 * @access private
	 */
	public static function bootstrap(\Skeleton\Core\Web\Module $module) {
		// Bootsprap the application
		// Example: check if we need to log in, if we do and we aren't, redirect
		/*
		if ($module->is_login_required()) {
			if (!isset($_SESSION['user'])) {
				\Skeleton\Core\Web\Session::destroy();
				\Skeleton\Core\Web\Session::redirect('/login');
			}

			User::set($_SESSION['user']);
		}
		*/
	}

	/**
	 * Teardown of the application
	 *
	 * @access private
	 */
	public static function teardown(\Skeleton\Core\Web\Module $module) {
		// Do your cleanup jobs here
	}
}
