<?php

/**
 * Class ComplexArrayUnion
 *
 * Defines the parser function {{#complexarraypusharray:}}, which allows users to push one or more arrays to the end of another array, creating a new array.
 *
 * @extends WSArrays
 */
class ComplexArrayPushArray extends WSArrays
{
    /**
     * Define parameters and initialize parser.
     *
     * @param Parser $parser
     * @param string $name
     * @return array|null
     */
    public static function defineParser( Parser $parser ) {
        GlobalFunctions::fetchSemanticArrays();

        return ComplexArrayPushArray::arrayUnion(func_get_args());
    }

    /**
     * @param $name
     * @return array|null
     */
    private static function arrayUnion($args) {
        // Remove $parser from args
        array_shift($args);

        // Get the first argument (name of new array)
        $new_array = reset($args);

        if(!GlobalFunctions::isValidArrayName($new_array)) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error($ca_invalid_name);
        }

        // Remove the first (second) argument
        array_shift($args);

        if(count($args) < 2) {
            $ca_too_little_arrays = wfMessage('ca-too-little-arrays');

            return GlobalFunctions::error($ca_too_little_arrays);
        }

        $arrays = [];
        foreach($args as $array) {
            if(!WSArrays::$arrays[$array]) {
                $ca_nonexistent_multiple = wfMessage('ca-nonexistent-multiple');

                return GlobalFunctions::error($ca_nonexistent_multiple);
            }

            array_push($arrays, WSArrays::$arrays[$array]);
        }

        if(GlobalFunctions::definedArrayLimitReached()) {
            $ca_max_defined_arrays_reached = wfMessage('ca-max-defined-arrays-reached', WSArrays::$options['max_defined_arrays'], $new_array);

            return GlobalFunctions::error($ca_max_defined_arrays_reached);
        }

        WSArrays::$arrays[$new_array] = $arrays;

        return null;
    }
}