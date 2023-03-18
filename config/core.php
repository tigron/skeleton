<?php
/**
 * Skeleton configuration
 */

return [
	// Where skeleton can find various parts of your application
	'application_path' => dirname(__FILE__) . '/../app/',
	'asset_paths' => [
		dirname(__FILE__) . '/../lib/external/assets/',
	],
	'tmp_path' => dirname(__FILE__) . '/../tmp/',

	// Display or hide errors
	'debug' => true,

	// Database configuration
	// tip: store this in a separate environment.php which you can ignore in
	// your version control system
	'database' => 'mysqli://username:password@my.server.tld/databasename',
];
