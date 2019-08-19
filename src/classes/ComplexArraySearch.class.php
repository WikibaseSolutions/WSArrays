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
 * Class ComplexArraySearch
 *
 * Defines the parser function {{#complexarraysearch:}}, which allows users to get search in an array.
 *
 * @extends WSArrays
 */
class ComplexArraySearch extends ResultPrinter {
    public function getName() {
        return 'complexarraysearch';
    }

    public function getAliases() {
        return [
          'casearch'
        ];
    }

    public function getType() {
        return 'normal';
    }

    /**
     * @var int
     */
    private static $found = 0;

    /**
     * @var int
     */
    private static $key = 0;

    /**
     * @param Parser $parser
     * @param string $name
     * @param string $value
     * @return array
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $name = '', $value = '' ) {
        GlobalFunctions::fetchSemanticArrays();

        if ( empty( $name ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error( $ca_omitted );
        }

        if ( empty( $value ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Value' );

            return GlobalFunctions::error( $ca_omitted );
        }

        return ComplexArraySearch::arraySearch( $name, $value );
    }

    /**
     * @param $name
     * @param $value
     * @return array|int
     *
     * @throws Exception
     */
    private static function arraySearch( $name, $value ) {
        if ( !WSArrays::$arrays[ $name ] ) {
            return null;
        }

        ComplexArraySearch::findValue( $value, $name );

        return htmlspecialchars(ComplexArraySearch::$key);
    }

    /**
     * @param $value
     * @param $key
     *
     * @throws Exception
     */
    private static function findValue( $value, $key ) {
        $array = GlobalFunctions::getArrayFromArrayName( $key, true );

        ComplexArraySearch::i( $array, $value, $key );
    }

    /**
     * @param $array
     * @param $value
     * @param $key
     */
    private static function i( $array, $value, &$key ) {
        if ( ComplexArraySearch::$found === 1 ) {
            return;
        }

        foreach( $array as $current_key => $current_item ) {
            $key .= "[$current_key]";

            if( $value == $current_item ) {
                ComplexArraySearch::$key = $key;

                ComplexArraySearch::$found = 1;
            } else {
                if( is_array( $current_item ) ) {
                    ComplexArraySearch::i( $current_item, $value, $key );
                }

                $key = substr( $key, 0, strrpos( $key, '[' ) );
            }
        }
    }
}