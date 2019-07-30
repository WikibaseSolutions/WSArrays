<?php

/**
 * Class ComplexArraySearch
 *
 * Defines the parser function {{#complexarraysearcharray:}}, which allows users to search for a string in the array, and define an array with all the keys of the result.
 *
 * @extends WSArrays
 */
class ComplexArraySearchArray extends WSArrays
{
    private static $found = [];
    private static $key = 0;

    /**
     * @param Parser $parser
     * @param string $new_array
     * @param string $name
     * @param string $value
     * @return array
     */
    public static function defineParser( Parser $parser, $new_array = '', $name = '', $value = '' )
    {
        GlobalFunctions::fetchSemanticArrays();

        if(empty($new_array)) {
            $ca_omitted = wfMessage('ca-omitted', 'New array');

            return GlobalFunctions::error($ca_omitted);
        }

        if(!GlobalFunctions::isValidArrayName($new_array)) {
            $ca_invalid_name = wfMessage( 'ca-invalid-name' );

            return GlobalFunctions::error($ca_invalid_name);
        }

        if (empty($name)) {
            $ca_omitted = wfMessage('ca-omitted', 'Name');

            return GlobalFunctions::error($ca_omitted);
        }

        if (empty($value)) {
            $ca_omitted = wfMessage('ca-omitted', 'Value');

            return GlobalFunctions::error($ca_omitted);
        }

        return ComplexArraySearchArray::arraySearchArray($new_array, $name, $value);
    }

    /**
     * @param $name
     * @param $value
     * @return array|int
     */
    private static function arraySearchArray($new_array, $name, $value) {
        if (!WSArrays::$arrays[$name]) {
            $ca_undefined_array = wfMessage('ca-undefined-array');

            return GlobalFunctions::error($ca_undefined_array);
        }

        ComplexArraySearchArray::findValues($value, $name);

        if(count(ComplexArraySearchArray::$found) > 0) {
            WSArrays::$arrays[$new_array] = ComplexArraySearchArray::$found;
        }

        return null;
    }

    /**
     * @param $value
     * @param $keyß
     */
    private static function findValues($value, $key) {
        $array = GlobalFunctions::getArrayFromArrayName($key);

        ComplexArraySearchArray::i($array, $value, $key);
    }

    /**
     * @param $array
     * @param $value
     * @param $key
     */
    private static function i($array, $value, &$key) {
        foreach($array as $current_key => $current_item) {
            $key .= "[$current_key]";

            if($value == $current_item) {
                array_push(ComplexArraySearchArray::$found, $key);
            } else {
                if(is_array($current_item)) {
                    ComplexArraySearchArray::i($current_item, $value, $key);
                }
            }

            $key = substr($key, 0, strrpos($key, '['));
        }
    }
}