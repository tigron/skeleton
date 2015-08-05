<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Util_Application {

	/**
	 * Get the url for an application
	 *
	 * @access public
	 * @param string $table
	 * @return array $fields
	 */
	public static function get_url($name) {
		$applications = Application::get_all();
		foreach ($applications as $application) {
			if ($application->name == $name) {
				$hostnames = $application->config->hostnames;
				return array_shift($hostnames);
			}
		}

		throw new Exception('Application not found');
	}
}
