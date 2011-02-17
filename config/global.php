<?php
/**
 * Global parameters
 * Do NOT use global variables, it makes baby Jesus cry. Use Config!
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
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
 * Define the External path
 */
define('EXT_PATH', realpath(LIB_PATH . '/external'));

/**
 * TMP Path
 */
define('TMP_PATH', realpath(ROOT_PATH . '/tmp'));

/**
 * PO Path
 */
define('PO_PATH', realpath(ROOT_PATH . '/po'));

require_once ROOT_PATH . '/config/Config.php';
require_once LIB_PATH . '/base/Errorhandling.php';
require_once LIB_PATH . '/base/Database.php';

?>
