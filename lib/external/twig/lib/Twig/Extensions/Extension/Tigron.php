<?php

/**
 * This file is part of Twig.
 *
 * (c) 2011 Tigron
 *
 * @author Tigron <info@tigron.be>
 */
class Twig_Extensions_Extension_Tigron extends Twig_Extension
{
    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'print_r' => new Twig_Filter_Function('twig_print_r'),
        );
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'Tigron';
    }
}

function twig_print_r($value)
{
	return print_r($value, true);
}
