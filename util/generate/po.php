<?php
/**
 * This script will generate po files based on all strings
 * that needs to be translated from the templates
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once dirname(__FILE__) . '/../../config/global.php';

require_once LIB_PATH . '/model/Language.php';
require_once LIB_PATH . '/base/Util.php';
require_once LIB_PATH . '/base/Translation.php';
require_once LIB_PATH . '/base/Web/Template.php';

$template_directories = array('template');

$config = Config::Get();
Twig_Autoloader::register();

// This is a dummy line. A language is required, otherwise rendering the template for cache will cause an exception
Translation::configure(Language::get_by_id(1), 'admin');


$applications_array = $config->applications;
$applications = array();
foreach ($applications_array as $application) {
	if (is_array($application)) {
		$applications[] = $application['name'];
	} else {
		$applications[] = $application;
	}
}

$to_generate = array();
foreach ($applications as $application) {
	if (file_exists(ROOT_PATH . '/app/' . $application . '/template')) {
		$to_generate[] = array(	'app_name'	=>	$application,
								'app_path'	=>	ROOT_PATH . '/app/' . $application . '/template');
	}
}
if (file_exists(STORE_PATH . '/pdf/template')) {
	$to_generate[] = array(	'app_name' => 'pdf',
							'app_path' => STORE_PATH . '/pdf/template');
}
if (file_exists(STORE_PATH . '/email/template')) {
	$to_generate[] = array(	'app_name' => 'email',
							'app_path' => STORE_PATH . '/email/template');
}
$applications = $to_generate;

echo 'creating template cache' . "\n";
foreach ($applications as $application) {
	$application_path = str_replace('template', '', $application['app_path']);
	$template = new Web_Template($application['app_name'], $application_path);

	$files = get_files_from_directory($application['app_path']);
	foreach ($files as $file) {
		$template_file = str_replace($application['app_path'] . '/' , '', $file);
		echo $application_path . $template_file . "\n";

		$template->render($template_file, false);
	}
}
echo "\n\n";

/**
 * Second: make a copy of every po file
 */
echo 'copying all po files' . "\n";
foreach ($applications as $application) {
	$languages = Language::get_all();

	foreach ($languages as $language) {
		$config = Config::Get();
		if ($config->base_language == $language->name_short) {
			continue;
		}
		if (file_exists(PO_PATH . '/' . $language->name_short . '/' . $application['app_name'] . '.po')) {
			rename(PO_PATH . '/' . $language->name_short . '/' . $application['app_name'] . '.po', PO_PATH . '/' . $language->name_short . '/' . $application['app_name'] . '_def.po');
		}
	}
}

/**
 * Third: translate every application
 */
echo 'translate applications' . "\n";
foreach ($applications as $application) {
	translate_application($application);
}

/**
 * Fourth: do a merge from each reference file
 */
foreach ($applications as $application) {
	$languages = Language::get_all();

	foreach ($languages as $language) {
		$config = Config::Get();
		if ($config->base_language == $language->name_short) {
			continue;
		}

		if (!file_exists(PO_PATH . '/' . $language->name_short . '/' . $application['app_name'] . '_def.po')) {
			continue;
		}

		Util::po_merge(PO_PATH . '/' . $language->name_short . '/' . $application['app_name'] . '_def.po', PO_PATH . '/' . $language->name_short . '/' . $application['app_name'] . '.po');
		rename(PO_PATH . '/' . $language->name_short . '/' . $application['app_name'] . '_def.po', PO_PATH . '/' . $language->name_short . '/' . $application['app_name'] . '.po');
	}
}

/**
 * Function
 *
 * Translate application: take all files from the cache and translate
 * them.
 */
function translate_application($application) {
	if (is_dir(TMP_PATH . '/twig/' . $application['app_name'])) {
		$files = scandir(TMP_PATH . '/twig/' . $application['app_name']);

		foreach ($files as $file) {
			if ($file[0] == '.') {
				continue;
			}

			if (is_dir(TMP_PATH . '/twig/' . $application['app_name'] . '/' . $file)) {
				translate_directory(TMP_PATH . '/twig/' . $application['app_name'] . '/' . $file, $application['app_name']);
				continue;
			}
		}
	}
}

/**
 * Get files from directory recursively
 */
function get_files_from_directory($directory) {
	$files = scandir($directory);
	$return_list = array();

	foreach ($files as $key => $file) {
		
		if ($file[0] == '.') {
			unset($files[$key]);
			continue;
		}

		if (is_dir($directory . '/' . $file)) {
			unset($files[$key]);
			$return_list = array_merge($return_list, get_files_from_directory($directory . '/' . $file));
			continue;
		}
		$return_list[] = $directory . '/' . $file;		

	}
	return $return_list;
}

/**
 * Function
 *
 * Translate directory: take all files from a certain directory and translate
 * them.
 */
function translate_directory($directory, $application) {
	$files = scandir($directory);

	foreach ($files as $file) {
		if ($file[0] == '.') {
			continue;
		}

		if (is_dir($directory . '/' . $file)) {
			translate_directory($directory . '/' . $file, $application);
			continue;
		}

		translate_file($directory . '/' . $file, $application);
	}
}

/**
 * Function
 *
 * Translate file: translate a certain file
 */
function translate_file($filename, $application) {
	$languages = Language::get_all();

	foreach ($languages as $language) {
		$config = Config::Get();
		if ($config->base_language == $language->name_short) {
			continue;
		}

		if (!file_exists(PO_PATH . '/' . $language->name_short . '/')) {
			mkdir(PO_PATH . '/' . $language->name_short . '/', 0755, true);
		}

		if (!file_exists(PO_PATH . '/' . $language->name_short . '/' . $application . '.po')) {
			touch(PO_PATH . '/' . $language->name_short . '/' . $application . '.po');
		}

		$content = file($filename);
		$content = substr($content[2], 3, strlen($content[2])-6);
		echo 'translating ' . $filename . ' to ' . $language->name_short . ' for ' . $application . ' containing ' . $content . "\n";

		$content = file_get_contents($filename);
		$content_orig = $content;
		$strings = array();

		while (strpos($content, 'Translation::translate_plural("') !== false) {
			$content = substr($content, strpos($content, 'Translation::translate_plural("') + strlen('Translation::translate_plural("'));
			$string = substr($content, 0, strpos($content, '", "'));
			$strings[] = stripslashes(str_replace('\t', "\t", $string));
			$content = substr($content, strpos($content, '", "') + strlen('", "'));
			$string = substr($content, 0, strpos($content, '", '));
			$strings[] = stripslashes(str_replace('\t', "\t", $string));
			$content = substr($content, strpos($content, '", '));
		}

		$content = $content_orig;

		while (strpos($content, 'Translation::translate("') !== false) {
			$content = substr($content, strpos($content, 'Translation::translate("') + strlen('Translation::translate("'));
			$string = substr($content, 0, strpos($content, '")'));
			$content = substr($content, strpos($content, '")'));
			$strings[] = stripslashes(str_replace('\t', "\t", $string));
		}

		$current_strings = Util::po_load(PO_PATH . '/' . $language->name_short . '/' . $application . '.po');
		$untranslated = array();

		foreach ($strings as $string) {
			$untranslated[$string] = '';
		}

		$strings = array_merge($current_strings, $untranslated);
		asort($strings);
		Util::po_save(PO_PATH . '/' . $language->name_short . '/' . $application . '.po', $strings);
	}
}
?>
