<?php
/**
 * Global parameters
 * Do NOT use global variables, it makes baby Jesus cry. Use Config!
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

/**
 * Define the Root path
 */
define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

/**
 * Define the path to the Library
 */
define('LIB_PATH', realpath(ROOT_PATH . '/lib'));
/**
 * TMP Path
 */
define('TMP_PATH', realpath(ROOT_PATH . '/tmp'));

require_once 'Config.php';
require_once LIB_PATH . '/base/Util.php';


require_once dirname(__FILE__) . '/../lib/base/Bootstrap.php';
Bootstrap::boot();
