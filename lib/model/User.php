<?php
/**
 * User class
 *
 * @package %%Package%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @version $Id$
 */

require_once LIB_PATH . '/model/Country.php';
require_once LIB_PATH . '/model/Log.php';
require_once LIB_PATH . '/model/Language.php';

class User {

	/**
	 * @var User $user
	 * @access private
	 */
	private static $user = null;

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
	 * Get the details of this user
	 *
	 * @access private
	 */
	private function get_details() {
		if (!isset($this->id) OR $this->id === null) {
			throw new Exception('Could not fetch user data: ID not set');
		}

		$db = Database::Get();
		$details = $db->getRow('SELECT * FROM user WHERE id=?', array($this->id));
		if ($details === null) {
			throw new Exception('Could not fetch user data: no user found with id ' . $this->id);
		}

		$this->details = $details;
	}

	/**
	 * Validate user data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = array()) {
		$required_fields = array('username', 'password', 'firstname', 'lastname', 'email');
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}		

		if (count($errors) > 0) {			
			return false;
		}

		if (isset($this->details['repeat_password']) AND $this->details['password'] != $this->details['repeat_password']) {
			$errors['password'] = 'do not match';
		}

		if (!Util::validate_email($this->details['email'])) {
			$errors['email'] = 'syntax error';
		}

		if ($this->id === null) {
			try {
				$user = self::get_by_username($this->details['username']);
				$errors['username'] = 'already exists';
			} catch (Exception $e) { }
		}
		
		if ($this->id === null) {
			try {
				$user = self::get_by_email($this->details['email']);
				$errors['email'] = 'already exists';
			} catch (Exception $e) { }
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Save the user
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

		$db->autoExecute('user', $this->details, $mode, $where);

		if ($mode === MDB2_AUTOQUERY_INSERT) {
			$this->id = $db->getOne('SELECT LAST_INSERT_ID();');
			Log::create('add', $this);
		} else {
			Log::create('edit', $this);		
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
		if (($key == 'password' OR $key == 'repeat_password') AND strlen($value) > 0) {
			$value = sha1($value);
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
		if ($key == 'language') {
			return Language::get_by_id($this->language_id);
		} elseif ($key == 'country') {
			return Country::get_by_id($this->country_id);
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
		if ($key == 'country') {
			return true;
		} elseif (isset($this->details[$key])) {
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
		$db->query('DELETE FROM user WHERE id=?', array($this->id));
	}

	/**
	 * Get video vote
	 *
	 * @access public
	 * @param Video $video
	 * @return mixed
	 */
	public function get_video_vote(Video $video) {
		try {
			$video_vote = Video_Vote::get_by_video_user($video, $this);
			return $video_vote;
		} catch (Exception $e) {}

		return false;
	}

	/**
	 * Get User info
	 * 
	 * @access public
	 */
	public function get_info() {
		return $this->details;
	}

	/**
	 * Get a User by ID
	 *
	 * @access public
	 * @parm int $id
	 * @return user
	 */
	public static function get_by_id($id) {
		return new User($id);
	}

	/**
	 * Get all
	 *
	 * @access public
	 * @return array users
	 */
	public static function get_all() {
		$db = Database::Get();
		$ids = $db->getCol('SELECT id FROM user', array());
		$users = array();
		foreach ($ids as $id) {
			$users[] = User::get_by_id($id);
		}
		return $users;
	}

	/**
	 * Fetch a user by username
	 *
	 * @access public
	 * @param string $username
	 * @return User $user
	 */
	public static function get_by_username($username) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM user WHERE username = ?', array($username));
		
		if ($id === null) {
			throw new Exception('User not found');
		}

		return User::get_by_id($id);
	}

	/**
	 * Fetch a user by email
	 *
	 * @access public
	 * @param string $email
	 * @return User $user
	 */
	public static function get_by_email($email) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM user WHERE email = ?', array($email));
		
		if ($id === null) {
			throw new Exception('User not found');
		}

		return User::get_by_id($id);
	}

	/**
	 * Authenticate a user
	 *
	 * @access public
	 * @throws Exception
	 * @param string $username
	 * @param string $password
	 * @return User $user
	 */
	public static function authenticate($username, $password) {
		$user = User::get_by_username($username);

		if ($user->password != sha1($password)) {
			throw new Exception('Authentication failed');
		}

		return $user;
	}
	
	/**
	 * Get the current user
	 *
	 * @access public
	 * @return User $user
	 */
	public static function get() {
		if (self::$user !== null) {
			return self::$user;
		}

		throw new Exception('No user set');
	}
	
	/**
	 * Set the current user
	 *
	 * @access public
	 * @param User $user
	 */
	public static function set(User $user) {
		self::$user = $user;
	}


	/**
	 * Get paged
	 *
	 * @access public
	 * @param string $sort
	 * @param string $direction
	 * @param int $page
	 * @param int $all
	 * @param array $extra_conditions
	 */
	public static function get_paged($sort, $direction, $page, $extra_conditions = array(), $all = false) {
		$db = Database::Get();

		$where = '';

		foreach ($extra_conditions as $key => $value) {
			if ($key != '%search%') {
				if (!is_array($extra_conditions[$key])) {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' = ' . $db->quote($value) . ' ';
				} else {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' ' . $value[0] . ' ' . $db->quote($value[1]) . ' ';
				}
			}
		}

		$config = Config::Get();
		if (!$all) {
			$limit = 20;
		} else {
			$limit = 1000;
		}

		if ($page < 1) {
			$page = 1;
		}

		if (isset($extra_conditions['%search%'])) {
			$where .= "AND (";
			$where .= "u.lastname LIKE '%" . $extra_conditions['%search%'] . "%' ";
			$where .= "OR u.firstname LIKE '%" . $extra_conditions['%search%'] . "%' ";
			$where .= "OR c.name LIKE '%" . $extra_conditions['%search%'] . "%' ";
			$where .= ") ";
		}

		$sql = 'SELECT DISTINCT(u.id)
		        FROM `user` u
				INNER JOIN country c ON c.id = u.country_id
		        WHERE 1 ' . $where . '
		        ORDER BY ' . $sort . ' ' . $direction . '
		        LIMIT ' . ($page-1)*$limit . ', ' . $limit;

		$ids = $db->getCol($sql);
		$users = array();
		foreach ($ids as $id) {
			$users[] = User::get_by_id($id);
		}

		return $users;
	}

	/**
	 * Count the users
	 *
	 * @access public
	 * @param array $extra_conditions
	 * @return int $count
	 */
	public static function count($extra_conditions = array()) {
		$db = Database::Get();

		$where = '';
		foreach ($extra_conditions as $key => $value) {
			if ($key != '%search%') {
				if (!is_array($extra_conditions[$key])) {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' = ' . $db->quote($value) . ' ';
				} else {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' ' . $value[0] . ' ' . $db->quote($value[1]) . ' ';
				}
			}
		}

		if (isset($extra_conditions['%search%'])) {
			$where .= "AND (";
			$where .= "u.lastname LIKE '%" . $extra_conditions['%search%'] . "%' ";
			$where .= "OR u.firstname LIKE '%" . $extra_conditions['%search%'] . "%' ";
			$where .= "OR c.name LIKE '%" . $extra_conditions['%search%'] . "%' ";
			$where .= ") ";
		}

		$sql = 'SELECT COUNT(DISTINCT(u.id))
		        FROM `user` u
				INNER JOIN country c ON c.id = u.country_id
		        WHERE 1 ' . $where;

		$count = $db->getOne($sql);

		return $count;
	}
}
?>