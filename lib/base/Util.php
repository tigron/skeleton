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

	/**
	 * Merge 2 po files
	 *
	 * @access public
	 * @param array $strings1
	 * @param array $strings2
	 */
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

	/**
	 * Reverse rewrite
	 *
	 * @access public
	 * @param string $html
	 * @return string $html_with_reverse_rewrite
	 */
	public static function reverse_rewrite_html($html) {
		$html = preg_replace_callback('@\<([^>]*) (href|src|action)="/([^"]*)?"@iU', 'Util::reverse_rewrite_html_callback', $html);
		return $html;
	}

	/**
	 * Reverse rewrite callback for regexp
	 *
	 * @access private
	 * @param array $data
	 * @return string $string
	 */
	private static function reverse_rewrite_html_callback($data) {
		try {
			$new_link = self::reverse_rewrite_link($data[3]);
		} catch (Exception $e) {
			return $data[0];
		}
		return str_replace($data[3], $new_link, $data[0]);
	}

	/**
	 * Do a reverse rewrite of a link
	 *
	 * @access public
	 * @param string $url
	 * @return string $reverse_rewrite
	 */
	public static function reverse_rewrite_link($url) {
		$config = Config::Get();
		$language = Language::Get();
		$url = parse_url($url);

		$params = array();
		if (isset($url['query'])) {
			parse_str($url['query'], $params);
		}

		if (isset($config->routes[APP_NAME])) {
			$routes = $config->routes[APP_NAME];
		} else {
			throw new Exception('No routes found for current application');
		}

		$correct_route = null;
		if (isset($routes[$url['path']]['routes'][$language->name_short])) {
			$correct_route = $routes[$url['path']];
		} elseif (isset($routes[$url['path']]['routes']['default'])) {
			$correct_route = $routes[$url['path']];
		} else {
			throw new Exception('No available route found');
		}

		// We have a possible correct route
		$variables = $correct_route['variables'];
		$correct_variable_string = null;

		if (count($params) == 0 AND in_array('', $correct_route['variables'])) {
			$correct_variable_string = '';
		} else {
			foreach ($variables as $variable_string) {
				if (substr_count($variable_string, '$') == count($params)) {
					$correct_variable_string = $variable_string;
					break;
				}
			}
		}

		if ($correct_variable_string === null) {
			throw new Exception('Route found but variables incorrect');
		}

		// See if all variables match
		$correct_variables = explode('/', $correct_variable_string);
		$variables_matches = true;

		foreach ($correct_variables as $key => $correct_variable) {
			$correct_variable = str_replace('$', '', $correct_variable);
			if (!isset($params[$correct_variable]) AND $correct_variable != '') {
				$variables_matches = false;
				break;
			}
			$correct_variables[$key] = $correct_variable;
		}

		if (!$variables_matches) {
			throw new Exception('Route found but variables incorrect');
		}

		// Now build the new querystring
		if (isset($correct_route['routes'][$language->name_short])) {
			$querystring = $correct_route['routes'][$language->name_short];
		} else {
			$querystring = $correct_route['routes']['default'];
		}

		foreach ($correct_variables as $correct_variable) {
			if ($correct_variable != '') {
				$querystring .= '/' . $params[$correct_variable];
			}
		}
		return $language->name_short . $querystring;
	}
}
?>
