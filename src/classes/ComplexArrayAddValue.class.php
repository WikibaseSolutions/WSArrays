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
    public static function defineParser( Parser $parser, $name = '', $value = '') {
        GlobalFunctions::fetchSemanticArrays();

        $ca_omitted = wfMessage('ca-omitted', 'Name');
        if(empty($name)) return GlobalFunctions::error($ca_omitted);

        $ca_omitted = wfMessage('ca-omitted', "Value");
        if(empty($value)) return GlobalFunctions::error($ca_omitted);

        $ca_subarray_not_provided = wfMessage('ca-subarray-not-provided');
        if(!strpos($name, "[") || !strpos($name, "]")) return GlobalFunctions::error($ca_subarray_not_provided);

        return self::arrayAddValue($name, $value);
    }

    /**
     * @param $array
     * @param $value
     * @return array|null
     */
    private static function arrayAddValue($array, $value) {
        $base_array = strtok($array, "[");

        $ca_undefined_array = wfMessage('ca-undefined-array');
        if(!isset(WSArrays::$arrays[$base_array])) return GlobalFunctions::error($ca_undefined_array);
        $wsarray = WSArrays::$arrays[$base_array];

        $valid = preg_match_all("/(?<=\[).+?(?=\])/", $array, $matches);

        $ca_invalid_name = wfMessage('ca-invalid-name');
        if($valid === 0) return GlobalFunctions::error($ca_invalid_name);

        self::set($matches[0], $wsarray, $value);

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