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
 * Class ComplexArrayExtract
 *
 * Defines the parser function {{#complexarrayextract:}}, which allows users to create a new array from a subarray.
 *
 * @extends WSArrays
 */
class ComplexArrayExtract extends ResultPrinter {
    public function getName() {
        return 'complexarrayextract';
    }

    public function getAliases() {
        return [
            'caextract'
        ];
    }

    public function getType() {
        return 'normal';
    }

    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $name
     * @param string $subarray
     * @return array|bool
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $name = '', $subarray = '' ) {
        if ( !$name ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'New array' );

            return GlobalFunctions::error( $ca_omitted );
        }

        if ( !GlobalFunctions::isValidArrayName( $name ) ) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error( $ca_invalid_name );
        }

        if ( !$subarray ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Subarray' );

            return GlobalFunctions::error( $ca_omitted );
        }

        return ComplexArrayExtract::arrayExtract( $name, $subarray );
    }

    /**
     * @param $name
     * @param $subarray
     * @return array|bool
     *
     * @throws Exception
     */
    private static function arrayExtract( $name, $subarray ) {
        // If no subarray is provided, show an error.
        if( !strpos( $subarray, "[" ) ||
            !strpos( $subarray, "]" ) ) {
            $ca_subarray_not_provided = wfMessage( 'ca-subarray-not-provided' );

            return GlobalFunctions::error( $ca_subarray_not_provided );
        }

        $array = GlobalFunctions::getArrayFromArrayName( $subarray, true );

        if( !$array ) {
            return null;
        }

        // If the array is a single value, convert it back to a single value array.
        if( is_string( $array ) ) {
            $array = [$array];
        }

        WSArrays::$arrays[ $name ] = new SafeComplexArray( $array );

        return null;
    }
}