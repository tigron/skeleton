<?php
/**
 * Relabel all files as belonging to another package. Use with care. ;)
 * 
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once dirname(__FILE__) . '/../config/global.php';

if (!isset($argv[1])){
	echo 'Error: specify package name' . "\n";
	die();
}

$package = $argv[1];

$config = Config::Get();
$directories = array(
	LIB_PATH . '/model',
	LIB_PATH . '/base',
	ROOT_PATH . '/config',
	ROOT_PATH . '/webroot'
);

foreach ($config->applications as $application) {
	$directories[] = ROOT_PATH . '/app/' . $application . '/module';
}

foreach ($directories as $directory) {
	set_package_on_dir($directory);
}

function set_package_on_dir($directory) {
	$files = scandir($directory);

	foreach($files as $file) {
		if ($file[0] == '.') {
			continue;
		}

		if (is_dir($directory . '/' . $file)) {
			set_package_on_dir($directory . '/' . $file);
			continue;
		}

		set_package_on_file($directory . '/' . $file);
	}
}


function set_package_on_file($filename) {
	global $package;
	echo 'Setting package for ' . $filename . "\n";
	$content = file_get_contents($filename);

	if (strpos($content, '@package ') === false) {
		return;
	}

	$lines = explode("\n", $content);
	foreach ($lines as $number => $line) {
		if (strpos($line, '@package') !== false) {
			$lines[$number] = ' * @package ' . $package;
		}
	}

	file_put_contents($filename, implode("\n", $lines));
}
