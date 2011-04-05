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
            'print_r' => new Twig_Filter_Function('twig_print_r_filter', array('is_safe' => array('html'))),
            'nl2br' => new Twig_Filter_Function('twig_nl2br_filter', array('is_safe' => array('html'))),
            'count' => new Twig_Filter_Function('twig_count_filter', array('is_safe' => array('html'))),
            'serialize' => new Twig_Filter_Function('twig_serialize_filter', array('is_safe' => array('html'))),
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

function twig_print_r_filter($value)
{
    return '<pre>' . print_r($value, true) . '</pre>';
}

function twig_nl2br_filter($value)
{
    return nl2br($value);
}

function twig_count_filter($value)
{
    return count($value);
}

function twig_serialize_filter($value)
{
    return serialize($value);
}
