<?php

/**
 * Class ComplexArrayPrint
 *
 * Defines the parser function {{#complexarrayprint:}}, which allows users to display an array in a couple of ways.
 *
 * @extends WSArrays
 */
class ComplexArrayPrint extends WSArrays
{
    /**
     * Holds the array being worked on.
     *
     * @var array
     */
    protected static $array = [];

    /**
     * Holds defined parameters.
     *
     * @var string
     */
    private static $name = null;
    private static $map = null;
    private static $subject = "@@";
    private static $indent_char = "*";

    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $name
     * @param string $options
     * @param string $map
     * @param string $subject
     * @return array|mixed|null|string|string[]
     */
    public static function defineParser( Parser $parser, $name = '', $options = '', $map = '', $subject = '') {
        GlobalFunctions::fetchSemanticArrays();

        $ca_omitted = wfMessage('ca-omitted', 'Name');
        if(empty($name)) return GlobalFunctions::error($ca_omitted);

        return ComplexArrayPrint::arrayPrint($name, $options, $map, $subject);
    }

    /**
     * @param $name
     * @param string $options
     * @param string $map
     * @param string $subject
     * @return array|mixed|null|string|string[]
     */
    private static function arrayPrint($name, $options = '', $map = '', $subject = '') {
        ComplexArrayPrint::$name = $name;
        ComplexArrayPrint::$map = $map;

        if($subject) ComplexArrayPrint::$subject = $subject;

        $ca_undefined_array = wfMessage('ca-undefined-array');
        if(!ComplexArrayPrint::$array = GlobalFunctions::getArrayFromArrayName($name)) return GlobalFunctions::error($ca_undefined_array);

        if(!empty($options)) {
            GlobalFunctions::serializeOptions($options);

            $result = ComplexArrayPrint::applyOptions($options);
        } else {
            $result = ComplexArrayPrint::createList();
        }

        return $result;
    }

    /**
     * @param $options
     * @return array|mixed|null|string|string[]
     */
    private static function applyOptions($options) {
        if(gettype($options) === "array") {
            $options = $options[0];
        }

        switch($options) {
            case 'map':
                return ComplexArrayPrint::applyMapping();
                break;
            case 'markup':
            case 'wson':
                return ComplexArrayPrint::ArrayToWSON(ComplexArrayPrint::$array);
                break;
            default:
                return ComplexArrayPrint::createList($options);
                break;
        }
    }

    /**
     * @return array|mixed|null|string
     */
    private static function applyMapping() {
        $ca_omitted = wfMessage('ca-omitted', 'Mapping');
        if(!ComplexArrayPrint::$map) return GlobalFunctions::error($ca_omitted);

        $ca_map_multidimensional = wfMessage('ca-map-multidimensional');
        if(GlobalFunctions::containsArray(ComplexArrayPrint::$array)) return GlobalFunctions::error($ca_map_multidimensional);

        if(count(ComplexArrayPrint::$array) === 1) {
            return ComplexArrayPrint::mapValue(ComplexArrayPrint::$array);
        }

        $result = null;
        foreach(ComplexArrayPrint::$array as $value) {
            $result .= ComplexArrayPrint::mapValue($value);
        }

        return $result;
    }

    /**
     * @param $value
     * @return mixed
     */
    private static function mapValue($value) {
        return str_replace(ComplexArrayPrint::$subject, $value, ComplexArrayPrint::$map);
    }

    /**
     * Create an (un)ordered list from an array.
     *
     * @param string $type
     * @return array|null|string
     */
    private static function createList($type = "unordered") {
        if(count(ComplexArrayPrint::$array) === 1 && !GlobalFunctions::containsArray(ComplexArrayPrint::$array)) {
            return ComplexArrayPrint::$array;
        }

        if($type == "ordered") ComplexArrayPrint::$indent_char = "#";
        $indent_char = ComplexArrayPrint::$indent_char;

        $result = null;

        foreach(ComplexArrayPrint::$array as $key => $value) {
            if(!is_array($value)) {
                if(!is_numeric($key)) {
                    $result .= "$indent_char $key: $value\n";
                } else {
                    $result .= "$indent_char $value\n";
                }
            } else {
                $result .= "$indent_char ".strval($key)."\n";

                ComplexArrayPrint::addArrayToUnorderedList($value, $result);
            }
        }

        return $result;
    }

    /**
     * @param $array
     * @param $result
     * @param int $depth
     */
    private static function addArrayToUnorderedList($array, &$result, $depth = 0) {
        $depth++;

        $indent_char = ComplexArrayPrint::$indent_char;

        foreach($array as $key => $value) {
            $indent = str_repeat("$indent_char", $depth + 1);

            if(is_array($value)) {
                $result .= "$indent ".strval($key)."\n";
                ComplexArrayPrint::addArrayToUnorderedList($value, $result, $depth);
            } else {
                if(!is_numeric($key)) {
                    $result .= "$indent $key: $value\n";
                } else {
                    $result .= "$indent $value\n";
                }
            }
        }
    }
}