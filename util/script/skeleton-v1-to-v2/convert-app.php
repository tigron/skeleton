<?php
if (!isset($argv[1])) {
	die('Usage: php convert-app.php PATH');
}

$dirname = dirname(__FILE__) . '/' . $argv[1];
if (!file_exists($dirname)) {
	die('Given directory does not exist');
}

echo "Give the namespace for all modules found: ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);

$new_namespace = trim($line);
$new_namespace = ltrim($new_namespace, "\\");

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

	// Make a new directory
	$relative = str_replace($dirname, '', $filename);
	$pathinfo = pathinfo($relative);
	$current_dir = $pathinfo['dirname'];
	$parts = explode('/', $current_dir);
	foreach ($parts as $key => $value) {
		$parts[$key] = ucfirst($value);
	}
	$new_dir = implode('/', $parts);
	@mkdir($dirname . '/' . $new_dir, 0755, true);

	// Take the current file
	$content = file_get_contents($filename);
	echo $filename;
	// Check if this file containts a class 'Web_Module_X'
	preg_match('/class Web_Module_(\S*)/', $content, $output_array);

	if (count($output_array) == 0) {
		echo "\t" . 'is not a Web_Module, ignoring' . "\n";
		return;
	}

	// Do the modifications

	// 1. Modify the classname
	$classname = $output_array[1];
	$parts = explode('_', $classname);
	$new_name = array_pop($parts);
	$content = str_replace($output_array[0], 'class ' . $new_name, $content);

	$namespace = implode('\\', $parts);
	$class_namespace = $new_namespace;
	if (count($parts) > 0) {
		$class_namespace .= '\\' . $namespace;
	}

	// 2. replace any reference to \Skeleton\Core\Web\Module by \Skeleton\Core\\Application\Web\Module
	$content = preg_replace('/Skeleton\\\Core\\\Web\\\Module/', 'Skeleton\\\Core\\\Application\\\Web\\\Module', $content);

	// 3. Replace all classnames by there root variant
	preg_match_all('/use (\S*);/', $content, $output_array);

	$classnames = [];
	foreach ($output_array[1] as $use) {
		$parts = explode('\\', $use);
		$classnames[] = array_pop($parts);
	}


	$content = preg_replace_callback('/catch[\s]*\([\s]*(\S*)/', function($matches) use ($classnames) {
		if ($matches[1][0] == '\\') {
			return $matches[0];
		}	

		if (in_array($matches[1], $classnames)) {
			return $matches[0];
		}

		$new_classname = str_replace($matches[1], "\\" . $matches[1], $matches[0]);
		return $new_classname;
	}, $content);

	$content = preg_replace_callback('/(\\\?[A-Z][a-zA-Z_\\\]*)\:\:/', function($matches) use ($classnames) {
		if ($matches[0][0] == '\\') {
			return $matches[0];
		}

		if (in_array($matches[1], $classnames)) {
			return $matches[0];
		}
		$new_classname = str_replace($matches[1], "\\" . $matches[1], $matches[0]);
		return $new_classname;
	}, $content);

	$content = preg_replace_callback('/new\s([A-Z][a-zA-Z_]*)/i', function($matches) use ($classnames) {
		if (in_array($matches[1], $classnames)) {
			return $matches[0];
		}
		return "new \\" . $matches[1];
	}, $content);

	// 4. Add namespace
	$lines = explode("\n", $content);
	$new_content = [];

	$php_open = false;
	$docblock_open = false;
	$docblock_closed = false;
	$first_code = false;
	$namespace_added = false;

	foreach ($lines as $line) {
		if ($namespace_added) {
			$new_content[] = $line;
			continue;
		}

		if (strpos($line, '<?php') !== false) {
			$new_content[] = $line;
			$php_open = true;
			continue;
		}

		if (strpos($line, '/**') !== false) {
			$new_content[] = $line;
			$docblock_open = true;
			continue;
		}

		if (strpos($line, '*/') !== false) {
			$new_content[] = $line;
			$docblock_closed = true;
			continue;
		}

		if (trim($line) == '') {
			$new_content[] = $line;
			continue;
		}

		if ($php_open and $docblock_open and !$docblock_closed) {
			$new_content[] = $line;
			continue;
		}

		if (strpos(trim($line), 'class') === 0 or strpos(trim($line), 'use') === 0) {
			$new_content[] = 'namespace ' . $class_namespace . ';';
			$new_content[] = '';
			$namespace_added = true;
			$new_content[] = $line;
			continue;
		}
	}

	$content = implode("\n", $new_content);
	file_put_contents($filename, $content);
	$pathinfo = pathinfo($filename);
	rename($filename, $dirname . '/' . $new_dir . '/' . ucfirst($pathinfo['basename']));
	echo ' converted' . "\n";
}
