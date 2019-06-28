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

        return self::arrayUnion(func_get_args());
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

        // Remove the first (second) argument
        array_shift($args);

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

        WSArrays::$arrays[$new_array] = $arrays;

        return null;
    }
}