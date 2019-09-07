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

require_once ('SafeComplexArray.class.php');

/**
 * Class GlobalFunctions
 *
 * Grandfather class. These functions are available in all other classes.
 */
class GlobalFunctions {
    /**
     * Print an error message.
     *
     * @param string $message
     * @return array
     */
    public static function error( $message ) {
        $params = func_get_args();
        array_shift( $params );

        $msgHtml = Html::rawElement(
            'span',
            array( 'class' => 'error' ),
            wfMessage( $message, $params )->toString()
        );

        return array( $msgHtml, 'noparse' => true, 'isHTML' => false );
    }

    /**
     * Check if the given string $json is valid JSON.
     *
     * @param string $json
     * @return bool
     */
    public static function isValidJSON( $json ) {
        json_decode( $json );

        return ( json_last_error() == JSON_ERROR_NONE );
    }

    /**
     * Convert WSON (custom JSON) to JSON.
     *
     * @param string $wson
     */
    public static function WSONtoJSON( &$wson ) {
        $wson = preg_replace( "/(?!\B\"[^\"]*)\(\((?![^\"]*\"\B)/i", "{", $wson );
        $wson = preg_replace( "/(?!\B\"[^\"]*)\)\)(?![^\"]*\"\B)/i", "}", $wson );
    }

    /**
     * Convert JSON to WSON.
     *
     * @param string $json
     */
    public static function JSONtoWSON( &$json ) {
        $json = preg_replace( "/(?!\B\"[^\"]*){(?![^\"]*\"\B)/i", "((", $json );
        $json = preg_replace( "/(?!\B\"[^\"]*)}(?![^\"]*\"\B)/i", "))", $json );
    }

    /**
     * Convert an array to WSON.
     *
     * @param array $array
     * @return null|string|string[]
     */
    public static function ArrayToWSON( $array ) {
        $json = json_encode( $array );

        $wson = preg_replace( "/(?!\B\"[^\"]*){(?![^\"]*\"\B)/i", "((", $json );
        return preg_replace( "/(?!\B\"[^\"]*)}(?![^\"]*\"\B)/i", "))", $wson );
    }

