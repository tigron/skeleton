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
	 * Constructor
	 *
	 * @access public
	 * @param int $id
	 */
	public function __construct($id = null) {
		parent::__construct($id);
	}

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
	public function save() {
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
	 * Get virtual dimensions of the picture after resize
	 *
	 * @param string $size
	 * @access private
	 */
	public function get_output_dimensions($size){
		if ($size == 'original') {
			return array('width'=>$this->width, 'height'=>$this->height);
		}	

		$config = Config::Get();
		$picture_formats = $config->picture_formats;

		if (!isset($picture_formats[$size])) {
			throw new Exception('Unknown format, please update config file');
		}

		if (isset($picture_formats[$size]['height'])) {
			$output_height = $picture_formats[$size]['height'];
		} else {
			$output_height = -1;
		}

		if (isset($picture_formats[$size]['width'])) {
			$output_width = $picture_formats[$size]['width'];
		} else {
			$output_width = -1;
		}

		//Now find out what should be the output size
		$width = $this->width;
		$height = $this->height;

		if ($output_height == -1 OR $width/$height*$output_height > $output_width) {
			$output_height = -1;
		} elseif ($output_width == -1 OR $height/$width*$output_width > $output_height) {
			$output_width = -1;
		}

		if ($output_width == -1) {
			$output_width = round($width /$height * $output_height,0);
		}
		if ($output_height == -1) {
			$output_height = round($height / $width * $output_width,0);
		}

		return array('width'=>$output_width, 'height'=>$output_height);
	}

	/**
	 * Resize the picture
	 *
	 * @access private
	 * @param string $size
	 */
	private function resize($size) {
		$path = $this->get_path();
		$output_dimensions = $this->get_output_dimensions($size);

		$image = imagecreatefromjpeg($path);
		$destination = imagecreatetruecolor($output_dimensions['width'], $output_dimensions['height']);
		imagecopyresampled($destination, $image, 0, 0, 0, 0, $output_dimensions['width'], $output_dimensions['height'], $this->width, $this->height);

		if (!file_exists(TMP_PATH . '/picture/' . $size)) {
			mkdir(TMP_PATH . '/picture/' . $size, 0755, true);
		}

		imagejpeg($destination, TMP_PATH . '/picture/' . $size . '/' . $this->unique_name);
		imagedestroy($image);
		imagedestroy($destination);
	}

	/**
	 * Resize and crop the picture
	 *
	 * @access private
	 * @param string $size
	 */
	private function crop($size) {
		$path = $this->get_path();
		$image = imagecreatefromjpeg($path);

		$config = Config::Get();
		$picture_formats = $config->picture_formats;
		$picture_format = $picture_formats[$size];		

		$thumb_width = $picture_format['width'];
		$thumb_height = $picture_format['height'];
		
		$this->get_dimensions();
		$original_aspect = $this->width / $this->height;
		$thumb_aspect = $thumb_width / $thumb_height;

		if ($original_aspect >= $thumb_aspect) {
			$new_height = $thumb_height;
			$new_width = $this->width / ($this->height / $thumb_height);
		} else {
			$new_width = $thumb_width;
			$new_height = $this->height / ($this->width / $thumb_width);
		}

		$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
		imagecopyresampled(
							$thumb, 
							$image, 
							0 - ($new_width - $thumb_width) / 2, 
							0 - ($new_height - $thumb_height) / 2,
							0, 0,
							$new_width, $new_height,
							$this->width, $this->height);
		
		if (!file_exists(TMP_PATH . '/picture/' . $size . '_crop')) {
			mkdir(TMP_PATH . '/picture/' . $size . '_crop', 0755, true);
		}
		
		imagejpeg($thumb, TMP_PATH . '/picture/' . $size . '_crop/' . $this->unique_name);
		imagedestroy($image);
		imagedestroy($thumb);
	}

	/**
	 * Output the picture to the browser
	 *
	 * @access public
	 * @param string $size
	 */
	public function show($size = 'original', $crop = false) {
		$config = Config::Get();
		$picture_formats = $config->picture_formats;

		if ($size != 'original' AND !isset($picture_formats[$size])) {
			throw new Exception('Picture requested in unknown size');
		}

		if ($crop === true AND !file_exists(TMP_PATH . '/picture/' . $size . '_crop/' . $this->unique_name)) {
			$this->crop($size);
		} elseif ($crop === false AND !file_exists(TMP_PATH . '/picture/' . $size . '/' . $this->unique_name)) {
			$this->resize($size);
		}

		if ($size == 'original') {
			$filename = $this->get_path();
		} else {
			if ($crop === true) {
				$filename = TMP_PATH . '/picture/' . $size . '_crop/' . $this->unique_name;
			} else {
				$filename = TMP_PATH . '/picture/' . $size . '/' . $this->unique_name;
			}
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
//		header('Content-Disposition: attachment; filename="'.$this->name.'"');
		header('Content-Type: ' . $this->mimetype);
		readfile($filename);
		exit;
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