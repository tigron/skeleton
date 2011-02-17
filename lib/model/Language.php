<?php
/**
 * Language class
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Language {
	/**
	 * @var int $id
	 * @access public
	 */
	public $id;

	/**
	 * @var array $details
	 * @access protected
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
	 * Get the details
	 *
	 * @access private
	 */
	private function get_details() {
		$db = Database::Get();
		$details = $db->getRow('SELECT * FROM language WHERE id=?', array($this->id));
		if ($details === null) {
			throw new Exception('language not found');
		}
		$this->details = $details;
	}

	/**
	 * Save the details
	 *
	 * @access public
	 */
	public function save() {
		$db = Database::Get();
		if (!isset($this->id)) {
			$mode = MDB2_AUTOQUERY_INSERT;
			$where = false;
		} else {
			$mode = MDB2_AUTOQUERY_UPDATE;
			$where = 'id=' . $db->quote($this->id);
		}

		$db->autoExecute('language', $this->details, $mode, $where);

		if ($mode == MDB2_AUTOQUERY_INSERT) {
			$this->id = $db->getOne('SELECT LAST_INSERT_ID();');
		}

		$this->get_details();
	}

	/**
	 * Set a key
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value) {
		$this->details[$key] = $value;
	}

	/**
	 * Get a key
	 *
	 * @access public
	 * @return mixed $value
	 */
	public function __get($key) {
		if (isset($this->details[$key])) {
			return $this->details[$key];
		} else {
			throw new Exception('Unknown key requested: '. $key);
		}
	}

	/**
	 * Get a Language by ID
	 *
	 * @access public
	 * @param int $id
	 * @return Language
	 */
	public static function get_by_id($id) {
		return new self($id);
	}

	/**
	 * Get all
	 *
	 * @access public
	 * @return array
	 */
	public static function get_all() {
		$db = Database::Get();
		$ids = $db->getCol('SELECT id FROM language');
		$languages = array();
		foreach ($ids as $id) {
			$languages[] = self::get_by_id($id);
		}
		return $languages;
	}

	/**
	 * Get by name_short
	 *
	 * @access public
	 * @return Language
	 * @param string $name_short
	 */
	public static function get_by_name_short($name) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM language WHERE name_short=?', array($name));

		if ($id === null) {
			throw new Exception('No such language');
		}

		return self::get_by_id($id);
	}
}
?>
