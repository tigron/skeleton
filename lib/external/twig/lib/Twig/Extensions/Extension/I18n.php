<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Twig_Extensions_Extension_I18n extends Twig_Extension
{
    private $function_translation;
    private $function_translation_plural;

	public function __construct($options = array()) {
        $options = array_merge(array(
            'function_translation_plural'   => 'ngettext',
            'function_translation'          => 'gettext',
        ), $options);

		$this->function_translation_plural = $options['function_translation_plural'];
		$this->function_translation = $options['function_translation'];
	}

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
    	$options = array(
            'function_translation_plural' => $this->function_translation_plural,
            'function_translation' => $this->function_translation,
    	);

        return array(new Twig_Extensions_TokenParser_Trans($options));
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'trans' => new Twig_Filter_Function($this->function_translation),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'i18n';
    }
}
