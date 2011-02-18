<?php
/**
 * Media detection and serving of media files
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Web_Media {
	/**
	 * Image extensions
	 *
	 * @var array $filetypes
	 * @access private
	 */
	private static $filetypes = array(
		'image' => array('gif', 'jpg', 'jpeg', 'png'),
		'css' => array('css'),
		'javascript' => array('js'),
	);

	/**
	 * Detect if the request is a request for media
	 *
	 * @param $request array
	 * @access public
	 */
	public static function detect($request) {
		if (count($request) == 0) {
			return;
		}

		// Find the filename and extension
		$filename = array_pop($request);
		$extension = substr($filename, strrpos($filename, '.'));

		// If the request does not contain an extension, it's not to be handled by media
		if (strpos($extension, '.') !== 0) {
			return;
		}

		// Remove the . from the extension
		$extension = substr($extension, 1);

		$path = implode('/', $request) . '/' . $filename;

		foreach (self::$filetypes as $filetype	=>	$extensions) {
			if (in_array($extension, $extensions)) {
				if (file_exists(MEDIA_PATH . '/' . $filetype . '/' . $path)) {
					self::output(MEDIA_PATH . '/' . $filetype . '/' . $path, $extension);
				} else if ((file_exists(MEDIA_PATH . '/tools/' . $path))) {
					self::output(MEDIA_PATH . '/tools/' . $path, $extension);
				} else {
					header("HTTP/1.1 404 Not Found", true);
					echo '404 File Not Found';
					exit();
				}
			}
		}

		if ((file_exists(MEDIA_PATH . '/tools/' . $path))) {
			self::output(MEDIA_PATH . '/tools/' . $path, $extension);
		}
	}

	/**
	 * Ouput the content of the file and cache it
	 *
	 * @param string $path
	 * @param string $extension
	 * @access private
	 */
	private static function output($path, $extension) {
		self::cache($path);
		header('Content-Type: ' . self::get_mime_type($extension));
		readfile($path);
		exit();
	}

	/**
	 * Get the mime type of a file
	 *
	 * @access private
	 * @param string $filename
	 * @return string $mime_type
	 */
	private static function get_mime_type($extension) {
		$mime_type = '';
		switch ($extension) {
			case 'htm'		:
			case 'html'		:	$mime_type = 'text/html';
								break;

			case 'css'		:	$mime_type = 'text/css';
								break;

			case 'js'		:	$mime_type = 'text/javascript';
								break;

			case 'png'		:	$mime_type = 'image/png';
								break;

			case 'gif'		:	$mime_type = 'image/gif';
								break;

			case 'jpg'		:
			case 'jpeg'		:	$mime_type = 'image/jpeg';
								break;

			default			:	$mime_type = 'text/plain';
		}

		return $mime_type;
	}

	/**
	 * Detect if the file should be resent to the client or if it can use its cache
	 *
	 * @param string filename requested
	 * @access private
	 */
	private static function cache($path = null, $modified = null) {
		if ($path == null) {
			$gmt_mtime = gmdate('D, d M Y H:i:s', strtotime($modified)).' GMT';
		} else {
			$gmt_mtime = gmdate('D, d M Y H:i:s', filemtime($path)).' GMT';
		}

		header('Cache-Control: public');
		header('Pragma: public');

		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime) {
				header('Expires: ');
				header('HTTP/1.1 304 Not Modified');
				exit;
			}
		}

		header('Last-Modified: '. $gmt_mtime);
		header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+30 minutes')).' GMT');
	}
}
?>
