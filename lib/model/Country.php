<?php
/**
 * Country class
 *
 * @package %%Package%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Country {
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
	private $details = array();

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
	 * Get the details of this country
	 *
	 * @access private
	 */
	private function get_details() {
		if (!isset($this->id) OR $this->id === null) {
			throw new Exception('Could not fetch country data: ID not set');
		}

		$db = Database::Get();
		$details = $db->getRow('SELECT * FROM country WHERE id=?', array($this->id));
		if ($details === null) {
			throw new Exception('Could not fetch country data: no country found with id ' . $this->id);
		}

		$this->details = $details;
	}

	/**
	 * Save the country
	 *
	 * @access public
	 */
	public function save() {
		$db = Database::Get();
		if (!isset($this->id) OR $this->id === null) {
			$mode = MDB2_AUTOQUERY_INSERT;
			$this->details['created'] = date('Y-m-d H:i:s');
			$where = false;
		} else {
			$mode = MDB2_AUTOQUERY_UPDATE;
			$where = 'id=' . $db->quote($this->id);
		}

		$db->autoExecute('country', $this->details, $mode, $where);

		if ($mode === MDB2_AUTOQUERY_INSERT) {
			$this->id = $db->getOne('SELECT LAST_INSERT_ID();');
		}

		$this->get_details();
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
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$db = Database::Get();
		$db->query('DELETE FROM country WHERE id=?', array($this->id));
	}

	/**
	 * Get a Country by ID
	 *
	 * @access public
	 * @parm int $id
	 * @return country
	 */
	public static function get_by_id($id) {
		return new Country($id);
	}

	/**
	 * Get all
	 *
	 * @access public
	 * @return array countrys
	 */
	public static function get_all() {
		$db = Database::Get();
		$ids = $db->getCol('SELECT id FROM country', array());
		$countrys = array();
		foreach ($ids as $id) {
			$countrys[] = Country::get_by_id($id);
		}
		return $countrys;
	}

	/**
	 * Get by ISO2
	 *
	 * @access public
	 * @param string $iso2
	 * @return Country $country
	 */
	public static function get_by_iso2($iso2) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM country WHERE ISO2=?', array($iso2));

		if ($id == null) {
			throw new Exception('No such country');
		} else {
			return Country::get_by_id($id);
		}
	}

	/**
	 * Get grouped
	 *
	 * @access public
	 * @return array $countries
	 */
	public static function get_grouped() {
		$db = Database::Get();
		$db_countries = $db->getAll('SELECT * FROM country WHERE european=1 ORDER BY name ASC', array());

		$countries = array(	'european' => array(), 'rest' => array() );
		foreach ($db_countries as $db_country) {
			$country = new Country();
			$country->id = $db_country['id'];
			$country->details = $db_country;
			$countries['european'][] = $country;
		}

		$db_countries = $db->getAll('SELECT * FROM country WHERE european=0 ORDER BY name ASC', array());
		foreach ($db_countries as $db_country) {
			$country = new Country();
			$country->id = $db_country['id'];
			$country->details = $db_country;
			$countries['rest'][] = $country;
		}
		return $countries;
	}
}
?>
