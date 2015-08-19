<?php
/**
 * User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class User {
	use Model, Get, Save, Delete;

	/**
	 * @var User $user
	 * @access private
	 */
	private static $user = null;

	/**
	 * Validate user data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = []) {
		$required_fields = ['username', 'password', 'firstname', 'lastname', 'email'];
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
	 * Fetch a user by username
	 *
	 * @access public
	 * @param string $username
	 * @return User $user
	 */
	public static function get_by_username($username) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM user WHERE username = ?', [$username]);

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
		$id = $db->getOne('SELECT id FROM user WHERE email = ?', [$email]);

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
	public static function get_paged($sort, $direction, $page, $extra_conditions = [], $all = false) {
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
		$users = [];
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
	public static function count($extra_conditions = []) {
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
