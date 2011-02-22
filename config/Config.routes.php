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
		 *
			'test/routing/engine' => array(
				'target' => 'index',
				'variables' => array(
					'$action/$object_id',
					'$action/$object_id/$condition',
				)
			)
		*/

		'/user' => array(
			'target' => 'user',
			'variables' => array(
				'$action'
			)
		)
	);
}
?>