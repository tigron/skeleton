<?php
/**
 * trait: Page
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 * @author David Vandemaele <david.vandemaele@tigron.be>
 */

trait Page {
	/**
	 * Get where clause for paging
	 *
	 * @access public
	 * @param array $extra_conditions
	 */
	private static function get_search_where($extra_conditions = array()) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$fields = Util::mysql_get_table_fields($table);
		$joins = self::trait_get_link_tables();

		$extra_conditions_raw = $extra_conditions;
		// Cleanup 'extra_conditions'
		foreach ($extra_conditions as $key => $value) {
			if ($key == '%search%') {
				continue;
			}

			$exists = false;
			foreach ($fields as $field) {
				if ($field == $key) {
					$exists = true;
				}
			}

			if (!$exists) {
				//unset($extra_conditions[$key]);
			}
		}

		$where = "\n\t";

		foreach ($extra_conditions as $key => $value) {
			if ($key != '%search%') {

				if (is_array($value[1])) {
					$where .= 'AND (0';
					foreach ($value[1] as $element) {
						$where .= ' OR ' . $db->quoteidentifier($key) . ' ' . $value[0] . ' ' . $db->quote($element);
					}
					$where .= ') ';
				} else {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' ' . $value[0] . ' ' . $db->quote($value[1]) . ' ' . "\n\t";
				}
			}
		}

		if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
			// Object Text fields: language_id
			if (isset($extra_conditions_raw['language_id'])) {
				$where .= 'AND object_text.language_id = ' . $db->quote($extra_conditions_raw['language_id'][1]) . ' ' . "\n\t";
			}
		}

		if (isset($extra_conditions['%search%']) AND $extra_conditions['%search%'] != '') {
			$where .= 'AND (0 ';

			foreach ($fields as $field) {
				$where .= 'OR ' . $table . '.' . $field . " LIKE '%"  . $extra_conditions['%search%'] . "%' " . "\n\t";
			}

			foreach ($joins as $join) {
				$fields = Util::mysql_get_table_fields($join);
				foreach ($fields as $field) {
					$where .= 'OR ' . $join . '.' . $field . " LIKE '%"  . $extra_conditions['%search%'] . "%' " . "\n\t";
				}
			}

			if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
				$where .= "OR object_text.content LIKE '%" . $extra_conditions['%search%'] . "%' " . "\n\t";
			}

			$where .= ') ' . "\n";
		}

		return $where;
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
	public static function get_paged($sort = 1, $direction = 'asc', $page = 1, $extra_conditions = array(), $all = false) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$where = self::get_search_where($extra_conditions);
		$joins = self::trait_get_link_tables();

		$object = new self();
		if (is_callable($sort)) {
			$sorter = 'object';
		} elseif (method_exists($object, $sort) AND is_callable(array($object, $sort))) {
			$sorter = 'object';
		} else {
			$sorter = 'db';
		}

		$config = Config::Get();

		if (!$all) {
			$limit = $config->items_per_page;
		} else {
			$limit = 1000;
		}

		if ($page < 1) {
			$page = 1;
		}

        if ($direction != 'asc') {
			$direction = 'desc';
        }

		$sql  = 'SELECT DISTINCT(' . $table . '.id) ' . "\n";
		$sql .= 'FROM `' . $table . '`' . "\n";
		foreach ($joins as $join) {
			$sql .= 'LEFT OUTER JOIN `' . $join . '` on `' . $table . '`.' . $join . '_id = ' . $join . '.id '  . "\n";
		}

		if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
			$sql .= 'LEFT OUTER JOIN object_text ON object_text.classname = "' . get_class() . '" AND object_text.object_id=' . $table . '.id ';
			if ($sorter == 'db' AND in_array($sort, self::$object_text_fields)) {
				$sql .= 'AND object_text.label = ' . $db->quote($sort) . ' AND object_text.language_id = ' . Application::Get()->language->id . ' ';

				$sort = 'object_text.content';
			}
			$sql .= "\n";
		}
		$sql .= 'WHERE 1 ' . $where . "\n";

		if ($sorter == 'db') {
			$sql .= 'ORDER BY ' . $sort . ' ' . $direction;
		}

		if ($all !== true AND $sorter == 'db') {
			$sql .= ' LIMIT ' . ($page-1)*$limit . ', ' . $limit;
		}

		$ids = $db->getCol($sql);
		$objects = array();
		foreach ($ids as $id) {
			$objects[] = self::get_by_id($id);
		}

		if ($sorter == 'object') {
			$objects = Util::object_sort($objects, $sort, $direction);

			if ($direction == 'desc') {
				$objects = array_reverse($objects);
			}

			$objects = array_slice($objects, ($page-1)*$limit, $limit);
		}


		return $objects;
	}

	/**
	 * Count the users
	 *
	 * @access public
	 * @param array $extra_conditions
	 * @return int $count
	 */
	public static function count($extra_conditions = array()) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$where = self::get_search_where($extra_conditions);
		$joins = self::trait_get_link_tables();

		$sql  = 'SELECT COUNT(DISTINCT(' . $table . '.id)) ';
		$sql .= 'FROM `' . $table . '` ';

		foreach ($joins as $join) {
			$sql .= 'LEFT OUTER JOIN `' . $join . '` on `' . $table . '`.' . $join . '_id = ' . $join . '.id ';
		}
		if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
			$sql .= 'LEFT OUTER JOIN object_text on object_text.classname = "' . get_class() . '" AND object_text.object_id=' . $table . '.id '  . "\n";
		}


		$sql .= 'WHERE 1 ' . $where;
		$count = $db->getOne($sql);

		return $count;
	}
}
