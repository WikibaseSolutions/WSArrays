<?php

/**
 * Class ComplexArrayPushArray
 *
 * Defines the parser function {{#complexarraypusharray:}}, which allows users to push one or more arrays to the end of another array, creating a new array.
 *
 * @extends WSArrays
 */
class ComplexArrayPushArray extends WSArrays
{
    /**
     * @var string
     */
    private static $new_array = '';

    /**
     * Define parameters and initialize parser.
     *
     * @param Parser $parser
     * @return array|null
     */
    public static function defineParser( Parser $parser ) {
        GlobalFunctions::fetchSemanticArrays();

        return ComplexArrayPushArray::arrayPush(func_get_args());
    }

    /**
     * @param $args
     * @return array|null
     */
    private static function arrayPush($args) {
        ComplexArrayPushArray::parseFunctionArguments($args);

        if(!GlobalFunctions::isValidArrayName(ComplexArrayPushArray::$new_array)) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error($ca_invalid_name);
        }

        if(count($args) < 2) {
            $ca_too_little_arrays = wfMessage('ca-too-little-arrays');

            return GlobalFunctions::error($ca_too_little_arrays);
        }

        if(GlobalFunctions::definedArrayLimitReached()) {
            $ca_max_defined_arrays_reached = wfMessage('ca-max-defined-arrays-reached', WSArrays::$options['max_defined_arrays'], $new_array);

            return GlobalFunctions::error($ca_max_defined_arrays_reached);
        }

        $arrays = ComplexArrayPushArray::iterate($args);

        if(!is_array($arrays)) {
            $ca_nonexistent_multiple = wfMessage('ca-nonexistent-multiple');

            return GlobalFunctions::error($ca_nonexistent_multiple);
        }

        WSArrays::$arrays[ComplexArrayPushArray::$new_array] = $arrays;

        return null;
    }

    /**
     * @param $arr
     * @return array|bool
     */
    private static function iterate($arr) {
        $arrays = [];
        foreach($arr as $array) {
            if(!WSArrays::$arrays[$array]) {
                return false;
            }

            array_push($arrays, WSArrays::$arrays[$array]);
        }

        return $arrays;
    }

    /**
     * @param $args
     */
    private static function parseFunctionArguments(&$args) {
        ComplexArrayPushArray::removeFirstItemFromArray($args);
        ComplexArrayPushArray::getFirstItemFromArray($args);
        ComplexArrayPushArray::removeFirstItemFromArray($args);
    }

    /**
     * @param $array
     */
    private static function removeFirstItemFromArray(&$array) {
        array_shift($array);
    }

    /**
     * @param $array
     */
    private static function getFirstItemFromArray(&$array) {
        ComplexArrayPushArray::$new_array = reset($array);
    }
}