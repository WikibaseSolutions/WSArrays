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
 * Class SafeComplexArray
 *
 * Great-grandfather class. This is the highest class. It defines the object arrays should be stored in. Arrays that are stored in this object, are always escaped and safe.
 */
class SafeComplexArray extends GlobalFunctions {
    /**
     * @var array
     */
    private $safe_array   = array();

    /**
     * @var array
     */
    private $unsafe_array = array();

    /**
     * @param array $array
     */
    public function __construct( array $array = array() ) {
        $this->storeDirtyArray( $array );
        $this->storeCleanArray( $array );
    }

    /**
     * Return the array with escaped characters.
     *
     * @return array
     * @throws Exception
     */
    public function getArray() {
        if ( !isset( $this->safe_array ) ) throw new Exception( "No array has been declared" );

        return $this->safe_array;
    }

    /**
     * Return the array without escaped characters.
     *
     * @return array
     * @throws Exception
     */
    public function getUnsafeArray() {
        if ( !isset( $this->unsafe_array ) ) throw new Exception( "No array has been declared" );

        return $this->unsafe_array;
    }

    /**
     * Store the array unescaped.
     *
     * @param $array
     */
    private function storeDirtyArray( $array ) {
        $this->unsafe_array = $array;
    }

    /**
     * Escape and store the array.
     *
     * @param $array
     */
    private function storeCleanArray( $array ) {
        $this->cleanArray( $array );

        $this->safe_array = $array;
    }

    /**
     * @param $array
     */
    private function cleanArray( &$array ) {
        array_walk_recursive( $array, "SafeComplexArray::filter" );
    }

    /**
     * Called by SafeComplexArray::cleanArray in the array_walk_recursive function.
     *
     * @param $value
     */
    private static function filter( &$value ) {
        $value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
    }
}