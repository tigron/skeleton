<?php
/**
 * trait: Save
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

class Exception_Validation extends Exception {}

trait Save {
	/**
	 * Save the object
	 *
	 * @access public
	 */
	public function save() {
		// If we have a validate() method, execute it
		if (is_callable(array($this, 'validate'))) {
			if ($this->validate() === false) {
				throw new Exception_Validation();
			}
		}

		// If we have a sanitize() method, execute it
		if (is_callable(array($this, 'sanitize'))) {
			$this->sanitize();
		}

		$table = self::trait_get_database_table();
		$db = self::trait_get_database();

		// If $this->id is null, do an insert, otherwise do an update
		if (!isset($this->id) OR $this->id === null) {
			$this->details['created'] = date('Y-m-d H:i:s');
			$db->insert($table, $this->details);
			$this->id = $db->get_one('SELECT LAST_INSERT_ID()');
		} else {
			$this->details['updated'] = date('Y-m-d H:i:s');
			$where = 'id=' . $db->quote($this->id);
			$db->update($table, $this->details, $where);
		}

		$this->get_details();
	}
}
