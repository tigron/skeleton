<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Util {

	/**
	 * Random code generator
	 *
	 * @access public
	 * @param string $length
	 * @param string $chars
	 * @return string $code
	 */
	public static function create_random_code($length, $chars = '23456789ABCDEFGHKMNPQRSTWXYZ') {
		$code = '';

		for ($i = 1; $i <= $length; $i++) {
			$num = mt_rand(1, strlen($chars));
	        $tmp = substr($chars, $num, 1);
	        $code = $code . $tmp;
		}

		return $code;
	}

	/**
	 * Get table fields
	 *
	 * @access public
	 * @param string $table
	 * @return array $fields
	 */
	public static function get_table_fields($table) {
		$db = Database::Get();
		$fields = $db->listTableFields(strtolower($table));
		return $fields;
	}

	/**
	 * Filter fields to insert/update table
	 *
	 * @access public
	 * @param string $table
	 * @param array $data
	 * @return $filtered_data
	 */
	public static function filter_table_data($table, $data) {
		$table_fields = Util::get_table_fields($table);
		$result = array();
		foreach ($table_fields as $field) {
			if (isset($data[$field])) {
				$result[$field] = $data[$field];
			}
		}

		return $result;
	}

	/**
	 * Fetches the mime type for a certain file
	 *
	 * @param string $file The path to the file
	 * @return string $mime_type
	 */
    public static function mime_type($file)  {
		$handle = finfo_open(FILEINFO_MIME);
		$mime_type = finfo_file($handle,$file);
		return $mime_type;
    }

	/**
	 * Load PO File
	 *
	 * @param string $filename
	 * @return array $strings
	 */
	public static function load_po($filename) {
		$strings = array();
		if (!file_exists($filename)) {
			return array();
		}
		$content = file_get_contents($filename);

		$matched = preg_match_all('/(msgid\s+("([^"]|\\\\")*?"\s*)+)\s+(msgstr\s+("([^"]|\\\\")*?"\s*)+)/',	$content, $matches);

		if (!$matched) {
			return array();
		}
		// get all msgids and msgtrs
		for ($i = 0; $i < $matched; $i++) {
			$msgid = preg_replace('/\s*msgid\s*"(.*)"\s*/s', '\\1', $matches[1][$i]);
			$msgstr= preg_replace('/\s*msgstr\s*"(.*)"\s*/s', '\\1', $matches[4][$i]);
			$strings[Util::prepare_po_load_string($msgid)] = Util::prepare_po_load_string($msgstr);
		}

		return $strings;
	}

	/**
	 * Prepare a string for loading
	 *
	 * @access private
	 * @param string $string
	 * @return string $fixed_string
	 */
	private static function prepare_po_load_string($string) {
		$smap = array('/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\\"/');
		$rmap = array('', "\n", "\r", "\t", '"');
		return (string) preg_replace($smap, $rmap, $string);
	}

	/**
	 * Prepare a string to be written in a po file
	 *
	 * @access private
	 * @param string $string
	 * @return string $fixed_string
	 */
	private static function prepare_po_save_string($string) {
		$smap = array('"', "\n", "\t", "\r");
		$rmap = array('\\"', '\\n"' . "\n" . '"', '\\t', '\\r');
		return (string) str_replace($smap, $rmap, $string);
	}

	/**
	 * Save a po file, based on a translation array
	 *
	 * @access public
	 * @param string $filename
	 * @param array $strings
	 */
	public static function save_po($filename, $strings) {
		$output = '';
		foreach ($strings as $key => $value) {
			$output .= 'msgid "' . Util::prepare_po_save_string($key) . '"' . "\n";
			$output .= 'msgstr "' . Util::prepare_po_save_string($value) . '"' . "\n\n";
		}

		file_put_contents($filename, $output);
	}

	public static function po_merge($base, $extra) {
		$base_strings = self::load_po($base);
		$extra_strings = self::load_po($extra);

		foreach ($extra_strings as $string => $translation) {
			if (isset($base_strings[$string]) AND $base_strings[$string] != '') {
				$extra_strings[$string] = $base_strings[$string];
			}
		}

		Util::save_po($base, $extra_strings);
	}
}
?>
