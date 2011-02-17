<?php
/**
 * Initialize the application
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once '../config/global.php';
require_once LIB_PATH . '/base/Web/Handler.php';
Web_Handler::Run();

?>
