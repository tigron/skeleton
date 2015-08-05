<?php
/**
 * Additional functions and filters for Twig
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Template_Twig_Extension_Default extends Twig_Extension {

	private $environment;

	/**
	 * Init runtime
	 *
	 * @access public
	 */
	public function initRuntime(Twig_Environment $environment) {
        parent::initRuntime($environment);
        $this->environment = $environment;
    }

	/**
	 * Returns a list of globals
	 *
	 * @return array
	 */
	public function getGlobals() {
		$templates = [
			'base' => '_default/macro.base.twig',
			'form' => '_default/form.base.twig',
		];

		$globals = [];
		foreach ($templates as $key => $template) {
			try {
				$loaded_template = $this->environment->loadTemplate('_default/macro.base.twig');
				$globals[$key] = $loaded_template;
			} catch (Twig_Error_Loader $e) { }
		}

		return $globals;
	}

    /**
     * Returns a list of filters
     *
     * @return array
     */
    public function getFilters() {
        return [
			new Twig_SimpleFilter('print_r', [$this, 'print_r_filter'], ['is_safe' => ['html']]),
			new Twig_SimpleFilter('json_decode', [$this, 'json_decode_filter'], ['is_safe' => ['html']]),
			new Twig_SimpleFilter('serialize', [$this, 'serialize_filter'], ['is_safe' => ['html']]),
			new Twig_SimpleFilter('round', [$this, 'round_filter'], ['is_safe' => ['html']]),
			new Twig_SimpleFilter('date', [$this, 'date_filter'], ['needs_environment' => true, 'is_safe' => ['html']]),
			new Twig_SimpleFilter('datetime', [$this, 'datetime_filter'], ['needs_environment' => true, 'is_safe' => ['html']]),
			new Twig_SimpleFilter('filesize', [$this, 'filesize_filter'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
	}

	/**
     * Returns a list of functions
     *
     * @return array
     */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction('strpos', [$this, 'strpos_function'], ['is_safe' => ['html']]),
			new Twig_SimpleFunction('math_add', [$this, 'math_add_function'], ['is_safe' => ['html']]),
			new Twig_SimpleFunction('math_sub', [$this, 'math_sub_function'], ['is_safe' => ['html']]),
			new Twig_SimpleFunction('math_mul', [$this, 'math_mul_function'], ['is_safe' => ['html']]),
		];
	}

	/**
	 * Filter print_r
	 *
	 * @param mixed $value
	 * @param bool $raw
	 * @return string $output
	 */
	public function print_r_filter($value, $raw = true) {
		$output = '';
		if ($raw === false) {
			$output = '<pre>';
		}

		$output .= print_r($value, true);

		if ($raw === false) {
			$output .= '</pre>';
		}

	    return $output;
	}

	/**
	 * Filter serialize
	 *
	 * @param mixed $value
	 * @return string $output
	 */
	public function serialize_filter($value) {
	    return serialize($value);
	}

	/**
	 * Filter round
	 *
	 * @param float $value
	 * @param int $decimal
	 * @return int $output
	 */
	public function round_filter($value, $decimal=0) {
		return round($value, $decimal);
	}

	/**
	 * JSON decode
	 *
	 * @param string $json
	 * @return array $array
	 */
	public function json_decode_filter($json) {
		return json_decode($json);
	}

	/**
	 * Filter date
	 *
	 * @param string $date
	 * @param string $format
	 * @return string $output
	 */
	public function date_filter(Twig_Environment $env, $date, $format = 'd/m/Y') {
		return twig_date_format_filter($env, $date, $format);
	}

	/**
	 * Filter datetime
	 *
	 * @param string $datetime
	 * @param string $format
	 * @return string $output
	 */
	public function datetime_filter(Twig_Environment $env, $datetime, $format = 'd/m/Y H:i:s') {
		return twig_date_format_filter($env, $datetime, $format);
	}

	/**
	 * Filesize filter
	 *
	 * @param int $filesize
	 * @return string $output
	 */
	public function filesize_filter(Twig_Environment $env, $filesize) {
		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'b';
		}
		$filesize = $new_filesize;

		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'Kb';
		}
		$filesize = $new_filesize;

		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'Mb';
		}
		$filesize = $new_filesize;

		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'Gb';
		}
		$filesize = $new_filesize;

		$new_filesize = $filesize / 1024;
		if ($new_filesize < 1) {
			return number_format($filesize, 2, '.', ' ') . 'Tb';
		} else {
			return 'unknown';
		}
	}

	/**
	 * Function strpos
	 *
	 * @param mixed $value
	 * @param string $to_search
	 * @return mixed $output
	 */
	public function strpos_function($value, $to_search) {
	    return strpos($value, $to_search);
	}

	/**
	 * Function math add
	 *
	 * @param float $value
	 * @param float $value2
	 * @return float $sum
	 */
	public function math_add_function($value, $value2) {
		return Util::math_add($value, $value2);
	}

	/**
	 * Function math sub
	 *
	 * @param float $value
	 * @param float $value2
	 * @return float $diff
	 */
	public function math_sub_function($value, $value2) {
		return Util::math_sub($value, $value2);
	}

	/**
	 * Function math multiply
	 *
	 * @param float $value
	 * @param float $value2
	 * @return float $product
	 */
	public function math_mul_function($value, $value2) {
		return Util::math_mul($value, $value2);
	}

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName() {
        return 'Default';
    }
}
