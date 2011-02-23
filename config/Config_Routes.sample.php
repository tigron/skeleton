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
		'admin' => array(
			'index' => array(
				'routes' => array(
					'en' => '/test/routing/engine',
					'nl' => '/probeer/verwijs/motor',
				),
				'variables' => array(
					'',
					'$action',
					'$action/$id',
				),
			),

			'customer' => array(
				'routes' => array(
					'nl' => 'klant',
					'en' => 'customer',
				),
				'variables' => array(
					'',
					'$action',
					'$action/$id',
				)
			),
		),
	);
}
?>
