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
		return 'normal';
	}

	/**
	 * Holds the array being worked on.
	 *
	 * @var array
	 */
	protected static $array = [];

	/**
	 * @var string
	 */
	private static $indent_char = "*";

	/**
	 * @var null
	 */
	private static $noparse = false;

	/**
	 * Define all allowed parameters. This parser is hooked with Parser::SFH_OBJECT_ARGS.
	 *
	 * @param Parser $parser
	 *
	 * @param null $array_name
	 * @param null $options
	 * @param bool $noparse
	 * @return array|mixed|null|string|string[]
	 *
	 * @throws Exception
	 */
	public static function getResult( Parser $parser, $array_name = null, $options = null, $noparse = false ) {
		GlobalFunctions::fetchSemanticArrays();

		self::$array = [];

		if ( empty( $array_name ) ) {
			return GlobalFunctions::error( wfMessage( 'ca-omitted', 'Name' ) );
		}

		self::$noparse = filter_var($noparse, FILTER_VALIDATE_BOOLEAN);

		return self::arrayPrint( $array_name, $options );
	}

	/**
	 * @param $array_name
	 * @param string $options
	 * @return array|mixed|null|string|string[]
	 *
	 * @throws Exception
	 */
	private static function arrayPrint( $array_name, $options = '' ) {
		self::$array = GlobalFunctions::getArrayFromArrayName( $array_name );

		if ( !self::$array ) {
			// Array does not exist
			return '';
		}

		if ( !empty( $options ) ) {
			GlobalFunctions::serializeOptions( $options );
			$result = self::applyOptions( $options );
		} else {
			$result = self::createList();
		}

		return $result;
	}

	/**
	 * @param $options
	 * @return array|mixed|null|string|string[]
	 */
	private static function applyOptions( $options ) {
		if ( is_array( $options ) ) {
			$options = $options[ 0 ];
		}

		switch ( $options ) {
			case 'markup':
			case 'wson':
				return GlobalFunctions::arrayToMarkup( self::$array );
				break;
			default:
				return self::createList();
				break;
		}
	}

	/**
	 * Create an (un)ordered list from an array.
	 *
	 * @return array|null|string
	 */
	private static function createList() {
		if ( !is_array( self::$array ) || count( self::$array ) === 1 && !GlobalFunctions::containsArray( self::$array ) ) {
			if ( is_array( self::$array ) ) {
				$last_el = reset( self::$array );
				$return  = key( self::$array ) . ": " . $last_el;

				return [ $return, 'noparse' => self::$noparse ];
			} else {
				return [ self::$array, 'noparse' => self::$noparse ];
			}
		}

		$result = null;
		foreach ( self::$array as $key => $value ) {
			if ( !is_array( $value ) ) {
				$result .= is_numeric( $key ) ? self::$indent_char . " $value\n" : self::$indent_char . " $key: $value\n";
			} else {
				$result .= self::$indent_char . " " . strval( $key ) . "\n";
				self::addArrayToList( $value, $result );
			}
		}

		return $result;
	}

	/**
	 * @param $array
	 * @param &$result
	 * @param int $depth
	 */
	private static function addArrayToList( $array, &$result, $depth = 0 ) {
		$depth++;

		foreach ( $array as $key => $value ) {
			$indent = str_repeat( self::$indent_char, $depth + 1 );

			if ( !is_array( $value ) ) {
				if ( is_numeric( $key ) ) {
					$result .= "$indent $value\n";
				} else {
					$result .= "$indent $key: $value\n";
				}
			} else {
				$result .= "$indent " . strval( $key ) . "\n";

				self::addArrayToList( $value, $result, $depth );
			}
		}
	}
}
