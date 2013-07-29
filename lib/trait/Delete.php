<?php
/**
 * trait: Delete
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

trait Delete {
	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$table = self::trait_get_database_table();
		$db = self::trait_get_database();

		$db->query('DELETE FROM ' . $table . ' WHERE id=?', array($this->id));
	}
}
