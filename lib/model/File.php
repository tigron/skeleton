<?php
/**
 * File class
 *
 * @package %%Package%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once LIB_PATH . '/base/File/Store.php';

class File {
	/**
	 * @var Int $id
	 * @access public
	 */
	public $id;

	/**
	 * Details
	 *
	 * @var array $details
	 * @access private
	 */
	protected $details = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int $id
	 */
	public function __construct($id = null) {
		if ($id !== null) {
			$this->id = $id;
			$this->get_details();
		}
	}

	/**
	 * Get the details of this file
	 *
	 * @access private
	 */
	protected function get_details() {
		if (!isset($this->id) OR $this->id === null) {
			throw new Exception('Could not fetch file data: ID not set');
		}

		$db = Database::Get();
		$details = $db->getRow('SELECT * FROM file WHERE id=?', array($this->id));
		if ($details === null) {
			throw new Exception('Could not fetch file data: no file found with id ' . $this->id);
		}

		$this->details = $details;
	}

	/**
	 * Save the file
	 *
	 * @access public
	 */
	public function save($get_details = true) {
		$db = Database::Get();

		if (!isset($this->id) OR $this->id === null) {
			$mode = MDB2_AUTOQUERY_INSERT;
			$this->details['created'] = date('Y-m-d H:i:s');
			$where = false;
		} else {
			$mode = MDB2_AUTOQUERY_UPDATE;
			$where = 'id=' . $db->quote($this->id);
		}

		$db->autoExecute('file', $this->details, $mode, $where);
		
		if ($mode === MDB2_AUTOQUERY_INSERT) {
			$this->id = $db->getOne('SELECT LAST_INSERT_ID();');
		}

		if ($get_details) {
			$this->get_details();
		}
	}

	/**
	 * Set a detail
	 *
	 * @access public
	 * @param string $key
	 * @param mixex $value
	 */
	public function __set($key, $value) {
		$this->details[$key] = $value;
	}

	/**
	 * Get a detail
	 *
	 * @access public
	 * @param string $key
	 * @return mixed $value
	 */
	public function __get($key) {
		if (!isset($this->details[$key])) {
			throw new Exception('Unknown key requested: ' . $key);
		} else {
			return $this->details[$key];
		}
	}

	/**
	 * Isset
	 *
	 * @access public
	 * @param string $key
	 * @return bool $isset
	 */
	public function __isset($key) {
		if (isset($this->details[$key])) {
			return true;
		} else {
			return false;
		}
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
	 * Load array
	 *
	 * @access public
	 * @param array $details
	 */
	public function load_array($details) {
		foreach ($details as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * Get path
	 *
	 * @access public
	 * @return string $path
	 */
	public function get_path() {
		$created = strtotime($this->created);
		$path = STORE_PATH . '/file/' . date('Y', $created) . '/' . date('m', $created) . '/' . date('d', $created) . '/' . $this->unique_name;
		return $path;
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$db = Database::Get();
		$db->query('DELETE FROM file WHERE id=?', array($this->id));
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

	/**
	 * Get content of the file
	 *
	 * @access public
	 */
	public function get_contents() {
		return file_get_contents($this->get_path());
	}

	/**
	 * Get a File by ID
	 *
	 * @access public
	 * @parm int $id
	 * @return file
	 */
	public static function get_by_id($id) {
		$file = new File($id);
		if ($file->is_picture()) {
			return Picture::get_by_id($id);
		}

		return $file;
	}

	/**
	 * Get all
	 *
	 * @access public
	 * @return array files
	 */
	public static function get_all() {
		$db = Database::Get();
		$ids = $db->getCol('SELECT id FROM file', array());
		$files = array();
		foreach ($ids as $id) {
			$files[] = File::get_by_id($id);
		}
		return $files;
	}

	/**
	 * Get by unique_name
	 *
	 * @access public
	 * @param string $unique_name
	 * @return File $file
	 */
	public static function get_by_unique_name($name) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM file WHERE unique_name=?', array($name));
		if ($id === null) {
			throw new Exception('File not found');
		}
		
		$file = File::get_by_id($id);
		if ($file->is_picture()) {
			return Picture::get_by_id($file->id);
		} else {
			return $file;
		}
	}
}
?>