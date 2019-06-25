<?php

/**
 * Class ComplexArrayPushValue
 *
 * Defines the parser function {{#complexarraypushvalue:}}, which allows users to push a value or subarray to the end of a (sub)array.
 *
 * @extends WSArrays
 */
class ComplexArrayPushValue extends WSArrays
{
    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $array
     * @param string $value
     * @return array|bool|null
     */
    public static function defineParser( Parser $parser, $array = '', $value = '') {
        GlobalFunctions::fetchSemanticArrays();

        $ca_omitted = wfMessage('ca-omitted', 'Array');
        if(empty($array)) return GlobalFunctions::error($ca_omitted);

        $ca_omitted = wfMessage('ca-omitted', 'Value');
        if(empty($value)) return GlobalFunctions::error($ca_omitted);

        return self::arrayPushValue($array, $value);
    }

    /**
     * @param $array
     * @param $value
     * @return array|bool|null
     */
    private static function arrayPushValue($array, $value) {
        $base_array = strtok($array, "[");

        $ca_undefined_array = wfMessage('ca-undefined-array');
        if(!isset(WSArrays::$arrays[$base_array])) return GlobalFunctions::error($ca_undefined_array);
        $wsarray = WSArrays::$arrays[$base_array];

        if(!strpos($array, "[")) {
            GlobalFunctions::WSONtoJSON($value);

            if(GlobalFunctions::isValidJSON($value)) {
                $value = json_decode($value, true);
            }

            array_push($wsarray, $value);

            WSArrays::$arrays[$base_array] = $wsarray;

            return null;
        }

        $valid = preg_match_all("/(?<=\[).+?(?=\])/", $array, $matches);

        $ca_invalid_name = wfMessage('ca-invalid-name');
        if($valid === 0) return GlobalFunctions::error($ca_invalid_name);

        $result = self::add($matches[0], $wsarray, $value);

        if($result !== true) return $result;

        WSArrays::$arrays[$base_array] = $wsarray;

        return null;
    }

    /**
     * Push value to location defined in $path.
     *
     * @param $path
     * @param array $array
     * @param null $value
     * @return array|bool
     */
    private static function add($path, &$array = array(), $value = null) {
        GlobalFunctions::WSONtoJSON($value);

        if(GlobalFunctions::isValidJSON($value)) {
            $value = json_decode($value, true);
        }

        $temp =& $array;

        $depth = count($path);

        $current_depth = 1;
        foreach($path as $key) {
            $current_depth++;

            $ca_nonexistent_subarray = wfMessage('ca-nonexistent-subarray');
            if(!array_key_exists($key, $temp)) return GlobalFunctions::error($ca_nonexistent_subarray);

            if($current_depth !== $depth) {
                $temp =& $temp[$key];
            }
        }

        array_push($temp, $value);

        return true;
    }
}