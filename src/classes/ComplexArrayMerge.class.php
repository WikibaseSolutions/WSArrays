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
        // Remove $parser from args
        array_shift($args);

        // Get the first argument (name of new array)
        $new_array = reset($args);

        // Remove the first (second) argument
        array_shift($args);

        // Get and remove the last argument
        $last_element = array_pop($args);

        // If the last element is not "recursive", add it back
        if($last_element !== "recursive") {
            array_push($args, $last_element);
        }

        $ca_too_little_arrays = wfMessage('ca-too-little-arrays');
        if(count($args) < 2) return GlobalFunctions::error($ca_too_little_arrays);

        $arrays = [];
        foreach($args as $array) {
            if(!WSArrays::$arrays[$array]) {
                $ca_nonexistant_multiple = wfMessage('ca-nonexistant-multiple');
                return GlobalFunctions::error($ca_nonexistant_multiple);
            }

            array_push($arrays, WSArrays::$arrays[$array]);
        }


        if($last_element === "recursive") {
            WSArrays::$arrays[$new_array] = call_user_func_array('array_merge_recursive', $arrays);
        } else {
            WSArrays::$arrays[$new_array] = call_user_func_array('array_merge', $arrays);
        }

        return null;
    }
}