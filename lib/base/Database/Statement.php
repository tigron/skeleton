<?php
/**
 * Database Statement Class
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */
class Database_Statement extends Mysqli_Stmt {

	/**
	 * Get columns of resultset
	 *
	 * @access public
	 * @return array $columns
	 */
	private function get_columns() {
		$meta = $this->result_metadata();

		// FIXME: This is a check to be compatible with PHP versions > 5.3.6
		if (version_compare(PHP_VERSION, '5.3.6') >= 0) {
			$database_in_key = true;
		} else {
			$database_in_key = false;
		}

		$columns = array();
		while ($column = $meta->fetch_field()) {
			if ($database_in_key === true) {
				$columns[] = $column->db . '.' . $column->table . '.' . $column->name;
			} else {
				$columns[] = $column->table . '.' . $column->name;
			}
		}
		return $columns;
	}
	
	/**
	 * Fetch_assoc
	 *
	 * @access public
	 * @return array $data
	 */
	public function fetch_assoc() {
		$data = array();
		$params = array();

		foreach ($this->get_columns() as $column) {
			$params[$column] = &$data[$column];
		}

		$result = call_user_func_array(array($this, 'bind_result'), $params);
		$data = array();
		while ($this->fetch()) {
			$row = array();
			foreach ($params as $key => $value) {
				$key = 	substr($key, strrpos($key, '.') + 1);
				$row[$key] = $value;
			}
			$data[] = $row;
		}
		return $data;
	}
}
?>
