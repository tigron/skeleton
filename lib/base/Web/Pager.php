<?php
/**
 * Handles paginating of query results
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Web_Pager {
	/**
	 * Classname
	 *
	 * @access private
	 * @var string $classname
	 */
	private $classname;

	/**
	 * Options
	 *
	 * @access private
	 * @var array $options
	 */
	private $options = [];

	/**
	 * Items
	 *
	 * @access public
	 * @var array $items
	 */
	public $items = [];

	/**
	 * Item count
	 *
	 * @access public
	 * @var int $item_count
	 */
	public $item_count = 0;

	/**
	 * Interval
	 *
	 * @access private
	 * @var int $interval
	 */
	private $interval = 5;

	/**
	 * Constructor
	 *
	 * @access private
	 * @param $code
	 */
	public function __construct($classname = null) {
		if ($classname === null) {
			throw new Exception('You must provide a classname');
		}
		$this->classname = $classname;
		if (file_exists(LIB_PATH . '/' . ucfirst($this->classname) . '.php')) {
			require_once LIB_PATH . '/' . ucfirst($this->classname) . '.php';
		}

		$this->options['extra_conditions'] = array();
		$this->options['direction'] = 'ASC';
		$this->options['page'] = 1;
	}

	/**
	 * Set sort field
	 *
	 * @access public
	 * @param string $sort
	 */
	public function set_sort($sort) {
		$this->options['sort'] = $sort;
	}

	/**
	 * Set direction
	 *
	 * @access public
	 * @param string $direction
	 */
	public function set_direction($direction = 'asc') {
		$this->options['direction'] = $direction;
	}

	/**
	 * Set sort_permissions
	 *
	 * @access public
	 * @param array $sort_permissions
	 */
	public function set_sort_permissions($sort_permissions) {
		$this->options['sort_permissions'] = $sort_permissions;
	}

	/**
	 * Set page
	 *
	 * @access public
	 * @param int $page
	 */
	public function set_page($page) {
		$this->options['page'] = $page;
	}

	/**
	 * Set condition
	 *
	 * @access public
	 * @param string $field
	 * @param string $comparison (optional)
	 * @param string $value
	 */
	public function set_condition() {
		$params = func_get_args();
		$extra_conditions = $this->options['extra_conditions'];

		$field = array_shift($params);
		if ($field == '%search%') {
			$this->options['extra_conditions']['%search%'] = array_shift($params);
			return;
		}

		if (count($params) == 1) {
			$extra_conditions[$field] = array('=', array_shift($params));
		} else {
			$extra_conditions[$field] = array( array_shift($params), array_shift($params));
		}

		$this->options['extra_conditions'] = $extra_conditions;
	}

	/**
	 * Get search
	 *
	 * @access public
	 * @return string $search
	 */
	public function get_search() {
		if (isset($this->options['extra_conditions']['%search%'])) {
			return $this->options['extra_conditions']['%search%'];
		} else {
			return '';
		}
	}

	/**
	 * Create the header cells of the paged table
	 *
	 * @param string $header Name of the header
	 * @param string $field_name Name of the database field that is represented here
	 * @return string $output
	 * @access public
	 */
	public function create_header($header, $field_name) {

		if (!isset($this->options['sort'])) {
			$this->options['sort'] = key($this->options['sort_permissions']);
		}

		if ($this->options['sort'] == $field_name) {
			if ($this->options['direction'] == 'ASC') {
				$direction = 'DESC';
			} else {
				$direction = 'ASC';
			}
		} else {
			$direction = 'ASC';
		}

		if (isset($_GET['search'])) {
			$search = '&search=' . $_GET['search'];
		} else {
			$search = '';
		}

		$output = '<a href="' . $_SERVER['REDIRECT_URL'] . '?page=' . $this->options['page'] . $search . '&sort=' . $field_name .'&direction=' . $direction .'&extra_conditions=' . base64_encode(serialize($this->options['extra_conditions'])) . '">';
		$output .= $header . ' ';

		if ($this->options['sort'] == $field_name) {
			if ($direction == 'DESC') {
				$output .= '<span class="glyphicon glyphicon-chevron-up"></span>';
			} else {
				$output .= '<span class="glyphicon glyphicon-chevron-down"></span>';
			}
		}
		$output .= '</a>';

		return $output;
	}

	/**
	 * Get conditions
	 *
	 * @return array $conditions
	 */
	public function get_conditions() {
		return $this->options['extra_conditions'];
	}

	/**
	 * Paginate the results
	 *
	 * @access private
	 */
	public function page() {

		if (!isset($this->options['sort'])) {
			$this->options['sort'] = key($this->options['sort_permissions']);
		}

		if (isset($_GET['sort'])) {
			$this->options['sort'] = $_GET['sort'];
		}

		/**
		 * We now have to rewrite the sort according to the permissions
		 */
		if (!isset($this->options['sort_permissions'][$this->options['sort']])) {
			throw new Exception('Sorting not allowed for field ' . $this->options['sort']);
		}
		$sort = $this->options['sort_permissions'][$this->options['sort']];

		if (isset($_GET['direction'])) {
			$this->options['direction'] = $_GET['direction'];
		}

		if (isset($_GET['page'])) {
			$this->options['page'] = $_GET['page'];
		}

		if (isset($_GET['extra_conditions']) AND (count($this->options['extra_conditions']) == 0)) {
			$this->options['extra_conditions'] = unserialize(base64_decode(urldecode($_GET['extra_conditions'])));
		}

		$this->options['all'] = false;

		$params = array(
			$sort,
			$this->options['direction'],
			$this->options['page'],
			$this->options['extra_conditions'],
			$this->options['all']
		);

		$this->items = call_user_func_array(array($this->classname, 'get_paged'), $params);
		$this->item_count = call_user_func_array(array($this->classname, 'count'), array($this->options['extra_conditions']));
		$this->generate_links();
	}

	/**
	 * Generate the necessary links to navigate the paged result
	 *
	 * @access private
	 */
	private function generate_links() {

		$config = Config::Get();
		$items_per_page = $config->items_per_page;
		if ($items_per_page == 0) {
			$pages = 0;
		} else {
			$pages = ceil($this->item_count / $items_per_page);
		}
		// Don't make links if there is only one page
		if ($pages == 1) {
			$this->links = '';
			return;
		}

		$first_page_link = $this->options['page'] - $this->interval;
		if ($first_page_link < 0) {
			$to_end = $first_page_link * (-1);
		} else {
			$to_end = 0;
		}

		if (isset($_GET['search'])) {
			$search = '&search=' . $_GET['search'];
		} else {
			$search = '';
		}

		if ($first_page_link < 1) {
			$first_page_link = 1;
		}
		if ($this->options['page'] >= $pages) {
			$last_page_link = $pages;
		} else {
			$last_page_link = $this->options['page'] + $this->interval;
		}
		$last_page_link += $to_end;
		if ($last_page_link >= $pages) {
			$last_page_link = $pages;
		}

		$links = '';

		for ($i = $first_page_link; $i<=$last_page_link; $i++) {
			if ($i == $this->options['page']) {
				$links .= '<li class="active"><a href="#">' . $i . '</a></li>' ;
			} else {
				$links .= '<li><a href="' . $_SERVER['REDIRECT_URL'] . '?page=' . $i . '&sort=' . $this->options['sort'] . $search . '&direction=' . $this->options['direction'] . '&extra_conditions=' . urlencode(base64_encode(serialize($this->options['extra_conditions']))) . '">' . $i . '</a></li>';
			}
		}
		if ($first_page_link > 1) {
			$links = '<li><a href="' . $_SERVER['REDIRECT_URL'] . '?page=1&sort=' . $this->options['sort'] . $search . '&direction=' . $this->options['direction'] . '&extra_conditions=' . urlencode(base64_encode(serialize($this->options['extra_conditions']))) . '">[1]</a></li>' . $links;
		}
		if ($last_page_link < $pages) {
			$links .= '&nbsp;&nbsp;&nbsp;' . '<li><a href="' . $_SERVER['REDIRECT_URL'] . '?page=' . $pages . '&sort=' . $this->options['sort'] . $search . '&direction=' . $this->options['direction'] . '&extra_conditions=' . urlencode(base64_encode(serialize($this->options['extra_conditions']))) . '">' . $pages . '</a></li>';
		}

		$this->links = '<ul class="pagination pagination-centered">' . $links . '</ul></div>';
	}
}
