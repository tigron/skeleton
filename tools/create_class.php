<?php
/**
 * Create class script
 *
 * This script allows you to quickly and easily generate an empty
 * class based on a skeleton file.
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once dirname(__FILE__) . '/../config/global.php';

if (!isset($argv[1])){
	echo 'Error: specify table name' . "\n";
	die();
}

$table = $argv[1];

$db = Database::Get();

$fields = $db->listTableFields(strtolower($table));
$empty_class = file_get_contents('empty_class.txt');
$empty_class = str_replace('%%table_name%%', $table, $empty_class);

$parts = explode('_', $table);
foreach ($parts as $key => $part) {
	$parts[$key] = ucfirst($part);
}

$empty_class = str_replace('%%classname%%', implode('_', $parts), $empty_class);
$filename = LIB_PATH . '/' . implode('/', $parts) . '.php';
file_put_contents($filename, $empty_class);
echo 'Generating ' . $filename . "\n";

?>
