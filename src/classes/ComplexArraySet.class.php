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
 * Class ComplexArraySet
 *
 * Unsets a value from an existing array.
 *
 * @extends WSArrays
 */
class ComplexArraySet extends ResultPrinter {
    public function getName() {
        return 'complexarrayset';
    }

    public function getAliases() {
        return [
            'caset'
        ];
    }

    public function getType() {
        return 'sfh';
    }

    /**
     * Define parameters and initialize parser.
     *
     * @param Parser $parser
     * @param string $array_name
     * @return array|null|bool
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $frame, $args ) {
        GlobalFunctions::fetchSemanticArrays();

        $array_name = GlobalFunctions::getValue(
            @$args[0],
            $frame
        );

        $value = GlobalFunctions::getValue(
            @$args[1],
            $frame,
            $parser,
            GlobalFunctions::getValue(
                @$args[2],
                $frame
            )
        );

        if( !$array_name || !$value ) {
            return;
        }

        return ComplexArraySet::arraySet( $array_name, $value );
    }

    /**
     * @param $array_name
     * @return null|bool|array
     * @throws Exception
     */
    private static function arraySet( $array_name, $value ) {
        $base_array_name = GlobalFunctions::getBaseArrayFromArrayName( $array_name );

        if ( $base_array_name === $array_name ) {
            // The user is trying to set the entire array, which is not supported.
            return false;
        }

        if ( !GlobalFunctions::arrayExists( $base_array_name ) ) {
            return false;
        }

        $array = GlobalFunctions::getArrayFromArrayName( $base_array_name );
        $keys  = GlobalFunctions::getKeys( $array_name );

        if ( !$array  ) {
            return false;
        }

        if ( !$keys ) {
            return false;
        }

        ComplexArraySet::setValueAtKey( $value, $array, $keys );

        WSArrays::$arrays[$base_array_name] = new ComplexArray( $array );

        return null;
    }

    private static function setValueAtKey( $value, &$array, $keys ) {
        $depth = count( $keys ) - 1;

        $temp =& $array;
        for ( $i = 0; $i <= $depth; $i++ ) {
            if ( $i === $depth ) {
                $temp[$keys[$i]] = $value;

                return;
            }

            if(!$temp[$keys[$i]] || !is_array($temp[$keys[$i]])) {
                $temp[$keys[$i]] = [];
            }

            $temp =& $temp[$keys[$i]];
        }
    }
}