<?php

/**
 * WSArrays - Associative and multidimensional arrays for MediaWiki.
 * Copyright (C) 2019 Marijn van Wezel
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

/**
 * Class ComplexArraySibling
 *
 * Defines the parser function {{#complexarraysibling:}}, which returns the key of the sibling with a given key of the given key.
 *
 * @extends WSArrays
 */
class ComplexArraySibling extends ResultPrinter {
    public function getName() {
        return 'complexarraysibling';
    }

    public function getAliases() {
        return [
            'casibling'
        ];
    }

    public function getType() {
        return 'normal';
    }

    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $key
     * @return string|array
     */
    public static function getResult(Parser $parser, $key = null, $sibling_key = null ) {
        if ( empty( $key ) ) {
            return GlobalFunctions::error( wfMessage( 'ca-omitted', 'Key' ) );
        }

        if ( empty( $sibling_key ) ) {
            return GlobalFunctions::error( wfMessage( 'ca-omitted', 'Sibling key' ) );
        }

        return ComplexArraySibling::arraySibling( $key, $sibling_key );
    }

    /**
     * @param string $key
     * @param string $sibling_key
     *
     * @return string
     */
    private static function arraySibling( $key, $sibling_key ) {
        $regex = '/\[[^\[\]]+\]$/m';
        $parent = preg_replace($regex, '', $key);

        // Check if this array is a base array
        if ( $parent === $key ) {
            return null;
        }

        return $parent . "[$sibling_key]";
    }
}