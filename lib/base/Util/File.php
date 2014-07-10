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

class Util_File {

	/**
	 * Sanitize filenames
	 *
	 * @access public
	 * @param string $name
	 * @return string $name
	 */
	public static function sanitize_name($name) {
		$special_chars = array ('#','$','%','^','&','*','!','~','‘','"','’','\'','=','?','/','[',']','(',')','|','<','>',';','\\',',','+');
		$name = preg_replace('/^[.]*/','',$name); // remove leading dots
		$name = preg_replace('/[.]*$/','',$name); // remove trailing dots
		$name = str_replace($special_chars, '', $name);// remove special characters
		$name = str_replace(' ','_',$name); // replace spaces with _

		$name_array = explode('.', $name);

		if (count($name_array) > 1) {
			$extension = array_pop($name_array);
		} else {
			$extension = null;
		}

		$name = implode('.', $name_array);
		$name = substr($name, 0, 50);

		if ($extension != null) {
			$name = $name . '.' . $extension;
		}

		return $name;
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

		if (strpos($mime_type, ';')) {
			$mime_type = preg_replace('/;.*/', ' ', $mime_type);
		}

		return trim($mime_type);
	}
}
?>
