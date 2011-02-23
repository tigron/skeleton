<?php
/**
 * Database class
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once LIB_PATH . '/base/Util.php';

class Database {
	/**
	 * @var DatabaseProxy
	 * @access private
	 */
	private static $proxy = array();

	/**
	 * Private (disabled) constructor
	 *
	 * @access private
	 */
	private function __construct() { }

	/**
	 * Get function, returns a Database object, handles connects if needed
	 *
	 * @return DB
	 * @access public
	 */
	public static function Get($config_db = 'database') {
		if (!isset(self::$proxy[$config_db]) OR self::$proxy[$config_db] == false) {
			self::$proxy[$config_db] = new DatabaseProxy();
			self::$proxy[$config_db]->connect($config_db);
		}
		return self::$proxy[$config_db];
	}
}


/**
 * Database Proxy Class
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */
class DatabaseProxy {
	/**
	 * PEAR DB Object
	 *
	 * @var DB
	 * @access public
	 */
	public $database = array();

	/**
	 * @var array
	 * @access public
	 */
	public $queries = 0;

	/**
	 * DatabaseProxy constructor
	 *
	 * @access public
	 */
	public function __construct($config_db = 'database') {
		$this->connect($config_db);
	}

	/**
	 * @param string Method to call
	 * @param array Arguments to pass
	 * @access public
	 */
	public function __call($method, $arguments) {
		$this->queries++;
		switch (strtolower($method)) {
			case 'getrow':               return $this->stripslashes_result($this->_getrow($arguments)); break;
			case 'getall':               return $this->stripslashes_result($this->_getall($arguments)); break;
			case 'getcol':               return $this->stripslashes_result($this->_getcol($arguments)); break;
			case 'query':                return $this->_query($arguments); break;
			case 'getone':               return $this->stripslashes_result($this->_getone($arguments)); break;
			case 'limitquery':           return $this->_limitquery($arguments); break;
			case 'autoexecute':          return $this->_autoexecute($arguments); break;
			case 'quote':                return $this->_quote($arguments); break;
			case 'quoteidentifier':      return $this->_quoteidentifier($arguments); break;
			case 'createdatabase':       return $this->_createDatabase($arguments); break;
			case 'dropdatabase':         return $this->_dropDatabase($arguments); break;
			case 'createuser':           return $this->_createUser($arguments); break;
			case 'updateuserpassword':   return $this->_updateUserPassword($arguments); break;
			case 'dropuser':             return $this->_dropUser($arguments); break;
			case 'grantallprivileges':   return $this->_grantAllPrivileges($arguments); break;
			case 'revokeallprivileges':  return $this->_revokeAllPrivileges($arguments); break;
			default:                     return call_user_func_array(array($this->database, $method), $arguments);
		}
	}

	/**
	 * Stripslashes on result
	 *
	 * @access private
	 * @param array $data
	 * @return array $result
	 */
	private function stripslashes_result($data) {
		if ($data === null) {
			return $data;
		} elseif (is_array($data)) {
			foreach ($data as $key => $field) {
				if (is_array($field)) {
					$data[$key] = $this->stripslashes_result($field);
				} else {
					$data[$key] = $this->custom_stripslashes($field);
				}
			}
		} else {
			return $this->custom_stripslashes($data);
		}
		return $data;
	}

	/**
	 * Customer stripslashes
	 *
	 * @access private
	 * @return string $slashed
	 * @param string $string
	 */
	private function custom_stripslashes($string) {
		if ($string === null) {
			return $string;
		} else {
			return stripslashes($string);
		}
	}

	/**
	 * Connect to the database
	 *
	 * @access public
	 * @param string $config_db
	 * @throws Exception Throws an Exception when the Database is unavailable
	 */
	public function connect($config_db) {
		/**
		 * Uses PEAR DB
		 */
		require_once 'MDB2.php';

		$config = Config::Get();
		$this->database = @MDB2::Factory($config->$config_db);
		if (@MDB2::isError($this->database)) {
			throw new Exception('Database connection failed');
		}

		if ($config->debug == true) {
			$this->database->setOption('debug', 1);
		}

		$this->database->loadModule('Extended');
		$this->database->loadModule('Manager');
		$this->database->setFetchMode(MDB2_FETCHMODE_ASSOC);
		$this->database->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_EMPTY_TO_NULL);
		$this->database->setOption('quote_identifier', true);
		$this->database->query("SET NAMES 'utf8'");
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _quote($arguments) {
		return $this->database->quote($arguments[0]);
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _quoteidentifier($arguments) {
		return $this->database->quoteIdentifier($arguments[0]);
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _getrow($arguments) {
		if (!isset($arguments[1])) {
			$arguments[1] = NULL;
		}

		return $this->database->extended->getRow($arguments[0], NULL, $arguments[1]);
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _getcol($arguments) {
		if (!isset($arguments[1])) {
			$arguments[1] = NULL;
		}
		return $this->database->extended->getCol($arguments[0], NULL, $arguments[1]);
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _autoexecute($arguments) {
		if (!isset($arguments[1])) {
			$arguments[1] = NULL;
		}
		$arguments[1] = Util::filter_table_data($arguments[0], $arguments[1], $this);
		$return = $this->database->extended->autoExecute($arguments[0], $arguments[1], $arguments[2], $arguments[3], NULL);
		if (@MDB2::isError($this->database)) {
			throw new Exception('Database error');
		}
		return $return;
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _getone($arguments) {
		if (!isset($arguments[1])) {
			$arguments[1] = NULL;
		}
		return $this->database->extended->getOne($arguments[0], NULL, $arguments[1]);
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _limitquery($arguments) {
		$arguments[0] = $arguments[0] . ' LIMIT '.$arguments[1] . ',' . $arguments[2];

		return $this->_getall(array($arguments[0]));
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _getall($arguments) {
		if (!isset($arguments[1])) {
			$arguments[1] = NULL;
		}
		$result = $this->database->extended->getAll($arguments[0], NULL, $arguments[1]);
		return $result;
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _query($arguments) {
		if (!isset($arguments[1])) {
			$arguments[1] = NULL;
		}
		return $this->database->extended->execParam($arguments[0], $arguments[1]);
	}

	/**
	 * Wrapper around MDB2 to product DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _createDatabase($arguments) {
		return $this->database->createDatabase($arguments[0]);
	}

	/**
	 * Wrapper around MDB2 to product DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _dropDatabase($arguments) {
		return $this->database->dropDatabase($arguments[0]);
	}

	/**
	 * Wrapper around MDB2 to product DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _createUser($arguments) {
		return $this->database->createUser($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
	}

	/**
	 * Wrapper around MDB2 to product DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _updateUserPassword($arguments) {
		return $this->database->updateUserPassword($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
	}

	/**
	 * Wrapper around MDB2 to product DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _dropUser($arguments) {
		return $this->database->dropUser($arguments[0], $arguments[1]);
	}

	/**
	 * Wrapper around MDB2 to product DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _grantAllPrivileges($arguments) {
		return $this->database->grantAllPrivileges($arguments[0], $arguments[1], $arguments[2]);
	}
	/**
	 * Wrapper around MDB2 to product DB-like syntax to the consumer
	 *
	 * @param array Arguments
	 * @access private
	 */
	private function _revokeAllPrivileges($arguments) {
		return $this->database->revokeAllPrivileges($arguments[0], $arguments[1], $arguments[2]);
	}
}
?>
