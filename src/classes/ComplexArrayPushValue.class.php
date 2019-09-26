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
        return 'sfh';
    }

    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $frame
     * @param string $args
     * @return array|bool|null
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $frame, $args ) {
        GlobalFunctions::fetchSemanticArrays();

        if ( !isset( $args[ 0 ] ) || empty( $args[ 0 ] ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error( $ca_omitted );
        }

        if ( !isset( $args[ 1 ] ) || empty( $args[ 1 ] ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Value' );

            return GlobalFunctions::error( $ca_omitted );
        }

        $noparse = GlobalFunctions::getValue( $args[ 2 ], $frame );
        $array_name = GlobalFunctions::getValue( $args[ 0 ], $frame );
        $value = GlobalFunctions::getValue( $args[ 1 ], $frame, $parser, $noparse );

        return ComplexArrayPushValue::arrayPushValue( $array_name, $value );
    }

    /**
     * @param $array_name
     * @param $value
     * @return array|bool|null
     *
     * @throws Exception
     */
    private static function arrayPushValue( $array_name, $value ) {
        $base_array = ComplexArrayPushValue::calculateBaseArray( $array_name );

        // If the array doesn't exist yet, create it
        if ( !GlobalFunctions::arrayExists( $array_name ) ) {
            if ( !GlobalFunctions::isValidArrayName( $base_array ) ) {
                $ca_invalid_name = wfMessage( 'ca-invalid-name' );

                return GlobalFunctions::error( $ca_invalid_name );
            }

            WSArrays::$arrays[ $base_array ] = new SafeComplexArray();
        }
        /*
        if ( preg_match_all( "/(?<=\[).+?(?=\])/", $array_name, $matches ) === 0 ) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error( $ca_invalid_name );
        }
        */
        global $wfEscapeEntitiesInArrays;
        if ( $wfEscapeEntitiesInArrays === true ) {
            $array = GlobalFunctions::getArrayFromSafeComplexArray( WSArrays::$arrays[ $base_array ] );
        } else {
            $array = GlobalFunctions::getUnsafeArrayFromSafeComplexArray( WSArrays::$arrays[ $base_array ] );
        }

        GlobalFunctions::toArrayIfValid( $value );

        if ( !strpos( $array_name, "[" ) ) {
            ComplexArrayPushValue::replace( $value, $array, $base_array );
        } else {
            $result = ComplexArrayPushValue::add( $matches[0], $array, $value );

            if ( $result !== true ) {
                return $result;
            }

            WSArrays::$arrays[ $base_array ] = new SafeComplexArray( $array );
        }

        return null;
    }

    private static function replace( $value, $array, $base_array ) {
        array_push( $array, $value );

        WSArrays::$arrays[ $base_array ] = new SafeComplexArray( $array );
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