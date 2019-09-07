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
 *
 * Initialization file for WSArrays.
 *
 * @license GPL-2.0-or-later
 * @version: 1.5
 *
 * @author Xxmarijnw <marijn@wikibase.nl>
 *
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die();
} else {
    global $wgVersion;
    global $wfSkipVersionControl;

    if ( !$wfSkipVersionControl ) {
        if ( version_compare( $wgVersion, 1.27 ) < 0 ) {
            if ( function_exists('wfMessage') ) {
                $ca_unsupported_version = wfMessage( 'ca-unsopported-version', 'MediaWiki', $wgVersion, '1.27' );
            } else {
                $ca_unsupported_version = "This version of MediaWiki is not supported by WSArrays (has version ".$wgVersion.", requires at least version 1.27)";
            }

            throw new Exception( $ca_unsupported_version );
        }

        if ( version_compare( PHP_VERSION, 5.0 ) < 0 ) {
            if ( function_exists( 'wfMessage' ) ) {
                $ca_unsupported_version = wfMessage( 'ca-unsopported-version', 'PHP', PHP_VERSION, '5.3' );
            } else {
                $ca_unsupported_version = "This version of PHP is not supported by WSArrays (has version ".PHP_VERSION.", requires at least version 5.3)";
            }

            throw new Exception( $ca_unsupported_version );
        }
    }
}

require_once 'GlobalFunctions.class.php';

global $wfEnableResultPrinter;

if ( $wfEnableResultPrinter ) {
    $GLOBALS[ 'smwgResultFormats' ][ 'complexarray' ] = 'SMW\Query\ResultPrinters\ComplexArrayPrinter';
}

/**
 * Class WSArrays
 *
 * Defines all parser functions.
 *
 * @extends GlobalFunctions
 */
class WSArrays extends SafeComplexArray {
    const VERSION = '1.5';

    /**
     * This variable holds all defined arrays. If an array is defined called "array", the array will be stored in WSArrays::$arrays["array"].
     *
     * @var array
     */
    public static $arrays = [];

    /**
     * This function is called on every page with a WSArrays parser function.
     *
     * @param Parser $parser
     * @return boolean
     * @throws Exception
     */
    final public static function onParserFirstCallInit( Parser $parser ) {
        try {
            require_once('ExtensionFactory.class.php');
            require_once('ResultPrinterFactory.class.php');

            ResultPrinterFactory::loadResultPrinters( $parser );
            ExtensionFactory::loadExtensions( $parser );
        } catch ( Exception $e ) {
            return false;
        }

        return true;
    }
}
