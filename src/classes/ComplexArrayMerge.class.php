<?php

/**
 * Class ComplexArrayMerge
 *
 * Defines the parser function {{#complexarraymerge:}}, which allows users to merge multiple arrays.
 *
 * @extends WSArrays
 */
class ComplexArrayMerge extends WSArrays
{
    /**
     * @var string
     */
    private static $new_array = '';

    /**
     * @var string
     */
    private static $last_element = '';

    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @return array|null
     */
    public static function defineParser( Parser $parser ) {
        GlobalFunctions::fetchSemanticArrays();

        return ComplexArrayMerge::arrayMerge(func_get_args());
    }

    /**
     * @param $args
     * @return array|null
     */
    private static function arrayMerge($args) {
        ComplexArrayMerge::parseFunctionArguments($args);

        if(!GlobalFunctions::isValidArrayName(ComplexArrayMerge::$new_array)) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error($ca_invalid_name);
        }

        if(count($args) < 2) {
            $ca_too_little_arrays = wfMessage('ca-too-little-arrays');

            return GlobalFunctions::error($ca_too_little_arrays);
        }

        if(GlobalFunctions::definedArrayLimitReached()) {
            $ca_max_defined_arrays_reached = wfMessage('ca-max-defined-arrays-reached', WSArrays::$options['max_defined_arrays'], ComplexArrayMerge::$new_array);

            return GlobalFunctions::error($ca_max_defined_arrays_reached);
        }

        $arrays = ComplexArrayMerge::iterate($args);

        if(!is_array($arrays)) {
            $ca_nonexistent_multiple = wfMessage('ca-nonexistent-multiple');

            return GlobalFunctions::error($ca_nonexistent_multiple);
        }

        if(ComplexArrayMerge::$last_element === "recursive") {
            WSArrays::$arrays[ComplexArrayMerge::$new_array] = call_user_func_array('array_merge_recursive', $arrays);
        } else {
            WSArrays::$arrays[ComplexArrayMerge::$new_array] = call_user_func_array('array_merge', $arrays);
        }

        return null;
    }

    /**
     * @param $args
     */
    private static function parseFunctionArguments(&$args) {
        ComplexArrayMerge::removeFirstItemFromArray($args);
        ComplexArrayMerge::getFirstItemFromArray($args);
        ComplexArrayMerge::removeFirstItemFromArray($args);
        ComplexArrayMerge::removeLastItemFromArray($args);

        // If the last element is not "recursive", add it back
        if(ComplexArrayMerge::$last_element !== "recursive") {
            ComplexArrayMerge::addItemToEndOfArray($args, ComplexArrayMerge::$last_element);
        }
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
     * @param $array
     */
    private static function removeFirstItemFromArray(&$array) {
        array_shift($array);
    }

    /**
     * @param $array
     */
    private static function removeLastItemFromArray(&$array) {
        ComplexArrayMerge::$last_element = array_pop($array);
    }

    /**
     * @param $array
     */
    private static function getFirstItemFromArray(&$array) {
        ComplexArrayMerge::$new_array = reset($array);
    }

    /**
     * @param $array
     * @param $item
     */
    private static function addItemToEndOfArray(&$array, $item) {
        array_push($array, $item);
    }
}