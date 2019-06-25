<?php

/**
 * Class ComplexArrayDefine
 *
 * Defines the parser function {{#complexarraydefine:}}, which allows users to define a new array.
 *
 * @extends WSArrays
 */
class ComplexArrayDefine extends WSArrays
{
    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $name The name of the array that is going to be defined
     * @param string $wson The array, encoded in valid JSON
     *
     * @return null
     */
    public static function defineParser( Parser $parser, $name = '', $wson = '') {
        GlobalFunctions::fetchSemanticArrays();

        $ca_omitted = wfMessage('ca-omitted', 'Name');
        if(empty($name)) return GlobalFunctions::error($ca_omitted);

        $ca_omitted = wfMessage('ca-omitted', 'Array');
        if(empty($wson)) return GlobalFunctions::error($ca_omitted);

        return self::arrayDefine($name, $wson);
    }

    /**
     * Define array and store it in WSArrays::$arrays.
     *
     * @param $name
     * @param $wson
     * @return array|null
     */
    private static function arrayDefine($name, $wson) {
        GlobalFunctions::WSONtoJSON($wson);

        $ca_invalid_wson = wfMessage('ca-invalid-wson');
        if(!GlobalFunctions::isValidJSON($wson)) return GlobalFunctions::error($ca_invalid_wson);

        $ca_max_defined_arrays_reached = wfMessage('ca-max-defined-arrays-reached', WSArrays::$options['max_defined_arrays'], $name);
        if(GlobalFunctions::definedArrayLimitReached()) return GlobalFunctions::error($ca_max_defined_arrays_reached);

        $array = json_decode($wson, true);

        WSArrays::$arrays[$name] = $array;

        return null;
    }
}