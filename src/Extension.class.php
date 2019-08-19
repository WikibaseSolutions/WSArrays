<?php

/**
 * Abstract class Extension
 *
 * @extends ExtensionFactory
 */
abstract class Extension extends ExtensionFactory {
    /**
     * This function should return a string containing the name of the class (which is
     * also the name of the parser function).
     *
     * @return string
     */
    abstract public function getName();

    /**
     * This function returns the name of any aliases the might want to define for
     * the parser function.
     *
     * @return array
     */
    abstract public function getAliases();

    /**
     * Specify whether to implement this extension as an 'sfh' hook or a 'standard' hook.
     *
     * @return null|string
     */
    abstract public function getType();
}