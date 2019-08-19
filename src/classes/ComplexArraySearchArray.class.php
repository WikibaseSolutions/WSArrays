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
 * Defines the parser function {{#complexarraysearcharray:}}, which allows users to search for a string in the array, and define an array with all the keys of the result.
 *
 * @extends WSArrays
 */
class ComplexArraySearchArray extends ResultPrinter {
    public function getName() {
        return 'complexarraysearcharray';
    }

    public function getAliases() {
        return [
            'casearcharray',
            'casearcha'
        ];
    }

    public function getType() {
        return 'normal';
    }

    private static $found = [];

    /**
     * @param Parser $parser
     * @param string $new_array
     * @param string $name
     * @param string $value
     * @return array
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $new_array = '', $name = '', $value = '' )
    {
        GlobalFunctions::fetchSemanticArrays();

        if ( empty( $new_array ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'New array' );

            return GlobalFunctions::error( $ca_omitted );
        }

        if ( !GlobalFunctions::isValidArrayName( $new_array ) ) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error($ca_invalid_name);
        }

        if ( empty( $name ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error( $ca_omitted );
        }

        if ( empty( $value ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Value' );

            return GlobalFunctions::error( $ca_omitted );
        }

        return ComplexArraySearchArray::arraySearchArray( $new_array, $name, $value );
    }

    /**
     * @param $name
     * @param $value
     * @param $new_array
     * @return array|int
     *
     * @throws Exception
     */
    private static function arraySearchArray( $new_array, $name, $value ) {
        if ( !WSArrays::$arrays[ $name ] ) {
            return null;
        }

        ComplexArraySearchArray::findValues( $value, $name );

        if ( count( ComplexArraySearchArray::$found ) > 0 ) {
            WSArrays::$arrays[ $new_array ] = new SafeComplexArray( ComplexArraySearchArray::$found );
        }

        return null;
    }

    /**
     * @param $value
     * @param $key
     *
     * @throws Exception
     */
    private static function findValues( $value, $key ) {
        $array = GlobalFunctions::getArrayFromArrayName( $key, true );

        ComplexArraySearchArray::i( $array, $value, $key );
    }

    /**
     * @param $array
     * @param $value
     * @param $key
     */
    private static function i( $array, $value, &$key ) {
        foreach ( $array as $current_key => $current_item ) {
            $key .= "[$current_key]";

            if ( $value == $current_item ) {
                array_push( ComplexArraySearchArray::$found, $key );
            } else {
                if ( is_array( $current_item ) ) {
                    ComplexArraySearchArray::i( $current_item, $value, $key );
                }
            }

            $key = substr( $key, 0, strrpos( $key, '[' ) );
        }
    }
}