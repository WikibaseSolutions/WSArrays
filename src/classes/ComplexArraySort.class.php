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
 * Class ComplexArraySort
 *
 * Defines the parser function {{#complexarraysort:}}, which allows users to sort arrays.
 *
 * @extends WSArrays
 */
class ComplexArraySort extends ResultPrinter {
    public function getName() {
        return 'complexarraysort';
    }

    public function getAliases() {
        return [
            'casort'
        ];
    }

    public function getType() {
        return 'normal';
    }

    /**
     * @var
     */
    private static $key;

    /**
     * @var
     */
    private static $array_name;

    /**
     * @var
     */
    private static $array;

    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $name
     * @param string $options
     * @param string $key
     * @return array|null
     *
     * @throws Exception
     */
    public static function getResult( Parser $parser, $array_name = '', $options = '', $key = '' ) {
        GlobalFunctions::fetchSemanticArrays();

        if ( empty( $array_name ) ) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error( $ca_omitted );
        }

        return ComplexArraySort::arraySort( $array_name, $options, $key );
    }

    /**
     * @param string $array_name
     * @param string $options
     * @param string $key
     * @return array|null
     *
     * @throws Exception
     */
    private static function arraySort( $array_name, $options = '', $key = '' ) {
        if ( !GlobalFunctions::arrayExists( $array_name ) ) {
            return null;
        }

        ComplexArraySort::$array_name = $array_name;
        ComplexArraySort::$array = GlobalFunctions::getUnsafeArrayFromSafeComplexArray( WSArrays::$arrays[ $array_name ] );

        if ( !empty( $key ) ) {
            ComplexArraySort::$key = $key;
        }

        if ( empty( $options ) ) {
            $result = ComplexArraySort::sortArray( "sort" );
        } else {
            GlobalFunctions::serializeOptions( $options );

            if ( count( $options ) === 1 ) {
                $result = ComplexArraySort::sortArray( $options[ 0 ] );
            } else {
                if ( $options[ 0 ] !== "keysort" ) {
                    $result = ComplexArraySort::sortArray( $options[ 0 ] );
                } else {
                    $result = ComplexArraySort::keysort( $options[ 1 ] );
                }
            }
        }

        if( $result === true ) {
            WSArrays::$arrays[ $array_name ] = new SafeComplexArray( ComplexArraySort::$array );

            return null;
        } else {
            return GlobalFunctions::error( $result );
        }
    }

    /**
     * @param $algo
     * @return bool|string
     */
    private static function sortArray( $algo ) {
        switch ( $algo ) {
            case 'multisort':
                $array = ComplexArraySort::multisort();
                break;
            case 'asort':
                $array = ComplexArraySort::asort();
                break;
            case 'arsort':
                $array = ComplexArraySort::arsort();
                break;
            case 'krsort':
                $array = ComplexArraySort::krsort();
                break;
            case 'natcasesort':
                $array = ComplexArraySort::natcasesort();
                break;
            case 'natsort':
                $array = ComplexArraySort::natsort();
                break;
            case 'rsort':
                $array = ComplexArraySort::rsort();
                break;
            case 'shuffle':
                $array = ComplexArraySort::shuffle();
                break;
            case 'keysort':
                $array = ComplexArraySort::keysort(null);
                break;
            case 'sort':
            default:
                $array = ComplexArraySort::sort();
                break;
        }

        return $array;
    }

    /**
     * Sort array using multisort
     *
     * @return bool|string
     */
    private static function multisort() {
        if ( !array_multisort( ComplexArraySort::$array ) ) {
            $ca_sort_broken = wfMessage( 'ca-sort-broken', 'multisort' );

            return $ca_sort_broken;
        }

        return true;
    }

    /**
     * Sort array using asort
     *
     * @return bool|string
     */
    private static function asort() {
        if ( !asort( ComplexArraySort::$array ) ) {
            $ca_sort_broken = wfMessage( 'ca-sort-broken', 'asort' );

            return $ca_sort_broken;
        }

        return true;
    }

    /**
     * Sort array using arsort
     *
     * @return bool|string
     */
    private static function arsort() {
        if ( !arsort( ComplexArraySort::$array ) ) {
            $ca_sort_broken = wfMessage( 'ca-sort-broken', 'arsort' );

            return $ca_sort_broken;
        }

        return true;
    }

    /**
     * Sort array using krsort
     *
     * @return bool|string
     */
    private static function krsort() {
        if ( !krsort( ComplexArraySort::$array ) ) {
            $ca_sort_broken = wfMessage( 'ca-sort-broken', 'krsort' );

            return $ca_sort_broken;
        }

        return true;
    }

    /**
     * Sort array using natcasesort
     *
     * @return bool|string
     */
    private static function natcasesort() {
        if ( !natcasesort( ComplexArraySort::$array ) ) {
            $ca_sort_broken = wfMessage( 'ca-sort-broken', 'natcasesort' );

            return $ca_sort_broken;
        }

        return true;
    }

    /**
     * Sort array using natsort
     *
     * @return bool|string
     */
    private static function natsort() {
        if ( !natsort( ComplexArraySort::$array ) ) {
            $ca_sort_broken = wfMessage( 'ca-sort-broken', 'natsort' );

            return $ca_sort_broken;
        }

        return true;
    }

    /**
     * Sort array using rsort
     *
     * @return bool|string
     */
    private static function rsort() {
        if ( !rsort( ComplexArraySort::$array ) ) {
            $ca_sort_broken = wfMessage( 'ca-sort-broken', 'rsort' );

            return $ca_sort_broken;
        }

        return true;
    }

    /**
     * Sort array using shuffle
     *
     * @return bool|string
     */
    private static function shuffle() {
        if ( !shuffle( ComplexArraySort::$array ) ) {
            $ca_sort_broken = wfMessage( 'ca-sort-broken', 'shuffle' );

            return $ca_sort_broken;
        }

        return true;
    }

    /**
     * Sort array using sort
     *
     * @return bool|string
     */
    private static function sort() {
        if ( !sort( ComplexArraySort::$array ) ) {
            $ca_sort_broken = wfMessage( 'ca-sort-broken', 'sort' );

            return $ca_sort_broken;
        }

        return true;
    }

    /**
     * Sort array using keysort
     *
     * @param $order
     *
     * @return bool|string
     */
    private static function keysort( $order ) {
        if ( !ComplexArraySort::$key ) {
            $ca_sort_missing_key = wfMessage( 'ca-sort-missing-key' );

            return $ca_sort_missing_key;
        }

        foreach ( ComplexArraySort::$array as $value ) {
            if ( is_array( $value[ ComplexArraySort::$key ] ) ) {
                $ca_sort_array_too_deep = wfMessage( 'ca-sort-array-too-deep' );

                return $ca_sort_array_too_deep;
            }
        }

        ComplexArraySort::ksort( ComplexArraySort::$array, ComplexArraySort::$key );

        $i = 0;
        $temp = [];
        foreach ( ComplexArraySort::$array as $key => $item ) {
            $temp[ $i ] = $item;
            $i++;
        }

        ComplexArraySort::$array = $temp;

        if ( $order == "desc" ) {
            ComplexArraySort::$array = array_reverse( ComplexArraySort::$array );
        }

        WSArrays::$arrays[ ComplexArraySort::$array_name ] = new SafeComplexArray( ComplexArraySort::$array );

        return true;
    }

    /**
     * User-defined sorting function which sorts based on key.
     *
     * @param $array
     * @param $key
     * @return bool|string
     */
    private static function ksort( &$array, $key ) {
        $sorter = array();
        $ret = array();

        reset( $array );

        foreach ( $array as $ii => $va ) {
            $sorter[ $ii ] = $va[ $key ];
        }

        asort( $sorter );

        foreach ( $sorter as $ii => $va ) {
            $ret[ $ii ] = $array[ $ii ];
        }

        $array = $ret;
    }
}