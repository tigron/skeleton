<?php
/**
 * Log class
 *
 * @package %%Package%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @version $Id$
 */

class Log {
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
	 * Get the details of this log
	 *
	 * @access private
	 */
	private function get_details() {
		if (!isset($this->id) OR $this->id === null) {
			throw new Exception('Could not fetch log data: ID not set');
		}

		$db = Database::Get();
		$details = $db->getRow('SELECT * FROM log WHERE id=?', array($this->id));
		if ($details === null) {
			throw new Exception('Could not fetch log data: no log found with id ' . $this->id);
		}

		$this->details = $details;
	}

	/**
	 * Save the log
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

		$db->autoExecute('log', $this->details, $mode, $where);

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
		if ($key == 'object') {
			$this->object_id = $value->id;
			$this->classname = get_class($value);
		} else {
			$this->details[$key] = $value;
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
		if ($key == 'user') {
			return User::get_by_id($this->user_id);
		} elseif (!isset($this->details[$key])) {
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
		$db->query('DELETE FROM log WHERE id=?', array($this->id));
	}

	/**
	 * Get content
	 */
	public function get_content() {
		try {
			$by = ' (by ' . $this->user->username . ')';
		} catch (Exception $e) {
			$by = '';
		}

		return $this->content . $by ;
	}

	/**
	 * Get a Log by ID
	 *
	 * @access public
	 * @parm int $id
	 * @return log
	 */
	public static function get_by_id($id) {
		return new Log($id);
	}

	/**
	 * Get all
	 *
	 * @access public
	 * @return array logs
	 */
	public static function get_all() {
		$db = Database::Get();
		$ids = $db->getCol('SELECT id FROM log LIMIT 50', array());

		$logs = array();
		foreach ($ids as $id) {
			$logs[] = Log::get_by_id($id);
		}

		return $logs;
	}
	
	/**
	 * Get by Object
	 *
	 * @access public
	 * @param object $object
	 */
	public static function get_by_object($object) {
		$db = Database::Get();
		$classname = get_class($object);
		$ids = $db->getCol('SELECT id FROM log WHERE classname=? AND object_id=? ORDER BY id DESC LIMIT 50', array($classname, $object->id));

		$logs = array();
		foreach ($ids as $id) {
			$logs[] = Log::get_by_id($id);
		}

		return $logs;
	}
	
	/**
	 * Create a log object
	 *
	 * @access public
	 * @param string $action
	 * @param object $object
	 */
	public static function create($action, $object = null) {
		// what class is it
		$classname = '';
		if (!is_null($object)) {
			$classname = strtolower(get_class($object));
		}

		$log = new Log();
		
		try {
			$user = User::Get();
			$log->user_id = $user->id;			
		} catch (Exception $e) {
			$log->user_id = 0;
		}		
		
		if ($action == 'add') {
			$content = ucfirst($classname) . ' created';
		} elseif ($action == 'edit') {
			$content = ucfirst($classname) . ' edited';
		} else {
			$content = ucfirst($action);
		}

		$log->classname = $classname;
		$log->object_id = !is_null($object) ? $object->id : 0;
		$log->content = $content;
		$log->save();

		return $log;
	}
}
?>