<?php
/**
 * Event handler for Application events
 */

namespace App\Admin\Event;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Session;
use \Skeleton\Database\Database;

class Application extends \Skeleton\Core\Event {

	/**
	 * Bootstrapping the application
	 *
	 * @access public
	 */
	public function bootstrap(\Skeleton\Core\Web\Module $module) {
		// The bootstrap event will be called at the very beginning
		// Some common uses can be found below as examples

		/*
		// Assign the sticky session object to our template
		$template = \Skeleton\Core\Web\Template::get();
		$sticky_session = new \Skeleton\Core\Web\Session\Sticky();
		$template->add_environment('sticky_session', $sticky_session);
		*/

		/*
		// Ensure the user is authenticated if the called module requires that
		if (!isset($_SESSION['user']) && $module->is_login_required()) {
			Session::redirect('/login');
		}
		*/

		/*
		// Call the secure() method on the module, if it exists
		if (is_callable([ $module, 'secure' ])) {
			if (!$module->secure()) {
				\Skeleton\Core\Web\Session::redirect('/login');
			}
		}
		*/

		/*
		// Store the current user in the User object for easy access
		if (isset($_SESSION['user'])) {
			\User::set($_SESSION['user']);
		}
		*/
	}

	/**
	 * Teardown of the application
	 *
	 * @access public
	 */
	public function teardown(\Skeleton\Core\Web\Module $module) {
	}
}

