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
 * Class ComplexArraySlice
 *
 * Defines the parser function {{#complexarrayslice:}}, which allows users to slice an array.
 *
 * @extends WSArrays
 */
class ComplexArraySlice extends ResultPrinter {
    public function getName() {
        return 'complexarrayslice';
    }

    public function getAliases() {
        return [
            'caslice'
        ];
    }

    public function getType() {
        return 'normal';
    }

    /**
     * @param Parser $parser
     * @param string $new_array
     * @param string $array
     * @param string $offset
     * @param string $length
     * @return array|null
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $new_array = '', $array = '', $offset = '', $length = '') {
        GlobalFunctions::fetchSemanticArrays();

        if ( empty( $new_array ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'New array' );

            return GlobalFunctions::error( $ca_omitted );
        }

        if ( !GlobalFunctions::isValidArrayName( $new_array ) ) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error( $ca_invalid_name );
        }

        if ( empty( $array ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error( $ca_omitted );
        }

        return ComplexArraySlice::arraySlice( $new_array, $array, $offset, $length );
    }

    /**
     * @param $new_array
     * @param $array
     * @param integer $offset
     * @param integer $length
     * @return array|null
     *
     * @throws Exception
     */
    private static function arraySlice( $new_array, $array, $offset = 0, $length = 0 ) {
        $array = GlobalFunctions::getArrayFromArrayName( $array, true );

        if ( !$array ) {
            return null;
        }

        if ( !empty( $length ) ) {
            WSArrays::$arrays[ $new_array ] = new SafeComplexArray( array_slice( $array, $offset, $length ) );
        } else {
            WSArrays::$arrays[ $new_array ] = new SafeComplexArray( array_slice( $array, $offset ) );
        }

        return null;
    }
}