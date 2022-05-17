<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Example\Module;

use \Skeleton\Core\Application\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Core\Web\Template;

class Index extends Module {

	public $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'index.twig';

	/**
	 * Display method.
	 *
	 * @access public
	 */
	public function display(): void {
	}
}
