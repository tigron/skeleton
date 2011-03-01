<?php
/**
 * Route configuration class
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */
class Config_Routes {

	/**
	 * Routes array
	 *
	 * @var $routes
	 * @access public
	 */
	public static $routes = array(

		/**
		 * This is a demo route
		*/
		// Application
		'admin' => array(
			// Module
			'index' => array(
				// Routes with language as key, use default as key for default
				'routes' => array(
					'default' => '/default/route/to/index',
					'en' => '/test/routing/engine',
				),
				// Variables that match the route, if no variables should match as well, use ''				
				'variables' => array(
					'',
					'$action',
					'$action/$id',
				),
			),

			'demo' => array(
				'routes' => array(
					'default' => '/default/route/to/demo',
					'nl' => '/standaard/route/naar/demo'
				),
				'variables' => array(
					'',
					'$action',
				)
			),
		),
	);
}
?>
