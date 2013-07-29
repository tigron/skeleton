<?php
/**
 * File_Store Class
 *
 * Stores and retrieves files
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 */

require_once LIB_PATH . '/model/File.php';
require_once LIB_PATH . '/model/Picture.php';

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
	public static function store($name, $content) {
		// create a file object
		$file = new File();
		$file->name = $name;
		$file->md5sum = hash('md5', $content);
		$file->save();

		// create directory if not exist
		$path = self::get_path($file);
		$pathinfo = pathinfo($path);
		if (!is_dir($pathinfo['dirname'])) {
			mkdir($pathinfo['dirname'], 0755, true);
		}

		// store file on disk
		file_put_contents($path, $content);

		// get file extension
		$finfo = finfo_open(FILEINFO_MIME);
		$mime_type = finfo_file($finfo, $path);

		$file->mime_type = $mime_type;
		$file->size = filesize($path);
		$file->save();

		if ($file->is_picture()) {
			$picture = new Picture();
			$picture->id = $file->id;
			$picture->save();
			return $picture;
		}

		return $file;
	}

	/**
	 * Upload a file
	 *
	 * @access public
	 * @param array $_FILES['file']
	 * @return File $file
	 */
	public static function upload($fileinfo) {
		// create a file object
		$file = new File();
		$file->name = $fileinfo['name'];
		$file->md5sum = hash('md5', file_get_contents($fileinfo['tmp_name']));
		$file->save();

		// create directory if not exist
		$path = self::get_path($file);
		$pathinfo = pathinfo($path);
		if (!is_dir($pathinfo['dirname'])) {
			mkdir($pathinfo['dirname'], 0755, true);
		}

		// store file on disk
		if (!move_uploaded_file($fileinfo['tmp_name'], $path)) {
			throw new Exception('upload failed');
		}

		// get file extension
		$finfo = finfo_open(FILEINFO_MIME);
		$mime_type = finfo_file($finfo, $path);

		$file->mime_type = $mime_type;
		$file->size = filesize($path);
		$file->save();

		if ($file->is_picture()) {
			$picture = new Picture();
			$picture->id = $file->id;
			$picture->save();
			return $picture;
		}

		return $file;
	}

	/**
	 * Delete a file
	 *
	 * @access public
	 * @param File $file
	 */
	public static function delete_file(File $file) {
		unlink(self::get_path($file));
	}

	/**
	 * Get the contents of a file by File
	 *
	 * @param File $file
	 * @access public
	 * @return mixed the content of the file
	 */
	public static function get_content_by_file(File $file) {
		return file_get_contents(self::get_path($file));
	}

	/**
	 * Get the contents of a file by File
	 *
	 * @param int $id
	 * @access public
	 * @return mixed the content of the file
	 */
	public static function get_content_by_file_id($id) {
		$file = File::get_by_id($id);
		return file_get_contents(self::get_path($file));
	}

	/**
	 * Get the physical path of a file
	 *
	 * @param File $file
	 * @return string $path
	 */
	public static function get_path(File $file) {
		$subpath = substr(base_convert($file->md5sum, 16, 10), 0, 3);
		$subpath = implode('/', str_split($subpath)) . '/';

		$path = STORE_PATH . '/file/' . $subpath . $file->id . '-' . Util::sanitize_filename($file->name);

		return $path;
	}
}
