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
 * Class ComplexArrayPrint
 *
 * Defines the parser function {{#complexarrayprint:}}, which allows users to display an array in a couple of ways.
 *
 * @extends WSArrays
 */
class ComplexArrayPrint extends ResultPrinter {
    public function getName() {
        return 'complexarrayprint';
    }

    public function getAliases() {
        return [
            'caprint'
        ];
    }

    public function getType() {
        return 'sfh';
    }

    /**
     * Holds the array being worked on.
     *
     * @var array
     */
    protected static $array = [];

    /**
     * @var null
     */
    private static $name = null;

    /**
     * @var null
     */
    private static $map = null;

    /**
     * @var string
     */
    private static $subject = "@@";

    /**
     * @var string
     */
    private static $indent_char = "*";

    /**
     * Define all allowed parameters. This parser is hooked with Parser::SFH_OBJECT_ARGS.
     *
     * @param Parser $parser
     * @param Object $frame
     * @param Object $args
     * @return array|mixed|null|string|string[]
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $frame, $args ) {
        GlobalFunctions::fetchSemanticArrays();

        if ( isset( $args[ 0 ] ) ) {
            $name = trim( $frame->expand( $args[ 0 ] ) );
        } else {
            $name = '';
        }

        if ( isset( $args[ 1 ] ) ) {
            $options = trim( $frame->expand( $args[ 1 ] ) );
        } else {
            $options = '';
        }

        if ( isset( $args[ 2 ] ) ) {
            $map = trim( $frame->expand( $args[ 2 ] ) );
        } else {
            $map = '';
        }

        if ( isset( $args[ 3 ] ) ) {
            $subject = trim( $frame->expand( $args[ 3 ], PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) );
        } else {
            $subject = '';
        }

        if ( empty( $name ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error( $ca_omitted );
        }

        return ComplexArrayPrint::arrayPrint( $name, $options, $map, $subject );
    }

    /**
     * @param $name
     * @param string $options
     * @param string $map
     * @param string $subject
     * @return array|mixed|null|string|string[]
     *
     * @throws Exception
     */
    private static function arrayPrint( $name, $options = '', $map = '', $subject = '' ) {
        ComplexArrayPrint::$name  = $name;
        ComplexArrayPrint::$map   = $map;

        ComplexArrayPrint::$array = GlobalFunctions::getArrayFromArrayName( $name );

        if ( !ComplexArrayPrint::$array ) {
            // Array does not exist
            return null;
        }

        if ( $subject ) {
            // If there is a subject set, store it
            ComplexArrayPrint::$subject = $subject;
        }

        if ( !empty( $options ) ) {
            GlobalFunctions::serializeOptions( $options );
            $result = ComplexArrayPrint::applyOptions( $options );
        } else {
            $result = ComplexArrayPrint::createList();
        }

        return $result;
    }

    /**
     * @param $options
     * @return array|mixed|null|string|string[]
     */
    private static function applyOptions( $options ) {
        if ( gettype( $options ) === "array" ) {
            $options = $options[ 0 ];
        }

        switch ( $options ) {
            case 'map':
                return ComplexArrayPrint::applyMapping();
                break;
            case 'markup':
            case 'wson':
                return ComplexArrayPrint::arrayToWSON( ComplexArrayPrint::$array );
                break;
            default:
                return ComplexArrayPrint::createList( $options );
                break;
        }
    }

    /**
     * @return array|mixed|null|string
     */
    private static function applyMapping() {
        if ( !ComplexArrayPrint::$map ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Mapping' );

            return GlobalFunctions::error( $ca_omitted );
        }

        if ( GlobalFunctions::containsArray( ComplexArrayPrint::$array ) ) {
            $ca_map_multidimensional = wfMessage( 'ca-map-multidimensional' );

            return GlobalFunctions::error( $ca_map_multidimensional );
        }

        if ( count( ComplexArrayPrint::$array ) === 1 ) {
            return ComplexArrayPrint::mapValue( ComplexArrayPrint::$array[ 0 ] );
        }

        $result = null;
        foreach ( ComplexArrayPrint::$array as $value ) {
            $result .= ComplexArrayPrint::mapValue( $value );
        }

        return $result;
    }

    /**
     * @param $value
     * @return mixed
     */
    private static function mapValue( $value ) {
        if ( is_array( $value ) ) return null;

        return str_replace( ComplexArrayPrint::$subject, $value, ComplexArrayPrint::$map );
    }

    /**
     * Create an (un)ordered list from an array.
     *
     * @param string $type
     * @return array|null|string
     */
    private static function createList( $type = "unordered" ) {
        if ( count( ComplexArrayPrint::$array ) === 1 && !GlobalFunctions::containsArray( ComplexArrayPrint::$array ) ) {
            if ( is_array( ComplexArrayPrint::$array ) ) {
                $last_el = reset( ComplexArrayPrint::$array );
                $return  = key( ComplexArrayPrint::$array ) . ": " . $last_el;

                return array( $return, 'noparse' => false, 'isHTML' => false );
            } else {
                return array( ComplexArrayPrint::$array, 'noparse' => false, 'isHTML' => false );
            }
        }

        if ( $type == "ordered" ) {
            ComplexArrayPrint::$indent_char = "#";
        }

        $indent_char = ComplexArrayPrint::$indent_char;

        $result = null;
        foreach ( ComplexArrayPrint::$array as $key => $value ) {
            if ( !is_array( $value ) ) {
                if ( !is_numeric( $key ) ) {
                    $result .= "$indent_char $key: $value\n";
                } else {
                    $result .= "$indent_char $value\n";
                }
            } else {
                $result .= "$indent_char ".strval( $key )."\n";

                ComplexArrayPrint::addArrayToList( $value, $result );
            }
        }

        return $result;
    }

    /**
     * @param $array
     * @param $result
     * @param int $depth
     */
    private static function addArrayToList( $array, &$result, $depth = 0 ) {
        $depth++;

        $indent_char = ComplexArrayPrint::$indent_char;

        foreach ( $array as $key => $value ) {
            $indent = str_repeat( "$indent_char", $depth + 1 );

            if ( is_array( $value ) ) {
                $result .= "$indent ".strval( $key )."\n";
                ComplexArrayPrint::addArrayToUnorderedList( $value, $result, $depth );
            } else {
                if ( !is_numeric( $key ) ) {
                    $result .= "$indent $key: $value\n";
                } else {
                    $result .= "$indent $value\n";
                }
            }
        }
    }

    /**
     * @param $array
     * @param $result
     * @param int $depth
     */
    private static function addArrayToUnorderedList( $array, &$result, $depth = 0 ) {
        $depth++;
        $indent_char = ComplexArrayPrint::$indent_char;
        foreach( $array as $key => $value ) {
            $indent = str_repeat( "$indent_char", $depth + 1 );
            if( is_array( $value ) ) {
                $result .= "$indent ".strval( $key )."\n";
                self::addArrayToUnorderedList( $value, $result, $depth );
            } else {
                if( !is_numeric( $key ) ) {
                    $result .= "$indent $key: $value\n";
                } else {
                    $result .= "$indent $value\n";
                }
            }
        }
    }
}