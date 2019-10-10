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
 * Class ComplexArrayDefine
 *
 * Defines the parser function {{#complexarraydefine:}}, which allows users to define a new array.
 *
 * @extends WSArrays
 */
class ComplexArrayDefine extends ResultPrinter {
    public function getName() {
        return 'complexarraydefine';
    }

    public function getAliases() {
        return [
            'cadefine'
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
     *
     * @throws Exception
     *
     * @return null
     */
    public static function getResult( Parser $parser, $frame, $args ) {
        GlobalFunctions::fetchSemanticArrays();

        // Name
        if ( !isset( $args[ 0 ] ) || empty( $args[ 0 ] ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error( $ca_omitted );
        } else {
            $name = GlobalFunctions::getValue( $args[ 0 ], $frame );
        }

        $noparse = GlobalFunctions::getValue( $args[ 3 ], $frame );
        $wson = GlobalFunctions::getValue( $args[ 1 ], $frame, $parser, $noparse );
        $sep = GlobalFunctions::getValue( $args[ 2 ], $frame );

        if ( !GlobalFunctions::isValidArrayName( $name ) ) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error( $ca_invalid_name );
        }

        // Define an empty array
        if ( empty( $wson ) ) {
            WSArrays::$arrays[ $name ] = new SafeComplexArray();

            return null;
        }

        return ComplexArrayDefine::arrayDefine( $name, $wson, $sep );
    }

    /**
     * Define array and store it in WSArrays::$arrays as a SafeComplexArray object.
     *
     * @param string $name
     * @param string $wson
     * @param string $sep
     * @return array|null
     * @throws Exception
     */
    private static function arrayDefine( $name, $wson, $sep = null ) {
        // Convert the WSON to an array
        $array = GlobalFunctions::WSONtoArray( $wson );

        // If it's not WSON, assume it is a simple array
        if ( !$array ) {
            ComplexArrayDefine::defineSimpleArray( $name, $wson, $sep );
        } else {
            WSArrays::$arrays[$name] = new SafeComplexArray( $array );
        }

        return null;
    }

    private static function defineSimpleArray( $name, $wson, $sep = null ) {
        if ( !$sep ) {
            $sep = ",";
        }

        $array = array_map('trim', explode( $sep, $wson ) );

        WSArrays::$arrays[$name] = new SafeComplexArray( $array );
    }
}