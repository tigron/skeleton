<?php
/**
 * File_Store Class
 *
 * Stores and retrieves files
 *
 * @package %%PACKAGE%%
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @version $Id$
 */

require_once LIB_PATH . '/model/File.php';

class File_Store {

	/**
	 * Private constructor
	 *
	 * @access private
	 */
	private function __construct() {}

	/**
	 * Store a file
	 *
	 * @param string $name
	 * @param string $mimetype
	 * @param mixed $content
	 * @access public
	 */
	public static function store($name, $content, $created = null) {
		$file = new File();
		$file->name = $name;
		$file->save();

		if (is_null($created)) {
			$created = time();
		} else {
			$created = strtotime($created);
		}

		$file->created = date('Y-m-d H:i:s', $created);
		$file->save();

		$dir = STORE_PATH . '/file/' . date('Y', $created) . '/' . date('m', $created) . '/' . date('d', $created);

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$unique_name = $dir . '/' . str_replace('.', '', microtime(true)) . '-' . Util::file_sanitize_name($file->name);

		file_put_contents($unique_name, $content);
		$size = filesize($unique_name);
		$file->mimetype = Util::file_mime_type($unique_name);
		$file->unique_name = basename($unique_name);
		$file->size = filesize($unique_name);
		$file->save();

		return File::get_by_id($file->id);
	}

	/**
	 * Upload a file
	 *
	 * @access public
	 * @param array $_FILES['file']
	 * @return File $file
	 */
	public static function upload($fileinfo) {
		$file = new File();
		$file->name = $fileinfo['name'];
		$file->save();

		$created = strtotime($file->created);
		$dir = STORE_PATH . '/file/' . date('Y', $created) . '/' . date('m', $created) . '/' . date('d', $created);

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$unique_name = $dir . '/' . str_replace('.', '', microtime(true)) . '-' . Util::file_sanitize_name($file->name);

		if (!move_uploaded_file($fileinfo['tmp_name'], $unique_name)) {
			throw new Exception('upload failed');
		}
		$file->unique_name = basename($unique_name);
		$file->size = filesize($unique_name);
		$file->mimetype = Util::file_mime_type($unique_name);
		$file->save();

		return File::get_by_id($file->id);
	}

	/**
	 * Upload multiple
	 *
	 * @access public
	 * @param array $_FILES['file']
	 * @return array $files
	 */
	public static function upload_multiple($fileinfo) {
		$files = array();
		foreach ($fileinfo['name'] as $key => $value) {
			$item_fileinfo = array();
			foreach ($fileinfo as $property => $value) {
				$item_fileinfo[$property] = $value[$key];
			}
			if ($item_fileinfo['size'] > 0) {
				$files[] = self::upload($item_fileinfo);
			}
		}
		return $files;
	}

	/**
	 * Delete a file
	 *
	 * @access public
	 * @param File $file
	 */
	public static function delete_file(File $file) {
		if (file_exists($file->get_path())) {
			unlink($file->get_path());
		}
	}
}
