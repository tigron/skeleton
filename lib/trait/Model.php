<?php
/**
 * trait: Model
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

trait Model {
	/**
	 * @var int $id
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
	 * Dirty fields
	 * Unsaved fields
	 *
	 * @var array $dirty_fields
	 * @access private
	 */
	private $dirty_fields = array();

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
	 * Get the details of this object
	 *
	 * @access private
	 */
	protected function get_details() {
		$table = self::trait_get_database_table();

		if (!isset($this->id) OR $this->id === null) {
			throw new Exception('Could not fetch ' . $table . ' data: id not set');
		}

		$db = self::trait_get_database();

		$details = $db->getRow('SELECT * FROM ' . $db->quoteIdentifier($table) . ' WHERE id=?', array($this->id));

		if ($details === null) {
			throw new Exception('Could not fetch ' . $table . ' data: none found with id ' . $this->id);
		}

		$this->details = $details;
		$this->dirty_fields = array();
	}

	/**
	 * Set a detail
	 *
	 * @access public
	 * @param string $key
	 * @param mixex $value
	 */
	public function __set($key, $value) {
		// Check if the key we want to set exists in the disallow_set variable
		if (property_exists(get_class(), 'class_configuration') AND isset(self::$class_configuration['disallow_set'])) {
			if (is_array(self::$class_configuration['disallow_set'])) {
				if (in_array($key, self::$class_configuration['disallow_set'])) {
					throw new Exception('Can not set ' . $key . ' directly');
				}
			} else {
				throw new Exception('Improper use of disallow_set');
			}
		}

		if (is_callable(array($this, 'set_' . $key))) {
			$method = 'set_' . $key;
			$this->$method($value);
			return;
		}

		if (is_object($value) AND property_exists($value, 'id')) {
			$key = $key . '_id';
			$this->$key = $value->id;
			return;
		}

		if (isset(self::$object_text_fields)) {
			if (strpos($key, 'text_') === 0) {
				if ($this->id === null) {
					$this->save();
				}
				$key = str_replace('text_', '', $key);
				list($language, $label) = explode('_', $key, 2);

				if (!in_array($label, self::$object_text_fields)) {
					throw new Exception('Incorrect text field');
				}

				$language = Language::get_by_name_short($language);
				$object_text = Object_Text::get_by_object_label_language($this, $label, $language);
				$object_text->content = $value;
				$object_text->save();
				return;
			}
		}

		if (!isset($this->details[$key])) {
			$this->dirty_fields[$key] = '';
		}

		if (isset($this->details[$key]) AND $this->details[$key] != $value) {
			// A new value is set, let's tag it as dirty

			if (!isset($this->dirty_fields[$key])) {
				$this->dirty_fields[$key] = $this->details[$key];
			}
		}

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
		if (isset($this->details[strtolower($key) . '_id']) AND class_exists($key)) {
			return $key::get_by_id($this->details[strtolower($key) . '_id']);
		}
		
		if (isset($this->details[$key])) {
			return $this->details[$key];
		}

		if (isset(self::$object_text_fields)) {
			if (strpos($key, 'text_') === 0) {
				$key = str_replace('text_', '', $key);
				list($language, $label) = explode('_', $key, 2);

				if (!in_array($label, self::$object_text_fields)) {
					throw new Exception('Incorrect text field');
				}

				$language = Language::get_by_name_short($language);
				if ($this->id === null) {
					return '';
				} else {
					return Object_Text::get_by_object_label_language($this, $label, $language)->content;
				}
			}
		}

		throw new Exception('Unknown key requested: ' . $key);
	}

	/**
	 * Isset
	 *
	 * @access public
	 * @param string $key
	 * @return bool $isset
	 */
	public function __isset($key) {
		if (isset($this->details[strtolower($key) . '_id']) AND class_exists($key)) {
			return true;
		}

		if (isset($this->details[$key])) {
			return true;
		}

		if (isset(self::$object_text_fields)) {
			if (strpos($key, 'text_') === 0) {
				$key = str_replace('text_', '', $key);
				list($language, $label) = explode('_', $key);

				if (!in_array($label, self::$object_text_fields)) {
					return false;
				} else {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Is Dirty
	 *
	 * @access public
	 * @return bool $dirty
	 */
	public function is_dirty() {
		$dirty_fields = $this->get_dirty_fields();
		if (count($dirty_fields) == 0) {
			return false;
		}

		return true;
	}

	/**
	 * Get dirty fields
	 *
	 * @access public
	 * @return array $dirty_fields
	 */
	public function get_dirty_fields() {
		return $this->dirty_fields;
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
	 * trait_get_database_config_name: finds out which database name we need to get
	 *
	 * @access public
	 * @return Database $database
	 */
	private static function trait_get_database() {
		if (property_exists(get_class(), 'class_configuration') AND isset(self::$class_configuration['database_config_name'])) {
			$db = Database::get(self::$class_configuration['database_config_name']);
		} else {
			$db = Database::get();
		}
		return $db;
	}

	/**
	 * trait_get_database_table: finds out which table we need to use
	 *
	 * @access public
	 * @return string $table
	 */
	private static function trait_get_database_table() {
		if (property_exists(get_class(), 'class_configuration') AND isset(self::$class_configuration['database_table'])) {
			return self::$class_configuration['database_table'];
		} else {
			return strtolower(get_class());
		}
	}

	/**
	 * Trait_get_link_tables
	 *
	 * @access private
	 * @return array $tables
	 */
	private static function trait_get_link_tables() {
		$db = Database::Get();
		$table = self::trait_get_database_table();
		$fields = Util::mysql_get_table_fields($table);
		$tables = $db->getCol('SHOW tables');

		$joins = array();
		foreach ($fields as $field) {
			if (substr($field, -3) != '_id') {
				continue;
			}

			$link_table = substr($field, 0, -3);

			if (in_array($link_table, $tables)) {
				$joins[] = $link_table;
			}
		}
		return $joins;
	}
}
