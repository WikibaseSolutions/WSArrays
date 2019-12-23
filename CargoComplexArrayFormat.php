<?php
/**
 * @author Marijn van Wezel
 * @ingroup Cargo
 */

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

namespace {
    class CargoComplexArrayFormat extends CargoDisplayFormat {
        public static function allowedParameters() {
            return array(

            );
        }

        /**
         * @param array $valuesTable Unused
         * @param array $formattedValuesTable
         * @param array $fieldDescriptions
         * @param array $displayParams
         *
         * @return string wikitext
         */
        function display( $valuesTable, $formattedValuesTable, $fieldDescriptions, $displayParams ) {
            var_dump($formattedValuesTable, $fieldDescriptions, $displayParams);

            return "";
        }
    }
}

namespace CargoComplexArrayFormat {
    /**
     * Class SafeComplexArray
     *
     * It defines the object arrays should be stored in. Arrays that are stored in this object, are always escaped and safe. This class is a copy of the class in src/ComplexArray.class.php.
     *
     * @package SMW\Query\ResultPrinters
     * @alias src/ComplexArray.class.php
     */
    class SafeComplexArray {
        private $safe_array = array();

        /**
         * @param array $array
         */
        public function __construct( array $array ) {
            $this->cleanArray( $array );
        }

        /**
         * @return array
         * @throws Exception
         */
        public function getArray() {
            if ( !isset( $this->safe_array ) ) throw new Exception( "No array has been declared" );

            return $this->safe_array;
        }

        /**
         * @param $array
         */
        private function cleanArray( &$array ) {
            array_walk_recursive( $array, "SafeComplexArray::filter" );

            $this->safe_array = $array;
        }

        /**
         * @param $value
         */
        private static function filter( &$value ) {
            $value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
        }
    }
}