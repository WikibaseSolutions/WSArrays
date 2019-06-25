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

        return self::arrayPrint($name, $options, $map, $subject);
    }

    /**
     * @param $name
     * @param string $options
     * @param string $map
     * @param string $subject
     * @return array|mixed|null|string|string[]
     */
    private static function arrayPrint($name, $options = '', $map = '', $subject = '') {
        self::$name = $name;
        self::$map = $map;

        if($subject) self::$subject = $subject;

        $ca_undefined_array = wfMessage('ca-undefined-array');
        if(!self::$array = GlobalFunctions::getArrayFromArrayName($name)) return GlobalFunctions::error($ca_undefined_array);

        if(!empty($options)) {
            GlobalFunctions::serializeOptions($options);

            $result = self::applyOptions($options);
        } else {
            $result = self::createList();
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
                return self::applyMapping();
                break;
            case 'markup':
            case 'wson':
                return self::ArrayToWSON(self::$array);
                break;
            default:
                return self::createList($options);
                break;
        }
    }

    /**
     * @return array|mixed|null|string
     */
    private static function applyMapping() {
        $ca_omitted = wfMessage('ca-omitted', 'Mapping');
        if(!self::$map) return GlobalFunctions::error($ca_omitted);

        $ca_map_multidimensional = wfMessage('ca-map-multidimensional');
        if(GlobalFunctions::containsArray(self::$array)) return GlobalFunctions::error($ca_map_multidimensional);

        if(count(self::$array) === 1) {
            return self::mapValue(self::$array);
        }

        $result = null;
        foreach(self::$array as $value) {
            $result .= self::mapValue($value);
        }

        return $result;
    }

    /**
     * @param $value
     * @return mixed
     */
    private static function mapValue($value) {
        return str_replace(self::$subject, $value, self::$map);
    }

    /**
     * Create an (un)ordered list from an array.
     *
     * @param string $type
     * @return array|null|string
     */
    private static function createList($type = "unordered") {
        if(count(self::$array) === 1 && !GlobalFunctions::containsArray(self::$array)) {
            return self::$array;
        }

        if($type == "ordered") self::$indent_char = "#";
        $indent_char = self::$indent_char;

        $result = null;

        foreach(self::$array as $key => $value) {
            if(!is_array($value)) {
                if(!is_numeric($key)) {
                    $result .= "$indent_char $key: $value\n";
                } else {
                    $result .= "$indent_char $value\n";
                }
            } else {
                $result .= "$indent_char ".strval($key)."\n";

                self::addArrayToUnorderedList($value, $result);
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

        $indent_char = self::$indent_char;

        foreach($array as $key => $value) {
            $indent = str_repeat("$indent_char", $depth + 1);

            if(is_array($value)) {
                $result .= "$indent ".strval($key)."\n";
                self::addArrayToUnorderedList($value, $result, $depth);
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