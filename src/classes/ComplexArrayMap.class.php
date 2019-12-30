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
 * Class ComplexArrayMap
 *
 * Defines the parser function {{#complexarraymap:}}, which allows users to iterate over (sub)arrays.
 *
 * @extends WSArrays
 */
class ComplexArrayMap extends ResultPrinter {
    public function getName() {
        return 'complexarraymap';
    }

    public function getAliases() {
        return [
            'camap'
        ];
    }

    public function getType() {
        return 'sfh';
    }

    /**
     * Buffer containing items to be returned.
     *
     * @var string
     */
    private static $buffer = '';

    /**
     * Variable containing the name of the array that needs to be mapped.
     *
     * @var string
     */
    private static $array = '';

    /**
     * Dynamic variable containing the key currently being worked on.
     *
     * @var string
     */
    private static $array_key = '';

    /**
     * @var bool
     */
    private static $hide = false;

    /**
     * @var string
     */
    private static $sep = "";

    /**
     * Define parameters and initialize parser. This parser is hooked with Parser::SFH_OBJECT_ARGS.
     *
     * @param Parser $parser
     * @param string $frame
     * @param string $args
     * @return array|null
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $frame, $args ) {
        GlobalFunctions::fetchSemanticArrays();

        // Name
        if ( !isset( $args[0] ) ) {
            return GlobalFunctions::error( wfMessage( 'ca-omitted', 'Name' ) );
        }

        // Map key
        if ( !isset( $args[1] ) ) {
            return GlobalFunctions::error( wfMessage( 'ca-omitted', 'Map key' ) );
        }

        // Map
        if ( !isset( $args[2] ) ) {
            return GlobalFunctions::error( wfMessage( 'ca-omitted', 'Map' ) );
        }

        // Hide
        if ( isset( $args[4] ) ) {
            if ( GlobalFunctions::getValue( $args[4], $frame ) === "true" ) {
                ComplexArrayMap::$hide = true;
            }
        }

        if ( isset( $args[3] ) ) {
            $sep = GlobalFunctions::getValue( $args[3], $frame );

            if($sep === '\n') {
                $sep = "\r\n";
            }

            ComplexArrayMap::$sep = $sep;
        }

        $name = GlobalFunctions::getValue( @$args[0], $frame );
        $map_key = GlobalFunctions::getValue( @$args[1], $frame );
        $map = GlobalFunctions::getValue( @$args[2], $frame, $parser, 'NO_IGNORE,NO_ARGS,NO_TAGS,NO_TEMPLATES' );

        return array( ComplexArrayMap::arrayMap( $name, $map_key, $map ), 'noparse' => false );
    }

    /**
     * @param $array_name
     * @param $map_key
     * @param $map
     * @return array|string
     *
     * @throws Exception
     */
    private static function arrayMap( $array_name, $map_key, $map ) {
        ComplexArrayMap::$buffer = '';

        $base_array = GlobalFunctions::getBaseArrayFromArrayName( $array_name );
        $array = GlobalFunctions::getArrayFromArrayName( $array_name );

        if ( !isset( WSArrays::$arrays[$base_array] ) || !$array ) {
            return null;
        }

        return ComplexArrayMap::iterate( $array, $map_key, $map, $array_name );
    }

    /**
     * @param $array
     * @param $map_key
     * @param $map
     * @param $array_name
     * @return string
     *
     * @throws Exception
     */
    private static function iterate( $array, $map_key, $map, $array_name ) {
        ComplexArrayMap::$array = $array_name;

        $buffer = [];
        foreach ( $array as $array_key => $subarray ) {
            ComplexArrayMap::$array_key = $array_key;

            $type = gettype( $subarray );

            if ( $type !== "array" ) {
                switch( $type ) {
                    case 'string':
                    case 'integer':
                    case 'float':
                        $buffer[] = str_replace( $map_key, $subarray, $map );
                }
            } else {
                $preg_quote = preg_quote( $map_key );

                $buffer[] = preg_replace_callback( "/($preg_quote(\[[^\[\]]+\])+)/", 'ComplexArrayMap::replaceCallback', $map );
            }
        }

        $buffer = ComplexArrayMap::$sep ? implode($buffer, ComplexArrayMap::$sep) : implode($buffer);

        return $buffer;
    }

    /**
     * @param $matches
     * @return array|bool
     *
     * @throws Exception
     */
    public static function replaceCallback( $matches ) {
        $value = ComplexArrayMap::getValueFromMatch( $matches[0] );

        switch( gettype( $value ) ) {
            case 'integer':
            case 'float':
            case 'string':
                return $value;
                break;
            default:
                if( !ComplexArrayMap::$hide ) {
                    return $matches[ 0 ];
                }

                break;
        }

        return null;
    }

    /**
     * @param $match
     * @return array|bool
     *
     * @throws Exception
     */
    private static function getValueFromMatch( $match ) {
        $pointer = ComplexArrayMap::getPointerFromArrayName( $match );
        $array_name = ComplexArrayMap::getArrayNameFromPointer( $pointer );
        $value = GlobalFunctions::getArrayFromArrayName( $array_name );

        return $value;
    }

    /**
     * @param $pointer
     * @return string
     */
    private static function getArrayNameFromPointer( $pointer ) {
        return ComplexArrayMap::$array . '[' . ComplexArrayMap::$array_key . ']' . $pointer;
    }

    /**
     * @param $array_key
     * @return null|string|string[]
     */
    private static function getPointerFromArrayName( $array_key ) {
        return preg_replace( "/[^\[]*/", "", $array_key, 1 );
    }
}