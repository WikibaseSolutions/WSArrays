<?php

/**
 * Class ComplexArrayAddValue
 *
 * Defines the parser function {{#complexarrayaddvalue:}}, which allows users to add values to (sub)arrays.
 *
 * @extends WSArrays
 */
class ComplexArrayAddValue extends WSArrays
{
    /**
     * Define parameters and initialize parser.
     *
     * @param Parser $parser
     * @param string $name
     * @param string $value
     * @return array|null
     */
    public static function defineParser( Parser $parser, $name = '', $value = '' ) {
        GlobalFunctions::fetchSemanticArrays();

        if(empty($name)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error($ca_omitted);
        }

        if(empty($value)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Value' );

            return GlobalFunctions::error($ca_omitted);
        }

        if(!strpos($name, "[") ||
           !strpos($name, "]")) {
            $ca_subarray_not_provided = wfMessage( 'ca-subarray-not-provided' );

            return GlobalFunctions::error($ca_subarray_not_provided);
        }

        return ComplexArrayAddValue::arrayAddValue($name, $value);
    }

    /**
     * @param $array
     * @param $value
     * @return array|null
     */
    private static function arrayAddValue($array, $value) {
        $base_array = GlobalFunctions::calculateBaseArray($array);

        if(!isset(WSArrays::$arrays[$base_array])) {
            $ca_undefined_array = wfMessage('ca-undefined-array');

            return GlobalFunctions::error($ca_undefined_array);
        }

        if(preg_match_all("/(?<=\[).+?(?=\])/", $array, $matches) === 0) {
            $ca_invalid_name = wfMessage('ca-invalid-name');

            return GlobalFunctions::error($ca_invalid_name);
        }

        $wsarray = WSArrays::$arrays[$base_array];

        ComplexArrayAddValue::set($matches[0], $wsarray, $value);

        WSArrays::$arrays[$base_array] = $wsarray;

        return null;
    }

    /**
     * @param $path
     * @param array $array
     * @param null $value
     */
    private static function set($path, &$array = array(), $value = null) {
        GlobalFunctions::WSONtoJSON($value);

        if(GlobalFunctions::isValidJSON($value)) {
            $value = json_decode($value, true);
        }

        $temp =& $array;

        foreach($path as $key) {
            $temp =& $temp[$key];
        }

        $temp = $value;
    }
}