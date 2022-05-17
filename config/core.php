<?php
/**
 * Skeleton configuration
 */

return [
	// Where skeleton can find various parts of your application
	'application_dir' => dirname(__FILE__) . '/../app/',
	'asset_dir' => dirname(__FILE__) . '/../lib/external/assets/',
	'tmp_dir' => dirname(__FILE__) . '/../tmp/',
	'lib_dir' => dirname(__FILE__) . '/../lib/',

	// Display or hide errors
	'debug' => true,

	// Database configuration
	// tip: store this in a separate environment.php which you can ignore in
	// your version control system
	'database' => 'mysqli://username:password@my.server.tld/databasename',
];
