<?php
/**
 * Picture class
 *
 * @package %%Package%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once LIB_PATH . '/model/File.php';
require_once LIB_PATH . '/model/Picture/Manipulation.php';

class Picture extends File {

	/**
	 * Details
	 *
	 * @var array $details
	 * @access private
	 */
	private $local_details = array();

	/**
	 * Local fields
	 *
	 * @access private
	 * @var array $fields
	 */
	private $local_fields = array('file_id', 'width', 'height');

	/**
	 * Get the details of this file
	 *
	 * @access private
	 */
	protected function get_details() {
		parent::get_details();
		if (!isset($this->id) OR $this->id === null) {
			throw new Exception('Could not fetch file data: ID not set');
		}

		$db = Database::Get();
		$details = $db->getRow('SELECT * FROM picture WHERE file_id=?', array($this->id));

		if ($details === null) {
			$this->save();
		}

		$this->local_details = $details;
	}

	/**
	 * Save the file
	 *
	 * @access public
	 */
	public function save($get_details = true) {
		parent::save(false);
		
		$db = Database::Get();
		if (!isset($this->local_details['id']) OR $this->local_details['id'] === null) {
			$mode = MDB2_AUTOQUERY_INSERT;
			$where = false;
			if (!isset($this->local_details['file_id']) OR $this->local_details['file_id'] == 0) {
				$this->file_id = $this->id;
			} else {
				$this->id = $this->file_id;
			}
		} else {
			$mode = MDB2_AUTOQUERY_UPDATE;
			$where = 'file_id=' . $db->quote($this->id);
		}

		$db->autoExecute('picture', $this->local_details, $mode, $where);
		$this->get_details();

		if ($mode === MDB2_AUTOQUERY_INSERT) {
			$this->get_dimensions();
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
		if (in_array($key, $this->local_fields)) {
			$this->local_details[$key] = $value;		
		} else {
			parent::__set($key, $value);
		}
	}

	/**
	 * Get a detail
	 *
	 * @access public
	 * @param string $key
	 * @return mixed $value
	 */
	public function __get($key) {
		if (isset($this->local_details[$key])) {
			return $this->local_details[$key];
		} else {
			return parent::__get($key);
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
		if (isset($this->local_details[$key])) {
			return true;
		} else {
			return parent::__isset($key);
		}
	}
	
	/**
	 * Get the dimensions of the picture
	 *
	 * @access private
	 */
	private function get_dimensions() {
		$path = $this->get_path();	
		list($width, $height) = getimagesize($path);
		$this->width = $width;
		$this->height = $height;
		$this->save();
	}
	
	/**
	 * Resize the picture
	 *
	 * @access private
	 * @param string $size
	 */
	private function resize($size) {		
		if (!file_exists(TMP_PATH . '/picture/' . $size)) {
			mkdir(TMP_PATH . '/picture/' . $size, 0755, true);
		}
			
		if ($size == 'original') {
			$resize_info = array('width' => $this->width, 'height' => $this->height, 'mode' => 'exact');
		} else {
			$config = Config::Get();
			$resize_info = $config->picture_formats[$size];
		}

		$image = new Picture_Manipulation($this);
		$image->resize($resize_info['width'], $resize_info['height'], $resize_info['mode']);
		$image->output(TMP_PATH . '/picture/' . $size . '/' . $this->unique_name);		
	}

	/**
	 * Output the picture to the browser
	 *
	 * @access public
	 * @param string $size
	 */
	public function show($size = 'original') {
		$config = Config::Get();
		$picture_formats = $config->picture_formats;

		if ($size != 'original' AND !isset($picture_formats[$size])) {
			throw new Exception('Picture requested in unknown size');
		}

		if(!file_exists(TMP_PATH . '/picture/' . $size . '/' . $this->unique_name)) {
			$this->resize($size);
		}

		if ($size == 'original') {
			$filename = $this->get_path();
		} else {
			$filename = TMP_PATH . '/picture/' . $size . '/' . $this->unique_name;			
		}

		$gmt_mtime = gmdate('D, d M Y H:i:s', filemtime($filename)).' GMT';

		header('Cache-Control: public');
		header('Pragma: public');

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime) {
				header('Expires: ');
				header('HTTP/1.1 304 Not Modified');
				exit;
			}
		}

		header('Last-Modified: '. $gmt_mtime);
		header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+300 minutes')).' GMT');
		header('Content-Type: ' . $this->mime_type);
		readfile($filename);
		exit();
	}

	/**
	 * Delete the image and its cache
	 *
	 * @access public
	 */
	public function delete() {
		$config = Config::Get();
		$formats = $config->picture_formats;

		foreach ($formats as $format =>	$properties) {
			if (file_exists(TMP_PATH . '/picture/' . $format . '/' . $this->unique_name)) {
				unlink(TMP_PATH . '/picture/' . $format . '/' . $this->unique_name);
			}
		}
		$db = Database::Get();
		$db->query('DELETE FROM picture WHERE file_id=?', array($this->id));

		parent::delete();
	}

	/**
	 * Get a picture by ID
	 *
	 * @access public
	 * @param int $id
	 * @return Picture $picture
	 */
	public static function get_by_id($id) {
		return new Picture($id);
	}
}
?>
