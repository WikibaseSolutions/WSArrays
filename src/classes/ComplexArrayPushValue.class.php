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
 * Class ComplexArrayPushValue
 *
 * Defines the parser function {{#complexarraypushvalue:}}, which allows users to push a value or subarray to the end of a (sub)array.
 *
 * @extends WSArrays
 */
class ComplexArrayPushValue extends ResultPrinter {
    public function getName() {
        return 'complexarraypushvalue';
    }

    public function getAliases() {
        return [
            'complexarraypush',
            'capush'
        ];
    }

    public function getType() {
        return 'normal';
    }

    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $array
     * @param string $value
     * @return array|bool|null
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $array = '', $value = '' ) {
        GlobalFunctions::fetchSemanticArrays();

        if ( empty( $array ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error( $ca_omitted );
        }

        if ( empty( $value ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Value' );

            return GlobalFunctions::error( $ca_omitted );
        }

        return ComplexArrayPushValue::arrayPushValue( $array, $value );
    }

    /**
     * @param $array
     * @param $value
     * @return array|bool|null
     *
     * @throws Exception
     */
    private static function arrayPushValue( $array, $value ) {
        $base_array = ComplexArrayPushValue::calculateBaseArray( $array );

        // If the array doesn't exist yet, create it
        if ( !isset( WSArrays::$arrays[ $base_array ] ) ) {
            if ( !GlobalFunctions::isValidArrayName( $base_array ) ) {
                $ca_invalid_name = wfMessage( 'ca-invalid-name' );

                return GlobalFunctions::error( $ca_invalid_name );
            }

            WSArrays::$arrays[ $base_array ] = new SafeComplexArray();
        }

        $wsarray = GlobalFunctions::getArrayFromSafeComplexArray( WSArrays::$arrays[ $base_array ] );

        if ( !strpos( $array, "[" ) ) {
            GlobalFunctions::WSONtoJSON( $value );

            if ( GlobalFunctions::isValidJSON( $value ) ) {
                $value = json_decode( $value, true );
            }

            array_push( $wsarray, $value );

            WSArrays::$arrays[ $base_array ] = new SafeComplexArray( $wsarray );

            return null;
        }

        if ( preg_match_all( "/(?<=\[).+?(?=\])/", $array, $matches ) === 0 ) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error( $ca_invalid_name );
        }

        $result = ComplexArrayPushValue::add( $matches[0], $wsarray, $value );

        if ( $result !== true ) {
            return $result;
        }

        WSArrays::$arrays[ $base_array ] = new SafeComplexArray( $wsarray );

        return null;
    }

    /**
     * Push value to location defined in $path.
     *
     * @param $path
     * @param array $array
     * @param null $value
     * @return array|bool
     */
    private static function add( $path, &$array = array(), $value = null ) {
        GlobalFunctions::WSONtoJSON( $value );

        if ( GlobalFunctions::isValidJSON( $value ) ) {
            $value = json_decode( $value, true );
        }

        $temp =& $array;

        $depth = count( $path );

        $current_depth = 1;

        foreach ( $path as $key ) {
            $current_depth++;

            if ( !array_key_exists( $key, $temp ) ) {
                $ca_nonexistent_subarray = wfMessage( 'ca-nonexistent-subarray' );

                return GlobalFunctions::error( $ca_nonexistent_subarray );
            }

            if( $current_depth !== $depth ) {
                $temp =& $temp[ $key ];
            }
        }

        array_push( $temp, $value );

        return true;
    }
}