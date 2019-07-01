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

        if(empty($name)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error($ca_omitted);
        }

        if(!GlobalFunctions::isValidArrayName($name)) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error($ca_invalid_name);
        }

        if(empty($wson)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Array' );

            return GlobalFunctions::error($ca_omitted);
        }

        return ComplexArrayDefine::arrayDefine($name, $wson);
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

        if(!GlobalFunctions::isValidJSON($wson)) {
            $ca_invalid_wson = wfMessage('ca-invalid-wson');

            return GlobalFunctions::error($ca_invalid_wson);
        }

        if(GlobalFunctions::definedArrayLimitReached()) {
            $ca_max_defined_arrays_reached = wfMessage('ca-max-defined-arrays-reached', WSArrays::$options['max_defined_arrays'], $name);

            return GlobalFunctions::error($ca_max_defined_arrays_reached);
        }

        WSArrays::$arrays[$name] = json_decode($wson, true);

        return null;
    }
}