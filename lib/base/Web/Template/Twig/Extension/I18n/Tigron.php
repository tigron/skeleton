<?php
/**
 * Translation extension for Twig
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/base/Web/Template/Twig/Extension/TokenParser/Trans/Tigron.php';
require_once LIB_PATH . '/base/Web/Template/Twig/Extension/Node/Trans/Tigron.php';

class Twig_Extensions_Extension_I18n_Tigron extends Twig_Extensions_Extension_I18n {

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers() {
        return array(new Twig_Extensions_TokenParser_Trans_Tigron());
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters() {
        return array(
            'trans' => new Twig_Filter_Function('Translation::translate'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return 'i18n';
    }
}
