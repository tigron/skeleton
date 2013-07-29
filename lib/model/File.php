<?php
/**
 * File class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/base/File/Store.php';

class File {
	use Model, Save, Get;

	/**
	 * Delete a file
	 *
	 * @access public
	 */
	public function delete() {
		File_Store::delete_file($this);
		$db = Database::Get();
		$db->query('DELETE FROM file WHERE id=?', array($this->id));
	}

	/**
	 * Is this a picture
	 *
	 * @access public
	 * @return bool $is_picture
	 */
	public function is_picture() {
		$mime_types = array(
			'image/jpeg',
			'image/jpg',
			'image/png',
			'image/gif',
			'image/tiff',
			'image/svg+xml',
		);

		if (in_array($this->mime_type, $mime_types)) {
			return true;
		}

		return false;
	}

	/**
	 * Get path
	 *
	 * @access public
	 * @return string $path
	 */
	public function get_path() {
		return File_Store::get_path($this);
	}

	/**
	 * Send this file as a download to the client
	 *
	 * @access public
	 */
	public function client_download() {
		header('Content-type: ' . $this->details['mime_type']);
		header('Content-Disposition: attachment; filename="'.$this->details['name'].'"');
		readfile($this->get_path());
		exit();
	}

	/**
	 * Send this file inline to the client
	 *
	 * @access public
	 */
	public function client_inline() {
		header('Content-type: ' . $this->details['mime_type']);
		header('Content-Disposition: inline; filename="'.$this->details['name'].'"');
		readfile($this->get_path());
		exit();
	}
}
