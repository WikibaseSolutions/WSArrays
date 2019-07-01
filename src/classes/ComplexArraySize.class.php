<?php

/**
 * Class ComplexArraySize
 *
 * Defines the parser function {{#complexarraysize:}}, which allows users to get the size of a (sub)array.
 *
 * @extends WSArrays
 */
class ComplexArraySize extends WSArrays
{
    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $name
     * @param string $options
     * @return array|int
     */
    public static function defineParser( Parser $parser, $name = '', $options = '') {
        GlobalFunctions::fetchSemanticArrays();

        $ca_omitted = wfMessage( 'ca-omitted', 'Name' );
        if(empty($name)) return GlobalFunctions::error($ca_omitted);

        return ComplexArraySize::arraySize($name, $options);
    }

    /**
     * Calculate size of array.
     *
     * @param $name
     * @param string $options
     * @return array|int
     */
    private static function arraySize($name, $options = '') {
        $base_array = strtok($name, "[");

        $ca_undefined_array = wfMessage('ca-undefined-array');
        if(!isset(WSArrays::$arrays[$base_array])) return GlobalFunctions::error($ca_undefined_array);
        $array = WSArrays::$arrays[$base_array];

        if(!strpos($name, "[") && empty($options)) {
            $count = count($array, COUNT_RECURSIVE);

            return $count;
        }

        if(!strpos($name, "[") && $options === "top") {
            $count = count($array);

            return $count;
        }

        if(!is_array($array = GlobalFunctions::getArrayFromArrayName($name))) return GlobalFunctions::error($ca_undefined_array);

        if($options === "top") return count($array);

        return count($array, COUNT_RECURSIVE);
    }
}