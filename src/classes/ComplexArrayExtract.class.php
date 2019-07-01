<?php

/**
 * Class ComplexArrayExtract
 *
 * Defines the parser function {{#complexarrayextract:}}, which allows users to create a new array from a subarray.
 *
 * @extends WSArrays
 */
class ComplexArrayExtract extends WSArrays
{
    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $name
     * @param string $subarray
     * @return array|bool
     */
    public static function defineParser( Parser $parser, $name = '', $subarray = '' ) {
        if(!$name) {
            $ca_omitted = wfMessage( 'ca-omitted', 'New array' );

            return GlobalFunctions::error($ca_omitted);
        }

        if(!GlobalFunctions::isValidArrayName($name)) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error($ca_invalid_name);
        }

        if(!$subarray) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Subarray' );

            return GlobalFunctions::error($ca_omitted);
        }

        return ComplexArrayExtract::arrayExtract($name, $subarray);
    }

    /**
     * @param $name
     * @param $subarray
     * @return array|bool
     */
    private static function arrayExtract($name, $subarray) {
        if(!strpos($subarray, "[") ||
           !strpos($subarray, "]")) {
            $ca_subarray_not_provided = wfMessage('ca-subarray-not-provided');

            return GlobalFunctions::error($ca_subarray_not_provided);
        }

        if(!$array = GlobalFunctions::getArrayFromArrayName($subarray)) {
            $ca_undefined_array = wfMessage('ca-undefined-array');

            return GlobalFunctions::error($ca_undefined_array);
        }

        if(GlobalFunctions::definedArrayLimitReached()) {
            $ca_max_defined_arrays_reached = wfMessage('ca-max-defined-arrays-reached', WSArrays::$options['max_defined_arrays'], $name);

            return GlobalFunctions::error($ca_max_defined_arrays_reached);
        }

        WSArrays::$arrays[$name] = $array;

        return null;
    }
}