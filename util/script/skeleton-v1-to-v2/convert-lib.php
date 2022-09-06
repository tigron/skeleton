<?php
if (!isset($argv[1])) {
	die('Usage: php convert-lib.php PATH');
}

$dirname = dirname(__FILE__) . '/' . $argv[1];
if (!file_exists($dirname)) {
	die('Given directory does not exist');
}

$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirname));
$files = array_filter(iterator_to_array($iterator), function($file) {
	return $file->isFile();
});

foreach ($files as $file) {
	if ($file->getExtension() != 'php') {
		continue;
	}

	handle_file($file->getPathName());
}

system('find ' . $dirname . ' -empty -type d -delete', $retval);

function handle_file($filename) {
	global $dirname;
	global $new_namespace;
	echo $filename;
	$content = file_get_contents($filename);
	// 2. replace any reference to \Skeleton\Core\Web\Module by \Skeleton\Core\\Application\Web\Module
	$content = preg_replace('/Config\:\:Get/', '\\\Skeleton\\\Core\\\Config::Get', $content);
	$content = preg_replace('/Config\:\:get/', '\\\Skeleton\\\Core\\\Config::Get', $content);	
	file_put_contents($filename, $content);
	echo ' converted' . "\n";
}
