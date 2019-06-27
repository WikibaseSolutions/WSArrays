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
        $ca_omitted = wfMessage('ca-omitted', 'Name');
        if(!$name) return GlobalFunctions::error($ca_omitted);

        $ca_omitted = wfMessage('ca-omitted', 'Subarray');
        if(!$subarray) return GlobalFunctions::error($ca_omitted);

        return self::arrayExtract($name, $subarray);
    }

    /**
     * @param $name
     * @param $subarray
     * @return array|bool
     */
    private static function arrayExtract($name, $subarray) {
        if(!strpos($subarray, "[") || !strpos($subarray, "]")) {
            $ca_subarray_not_provided = wfMessage('ca-subarray-not-provided');

            return GlobalFunctions::error($ca_subarray_not_provided);
        }

        $ca_undefined_array = wfMessage('ca-undefined-array');
        if(!$array = GlobalFunctions::getArrayFromArrayName($subarray)) return GlobalFunctions::error($ca_undefined_array);

        WSArrays::$arrays[$name] = $array;

        return null;
    }
}