<?php
/**
 * trait: Save
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Exception_Validation extends Exception {

	private $errors = [];

	public function __construct($errors) {
		$this->errors = $errors;
	}

	public function get_errors() {
		return $this->errors;
	}

}

trait Save {

	/**
	 * Save the object
	 *
	 * @access public
	 */
	public function save($validate = true) {
		// If we have a validate() method, execute it
		if (is_callable(array($this, 'validate')) and $validate) {
			if ($this->validate($errors) === false) {
				throw new Exception_Validation($errors);
			}
		}

		$table = self::trait_get_database_table();

		$db = self::trait_get_database();

		if (!isset($this->id) OR $this->id === null) {
			$mode = MDB2_AUTOQUERY_INSERT;
			$this->details['created'] = date('Y-m-d H:i:s');
			$where = false;
		} else {
			$mode = MDB2_AUTOQUERY_UPDATE;
			$this->details['updated'] = date('Y-m-d H:i:s');
			$where = 'id=' . $db->quote($this->id);
		}

		if (is_callable(array($this, 'generate_slug'))) {
			$slug = $this->generate_slug();
			$this->details['slug'] = $slug;
		}

		$db->autoExecute($table, $this->details, $mode, $where);

		if ($mode === MDB2_AUTOQUERY_INSERT) {
			$this->id = $db->getOne('SELECT LAST_INSERT_ID();');
		}

		$this->get_details();
	}
}
