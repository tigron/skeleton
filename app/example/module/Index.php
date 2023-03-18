<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Example\Module;

use Skeleton\Application\Web\Module;
use Skeleton\Application\Web\Template;
use Skeleton\Core\Http\Session;

class Index extends Module {

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