    /**
     * Convert WSON to an array.
     *
     * @param string $wson
     * @return bool|mixed
     */
    public static function WSONtoArray( $wson ) {
        GlobalFunctions::WSONtoJSON( $wson );

        if ( !GlobalFunctions::isValidJSON( $wson ) ) {
            return false;
        }

        try {
            return json_decode( $wson, true );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create an array from a comma-separated list.
     *
     * @param string $options
     */
    public static function serializeOptions( &$options ) {
        $options = explode( ",", $options );
    }

    /**
     * Check if an array contains a subarray.
     *
     * @param array $array
     * @return bool
     */
    public static function containsArray( $array ) {
        if ( !is_array( $array ) ) {
            return false;
        }

        foreach ( $array as $value ) {
            if ( is_array( $value ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the contents of a subarray based on the name (basearray[subarray][subarray]...).
     *
     * @param string $array_name
     * @param boolean $unsafe
     * @return bool|array
     *
     * @throws Exception
     */
    public static function getArrayFromArrayName( $array_name, $unsafe = false ) {
        global $wgEscapeEntitiesInArrays;
        if($wgEscapeEntitiesInArrays === false) {
            $unsafe = true;
        }

        /* This is already a base array, so just get the array */
        if ( !strpos( $array_name, "[" ) ) {
            if ( isset( WSArrays::$arrays[ $array_name ] ) ) {
                if($unsafe === true) {
                    return GlobalFunctions::getUnsafeArrayFromSafeComplexArray( WSArrays::$arrays[ $array_name ] );
                } else {
                    return GlobalFunctions::getArrayFromSafeComplexArray( WSArrays::$arrays[ $array_name ] );
                }
            }
        } else {
            return GlobalFunctions::getSubarrayFromArrayName( $array_name, $unsafe );
        }

        return false;
    }

    /**
     * Get the subarray from an array name in the form of <base_array>[<sub1>][<sub2>][...]. Used by GlobalFunctions::getArrayFromArrayName().
     *
     * @param string $array_name
     * @param bool $unsafe
     * @return array|bool|mixed
     * @throws Exception
     */
    private static function getSubarrayFromArrayName( $array_name, $unsafe ) {
        /* Get the name of the base array */
        $base_array_name = GlobalFunctions::calculateBaseArray( $array_name );

        if ( !GlobalFunctions::arrayExists( $base_array_name ) ) {
            $ca_undefined_array = wfMessage( 'ca-undefined-array' );

            return GlobalFunctions::error( $ca_undefined_array );
        }

        if ( preg_match_all( "/(?<=\[).+?(?=\])/", $array_name, $matches ) === 0 ) {
            return false;
        }

        if ( $unsafe === true ) {
            $array = GlobalFunctions::getUnsafeArrayFromSafeComplexArray( WSArrays::$arrays[ $base_array_name ] );
        } else {
            $array = GlobalFunctions::getArrayFromSafeComplexArray( WSArrays::$arrays[ $base_array_name ] );
        }

        if ( !is_array( $array ) ) {
            return false;
        }

        $array = GlobalFunctions::getArrayFromMatch( $array, $matches[0] );

        return $array;
    }

    /**
     * @param array $array
     * @param array $matches
     * @return array|bool|mixed
     */
    private static function getArrayFromMatch( array $array, array $matches ) {
        $wairudokado_helper_object = false;

        foreach ($matches as $index => $match) {
            $current_array = $array;

            if ($wairudokado_helper_object === true) {
                $wairudokado_helper_object = false;

                continue;
            }

            /*
             * The Wairudokado (transliterated Japanese for wildcard, tribute to the Scope Resolution Operator in PHP) operator gives users the ability to use wildcards as pointers in an array
             */
            if (GlobalFunctions::isWairudokado($match)) {
                if ( GlobalFunctions::isWairudokado( end( $matches ) ) ) {
                    // The Wairudokado operator does not make sense when it's at the end, so just ignore it
                    return $array;
                }

                if ( GlobalFunctions::isWairudokado( $matches[$index + 1] ) ) {
                    // Skip sequential Wairudokado operators and interpret them as one
                    continue;
                }

                $array = GlobalFunctions::getArrayFromWairudokado( $array, $matches, $index );
                $wairudokado_helper_object = true;
            } else {
                foreach ($array as $key => $value) {
                    if ($key == $match) {
                        $array = $value;
                        continue;
                    }
                }

                if ($current_array === $array) {
                    return false;
                }
            }
        }

        return $array;
    }

    /**
     * @param $array
     * @param $matches
     * @param $index
     * @return array
     */
    private static function getArrayFromWairudokado( $array, $matches, $index ) {
        $helper_array = [];

        foreach ( $array as $item ) {
            if ( !is_array( $item ) ) {
                continue;
            }

            array_push( $helper_array, $item[ $matches[ $index + 1 ] ] );
        }

        return $helper_array;
    }

    /**
     * Check if the user has supplied a wildcard. Used by GlobalFunctions::getSubarrayFromArrayName().
     *
     * @param string $match
     * @return bool
     */
    private static function isWairudokado( $match ) {
        if ( $match === '*' ) {
            return true;
        }

        return false;
    }

    /**
     * Find the max depth of a multidimensional array.
     *
     * @param array $array
     * @param int $depth
     * @return int|mixed
     */
    public static function arrayMaxDepth( $array, $depth = 0 ) {
        $max_sub_depth = 0;
        foreach ( array_filter( $array, 'is_array' ) as $subarray ) {
            $max_sub_depth = max(
                $max_sub_depth,
                GlobalFunctions::arrayMaxDepth($subarray, $depth + 1)
            );
        }

        return $max_sub_depth + $depth;
    }

    /**
     * Fetch any arrays defined by Semantic MediaWiki.
     *
     * Semantic MediaWiki stores all ComplexArrays in the configuration parameter $wfDefinedArraysGlobal. In order to allow access to these array, we need to move them to WSArrays::$arrays.
     *
     * @return void
     */
    public static function fetchSemanticArrays() {
        global $wfDefinedArraysGlobal;
        if ( $wfDefinedArraysGlobal !== null ) {
            WSArrays::$arrays = array_merge( WSArrays::$arrays, $wfDefinedArraysGlobal );
        }
    }

    /**
     * Get the name of the base array from a full name.
     *
     * @param string $array_name
     * @return string
     */
    public static function calculateBaseArray( $array_name ) {
        return strtok( $array_name, "[" );
    }

    /**
     * @param string $array_name
     * @return bool
     */
    public static function isValidArrayName( $array_name ) {
        if ( strpos( $array_name, '[' ) !== false ||
            strpos( $array_name, ']' ) !== false ||
            strpos( $array_name, '{' ) !== false ||
            strpos( $array_name, '}' ) !== false ) {
            return false;
        }

        return true;
    }

    /**
     * @param SafeComplexArray $array
     * @return array
     * @throws Exception
     */
    public static function getArrayFromSafeComplexArray( SafeComplexArray $array ) {
        return $array->getArray();
    }

    /**
     * @param SafeComplexArray $array
     * @return array
     * @throws Exception
     */
    public static function getUnsafeArrayFromSafeComplexArray( SafeComplexArray $array ) {
        return $array->getUnsafeArray();
    }

    /**
     * @param string $array_name
     * @return bool
     */
    public static function arrayExists( $array_name ) {
        if ( isset( WSArrays::$arrays[ $array_name ] ) ) {
            return true;
        }

        return false;
    }
}