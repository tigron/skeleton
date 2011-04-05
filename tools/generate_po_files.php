<?php
/**
 * This script will generate po files based on all strings
 * that needs to be translated from the templates
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once dirname(__FILE__) . '/../config/global.php';

require_once EXT_PATH . '/twig/lib/Twig/Autoloader.php';
require_once LIB_PATH . '/model/Language.php';
require_once LIB_PATH . '/base/Util.php';
require_once LIB_PATH . '/base/Translation.php';

$config = Config::Get();
Twig_Autoloader::register();

$applications_array = $config->applications;
$applications = array();
foreach ($applications_array as $application) {
	$applications[$application] = true;
}

$applications = array_keys($applications);

/**
 * First: generate the full template cache
 */
foreach ($applications as $application) {
	$directories = array(
			ROOT_PATH . '/app/' . $application . '/template',
			ROOT_PATH . '/app/' . $application . '/macro',
	);

	$loader = new Twig_Loader_Filesystem($directories);

	// force auto-reload to always have the latest version of the template
	$twig = new Twig_Environment(
		$loader, array(
			'cache' => TMP_PATH . '/twig/' . $application,
			'auto_reload' => true
		)
	);

	$twig->addExtension(new Twig_Extensions_Extension_Tigron());
	$twig->addExtension(
		new Twig_Extensions_Extension_I18n(
			array(
				'function_translation' => 'Translation::translate',
				'function_translation_plural' => 'Translation::translate_plural',
			)
		)
	);

	$twig->addGlobal('base', $twig->loadTemplate('base.macro'));

	// iterate over all the templates
	foreach ($directories as $directory) {
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
			$file = str_replace($directory.'/', '', $file);

			if (strrpos($file, '.') ==  (strlen($file)-1)) {
				continue;
			}

			$template = $twig->loadTemplate($file);
		}
	}
}

/**
 * Second: make a copy of every po file
 */
foreach ($applications as $application) {
	$languages = Language::get_all();

	foreach ($languages as $language) {
		$config = Config::Get();
		if ($config->base_language == $language->name_short) {
			continue;
		}
		if (file_exists(PO_PATH . '/' . $language->name_short . '/' . $application . '.po')) {
			rename(PO_PATH . '/' . $language->name_short . '/' . $application . '.po', PO_PATH . '/' . $language->name_short . '/' . $application . '_def.po');
		}
	}
}

/**
 * Third: translate every application
 */
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

		if (!file_exists(PO_PATH . '/' . $language->name_short . '/' . $application . '_def.po')) {
			continue;
		}

		Util::po_merge(PO_PATH . '/' . $language->name_short . '/' . $application . '_def.po', PO_PATH . '/' . $language->name_short . '/' . $application . '.po');
		rename(PO_PATH . '/' . $language->name_short . '/' . $application . '_def.po', PO_PATH . '/' . $language->name_short . '/' . $application . '.po');
	}
}

/**
 * Function
 *
 * Translate application: take all files from the cache and translate
 * them.
 */
function translate_application($application) {
	$files = scandir(TMP_PATH . '/twig/' . $application);

	foreach ($files as $file) {
		if ($file[0] == '.') {
			continue;
		}

		if (is_dir(TMP_PATH . '/twig/' . $application . '/' . $file)) {
			translate_directory(TMP_PATH . '/twig/' . $application . '/' . $file, $application);
			continue;
		}
	}
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

		echo 'translating ' . $filename . "\n";

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
