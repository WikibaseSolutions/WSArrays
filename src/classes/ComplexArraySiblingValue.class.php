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
 * Class ComplexArraySibling
 *
 * Defines the parser function {{#complexarraysiblingvalue:}}, which returns the value of the sibling with a given key of the given key.
 *
 * @extends WSArrays
 */
class ComplexArraySiblingValue extends ResultPrinter {
	public function getName() {
		return 'complexarraysiblingvalue';
	}

	public function getAliases() {
		return [
			'casiblingvalue',
			'casiblingv'
		];
	}

	public function getType() {
		return 'normal';
	}

	/**
	 * Define all allowed parameters.
	 *
	 * @param Parser $parser
	 * @param string|null $array_key
	 * @param string|null $sibling_key
	 * @return string|array
	 * @throws Exception
	 */
	public static function getResult( Parser $parser, $array_key = null, $sibling_key = null ) {
		GlobalFunctions::fetchSemanticArrays();

		if ( empty( $array_key ) ) {
			return GlobalFunctions::error( wfMessage( 'ca-omitted', 'Key' ) );
		}

		if ( empty( $sibling_key ) && $sibling_key != "0" ) {
			return GlobalFunctions::error( wfMessage( 'ca-omitted', 'Sibling key' ) );
		}

		return self::arraySiblingValue( $array_key, $sibling_key );
	}

	/**
	 * @param string $array_key
	 * @param string $sibling_key
	 *
	 * @return string
	 * @throws Exception
	 */
	private static function arraySiblingValue( $array_key, $sibling_key ) {
		$base_array = GlobalFunctions::getBaseArrayFromArrayName( $array_key );

		$array = GlobalFunctions::getArrayFromArrayName( $base_array );
		$array_keys = GlobalFunctions::getKeys( $array_key );

		array_pop( $array_keys );

		$parent_array = self::getArrayFromArrayAtKeys( $array, $array_keys );

		if ( $parent_array && isset( $parent_array[$sibling_key] ) && !is_array( $parent_array[$sibling_key] ) ) {
			return $parent_array[$sibling_key];
		}

		return null;
	}

	private static function getArrayFromArrayAtKeys( array $array, array $array_keys ) {
		$temp = $array;

		foreach ( $array_keys as $key ) {
			if ( isset( $temp[$key] ) ) {
				$temp = $temp[$key];
			} else {
				return false;
			}
		}

		return $temp;
	}
}
