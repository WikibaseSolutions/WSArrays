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
 * Class ComplexArrayMerge
 *
 * Defines the parser function {{#complexarraymerge:}}, which allows users to merge multiple arrays.
 *
 * @extends WSArrays
 */
class ComplexArrayMerge extends ResultPrinter {
    public function getName() {
        return 'complexarraymerge';
    }

    public function getAliases() {
        return [
            'camerge'
        ];
    }

    public function getType() {
        return 'normal';
    }

    /**
     * @var string
     */
    private static $new_array = '';

    /**
     * @var string
     */
    private static $last_element = '';

    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @return array|null
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser ) {
        GlobalFunctions::fetchSemanticArrays();

        return ComplexArrayMerge::arrayMerge( func_get_args() );
    }

    /**
     * @param $args
     * @return array|null
     * @throws Exception
     */
    private static function arrayMerge( $args ) {
        ComplexArrayMerge::parseFunctionArguments( $args );

        if ( !GlobalFunctions::isValidArrayName( ComplexArrayMerge::$new_array ) ) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error( $ca_invalid_name );
        }

        if ( count( $args ) < 2 ) {
            $ca_too_little_arrays = wfMessage( 'ca-too-little-arrays' );

            return GlobalFunctions::error( $ca_too_little_arrays );
        }

        $arrays = ComplexArrayMerge::iterate( $args );

        if ( ComplexArrayMerge::$last_element === "recursive" ) {
            WSArrays::$arrays[ ComplexArrayMerge::$new_array ] = new SafeComplexArray( call_user_func_array( 'array_merge_recursive', $arrays ) );
        } else {
            WSArrays::$arrays[ ComplexArrayMerge::$new_array ] = new SafeComplexArray( call_user_func_array( 'array_merge', $arrays ) );
        }

        return null;
    }

    /**
     * @param $args
     */
    private static function parseFunctionArguments( &$args ) {
        ComplexArrayMerge::removeFirstItemFromArray( $args );
        ComplexArrayMerge::getFirstItemFromArray( $args );
        ComplexArrayMerge::removeFirstItemFromArray( $args );
        ComplexArrayMerge::removeLastItemFromArray( $args );

        // If the last element is not "recursive", add it back
        if ( ComplexArrayMerge::$last_element !== "recursive" ) {
            ComplexArrayMerge::addItemToEndOfArray( $args, ComplexArrayMerge::$last_element );
        }
    }

    /**
     * @param $arr
     * @return array
     * @throws Exception
     */
    private static function iterate( $arr ) {
        $arrays = [];
        foreach( $arr as $array ) {
            // Check if the array exists
            if ( !isset( WSArrays::$arrays[ $array ] ) ) {
                continue;
            }

            // Convert the SafeComplexArray object to an actual array
            $safe_array = GlobalFunctions::getArrayFromSafeComplexArray( WSArrays::$arrays[ $array ] );

            array_push( $arrays, $safe_array );
        }

        return $arrays;
    }

    /**
     * @param $array
     */
    private static function removeFirstItemFromArray( &$array ) {
        array_shift( $array );
    }

    /**
     * @param $array
     */
    private static function removeLastItemFromArray( &$array ) {
        ComplexArrayMerge::$last_element = array_pop( $array );
    }

    /**
     * @param $array
     */
    private static function getFirstItemFromArray( &$array ) {
        ComplexArrayMerge::$new_array = reset( $array );
    }

    /**
     * @param $array
     * @param $item
     */
    private static function addItemToEndOfArray( &$array, $item ) {
        array_push( $array, $item );
    }
}