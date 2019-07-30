<?php

/**
 * Class ComplexArrayMap
 *
 * Defines the parser function {{#complexarraymap:}}, which allows users to iterate over (sub)arrays.
 *
 * @extends WSArrays
 */
class ComplexArrayMap extends WSArrays {
    /**
     * Buffer containing items to be returned.
     *
     * @var string
     */
    private static $buffer = '';

    /**
     * Variable containing the name of the array that needs to be mapped.
     *
     * @var string
     */
    private static $array = '';

    /**
     * Dynamic variable containing the key currently being worked on.
     *
     * @var string
     */
    private static $array_key = '';

    /**
     * @var bool
     */
    private static $hide = false;

    /**
     * Define parameters and initialize parser.
     *
     * @param Parser $parser
     * @param string $name
     * @param string $map_key
     * @param string $map
     * @param string $hide
     * @return array|null
     */
    public static function defineParser( Parser $parser, $name = '', $map_key = '', $map = '', $hide = '' ) {
        GlobalFunctions::fetchSemanticArrays();

        if(empty($name)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error($ca_omitted);
        }

        if(empty($map_key)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Map key' );

            return GlobalFunctions::error($ca_omitted);
        }

        if(empty($map)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Map' );

            return GlobalFunctions::error($ca_omitted);
        }

        if(!empty($hide) && $hide === "true") {
            ComplexArrayMap::$hide = true;
        }

        return ComplexArrayMap::arrayMap($name, $map_key, $map);
    }

    /**
     * @param $array_name
     * @param $map_key
     * @param $map
     * @return array|string
     */
    private static function arrayMap($array_name, $map_key, $map) {
        $base_array = GlobalFunctions::calculateBaseArray($array_name);

        if(!isset(WSArrays::$arrays[$base_array])) {
            $ca_undefined_array = wfMessage('ca-undefined-array');

            return GlobalFunctions::error($ca_undefined_array);
        }

        $array = GlobalFunctions::getArrayFromArrayName($array_name);

        if(!$array) {
            $ca_undefined_array = wfMessage('ca-undefined-array');

            return GlobalFunctions::error($ca_undefined_array);
        }

        return ComplexArrayMap::iterate($array, $map_key, $map, $array_name);
    }

    /**
     * @param $array
     * @param $map_key
     * @param $map
     * @param $array_name
     * @return string
     */
    private static function iterate($array, $map_key, $map, $array_name) {
        ComplexArrayMap::$array = $array_name;

        foreach($array as $array_key => $subarray) {
            ComplexArrayMap::$array_key = $array_key;

            $type = gettype($subarray);

            if($type !== "array") {
                switch($type) {
                    case 'string':
                    case 'integer':
                    case 'float':
                        $mapping = str_replace($map_key, $subarray, $map);

                        ComplexArrayMap::$buffer .= $mapping;
                }
            } else {
                $preg_quote = preg_quote($map_key);

                ComplexArrayMap::$buffer .= preg_replace_callback("/($preg_quote(\[[^\[\]]+\])+)/", 'ComplexArrayMap::replaceCallback', $map);
            }
        }

        return ComplexArrayMap::$buffer;
    }

    /**
     * @param $matches
     * @return array|bool
     */
    public static function replaceCallback($matches) {
        $value = ComplexArrayMap::getValueFromMatch($matches[0]);

        switch(gettype($value)) {
            case 'integer':
            case 'float':
            case 'string':
                return $value;
                break;
            default:
                if(!ComplexArrayMap::$hide) {
                    return $matches[0];
                }

                break;
        }

        return null;
    }

    private static function getValueFromMatch($match) {
        $pointer = ComplexArrayMap::getPointerFromArrayName($match);
        $array_name = ComplexArrayMap::getArrayNameFromPointer($pointer);
        $value = GlobalFunctions::getArrayFromArrayName($array_name);

        return $value;
    }

    private static function getArrayNameFromPointer($pointer) {
        return ComplexArrayMap::$array . '[' . ComplexArrayMap::$array_key . ']' . $pointer;
    }

    private static function getPointerFromArrayName($array_key) {
        return preg_replace("/[^\[]*/", "", $array_key, 1);
    }
}