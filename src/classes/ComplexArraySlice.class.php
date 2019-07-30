<?php

/**
 * Class ComplexArraySlice
 *
 * Defines the parser function {{#complexarrayslice:}}, which allows users to slice an array.
 *
 * @extends WSArrays
 */
class ComplexArraySlice extends WSArrays
{
    /**
     * @param Parser $parser
     * @param string $new_array
     * @param string $array
     * @param string $offset
     * @param string $length
     * @return array|null
     */
    public static function defineParser( Parser $parser, $new_array = '', $array = '', $offset = '', $length = '') {
        GlobalFunctions::fetchSemanticArrays();

        if(empty($new_array)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'New array' );

            return GlobalFunctions::error($ca_omitted);
        }

        if(!GlobalFunctions::isValidArrayName($new_array)) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error($ca_invalid_name);
        }

        if(empty($array)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error($ca_omitted);
        }

        return ComplexArraySlice::arraySlice($new_array, $array, $offset, $length);
    }

    /**
     * @param $new_array
     * @param $array
     * @param integer $offset
     * @param integer $length
     * @return array|null
     */
    private static function arraySlice($new_array, $array, $offset = 0, $length = 0) {
        if(!$array = GlobalFunctions::getArrayFromArrayName($array)) {
            $ca_undefined_array = wfMessage('ca-undefined-array');

            return GlobalFunctions::error($ca_undefined_array);
        }

        if(!empty($length)) {
            WSArrays::$arrays[$new_array] = array_slice($array, $offset, $length);

            return null;
        } else {
            WSArrays::$arrays[$new_array] = array_slice($array, $offset);

            return null;
        }
    }
}