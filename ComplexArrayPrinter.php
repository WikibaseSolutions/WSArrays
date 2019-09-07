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

/*
 *
 * PLACE THIS FILE IN THE FOLDER "{mediawiki_dir}/extensions/SemanticMediaWiki/src/Query/ResultPrinters"
 *
 */

namespace SMW\Query\ResultPrinters;

/**
 * Class SafeComplexArray
 *
 * It defines the object arrays should be stored in. Arrays that are stored in this object, are always escaped and safe. This class is a copy of the class in src/SafeComplexArray.class.php.
 *
 * @package SMW\Query\ResultPrinters
 * @alias src/SafeComplexArray.class.php
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

/**
 * Class ComplexArrayPrinter
 *
 * @package SMW\Query\ResultPrinters
 * @extends ResultPrinter
 */
class ComplexArrayPrinter extends ResultPrinter {
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var bool
     */
    private $unassociative = false;

    /**
     * @var bool
     */
    private $hide_meta = false;

    /**
     * @var bool
     */
    private $simple = false;

    /**
     * Define the name of the format.
     *
     * @return string
     */
    public function getName() {
        return 'complexarray';
    }

    /**
     * @param array $definitions
     * @return array
     */
    public function getParamDefinitions( array $definitions ) {
        $definitions = parent::getParamDefinitions( $definitions );

        $definitions[] = [
            'name' => 'name',
            'message' => 'ca-smw-paramdesc-name',
            'default' => ''
        ];

        $definitions[] = [
            'name' => 'hide',
            'message' => 'ca-smw-paramdesc-hide',
            'default' => 'false'
        ];

        $definitions[] = [
            'name' => 'unassociative',
            'message' => 'ca-smw-paramdesc-unassociative',
            'default' => 'false'
        ];

        $definitions[] = [
            'name' => 'simple',
            'message' => 'ca-smw-paramdesc-simple',
            'default' => 'true'
        ];

        return $definitions;
    }

    /**
     * @param \SMWQueryResult $queryResult
     * @param $outputMode
     * @return bool|string
     */
    protected function getResultText( \SMWQueryResult $queryResult, $outputMode ) {
        return $this->buildContents( $queryResult );
    }

    /**
     * @param \SMWQueryResult $queryResult
     * @return bool|string
     */
    private function buildContents( \SMWQueryResult $queryResult ) {
        global $wfDefinedArraysGlobal;

        $this->name = $this->params[ 'name' ];
        $this->hide_meta = filter_var( $this->params[ 'hide' ], FILTER_VALIDATE_BOOLEAN );
        $this->unassociative = filter_var( $this->params[ 'unassociative' ], FILTER_VALIDATE_BOOLEAN );
        $this->simple = filter_var( $this->params[ 'simple' ], FILTER_VALIDATE_BOOLEAN );

        if ( !$this->name ) {
            $json = json_encode( $this->buildResultArray( $queryResult ) );

            $json = preg_replace( "/(?!\B\"[^\"]*){(?![^\"]*\"\B)/i", "((", $json );
            $json = preg_replace( "/(?!\B\"[^\"]*)}(?![^\"]*\"\B)/i", "))", $json );

            return $json;
        }

        $result = $this->buildResultArray( $queryResult );

        $wfDefinedArraysGlobal[ $this->name ] = new \SafeComplexArray( $result );

        return null;
    }

    /**
     * @param \SMWQueryResult $res
     * @return array
     */
    private function buildResultArray( \SMWQueryResult $res ) {
        /**
         *
         */
        $res = array_merge( $res->serializeToArray(), [ 'rows' => $res->getCount() ] );

        /**
         * Create an empty array that needs to be returned.
         */
        $return = [];

        foreach ( $res['results'] as $result ) {
            $r = [];

            $printouts = $result[ 'printouts' ];

            if ( count($printouts) !== 0 ) {
                foreach ( $printouts as $key => $printout ) {
                    if ( isset( $printout[ 0 ] ) ) {
                        switch ( $printout[ 0 ] ) {
                            case 'f':
                                $printout[ 0 ] = 0;
                                break;
                            case 't':
                                $printout[ 0 ] = 1;
                                break;
                        }

                        if ( $this->simple ) {
                            if ( is_array( $printout[ 0 ] ) ) {
                                if ( isset( $printout[ 0 ][ 'fulltext' ] ) ) {
                                    $printout[ 0 ] = $printout[ 0 ][ 'fulltext' ];
                                }
                            } elseif ( strpos( $printout[ 0 ], 'mailto:' ) !== false ) {
                                $printout[0] = str_replace( "mailto:", "", $printout[ 0 ] );
                            }
                        }

                        if ( $this->unassociative ) {
                            array_push( $r, $printout[ 0 ] );
                        } else {
                            $r[$key] = $printout[ 0 ];
                        }
                    }
                }
            }

            if ( !$this->hide_meta ) {
                if ( isset( $result[ 'fulltext' ] ) ) $r[ 'catitle' ] = $result[ 'fulltext' ];
                if ( isset( $result[ 'fullurl' ] ) ) $r[ 'cafullurl' ] = $result[ 'fullurl' ];
                if ( isset( $result[ 'namespace' ] ) ) $r[ 'canamespace' ] = $result[ 'namespace' ];
                if ( isset( $result[ 'exists' ] ) ) $r[ 'caexists' ] = $result[ 'exists' ];
                if ( isset( $result[ 'displaytitle' ] ) ) $r[ 'cadisplaytitle' ] = $result[ 'displaytitle' ];
            }

            array_push( $return, $r );
        }

        return $return;
    }
}
