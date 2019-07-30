<?php

/**
 * Class ComplexArraySearch
 *
 * Defines the parser function {{#complexarraysearch:}}, which allows users to get search in an array.
 *
 * @extends WSArrays
 */
class ComplexArraySearch extends WSArrays {
    private static $found = 0;
    private static $key = 0;

    /**
     * @param Parser $parser
     * @param string $name
     * @param string $value
     * @return array
     */
    public static function defineParser( Parser $parser, $name = '', $value = '' )
    {
        GlobalFunctions::fetchSemanticArrays();

        if (empty($name)) {
            $ca_omitted = wfMessage('ca-omitted', 'Name');

            return GlobalFunctions::error($ca_omitted);
        }

        if (empty($value)) {
            $ca_omitted = wfMessage('ca-omitted', 'Value');

            return GlobalFunctions::error($ca_omitted);
        }

        return ComplexArraySearch::arraySearch($name, $value);
    }

    /**
     * @param $name
     * @param $value
     * @return array|int
     */
    private static function arraySearch($name, $value)
    {
        if (!WSArrays::$arrays[$name]) {
            $ca_undefined_array = wfMessage('ca-undefined-array');

            return GlobalFunctions::error($ca_undefined_array);
        }

        ComplexArraySearch::findValue($value, $name);

        return ComplexArraySearch::$key;
    }

    /**
     * @param $value
     * @param $key
     * @return int
     */
    private static function findValue($value, $key) {
        $array = GlobalFunctions::getArrayFromArrayName($key);

        ComplexArraySearch::i($array, $value, $key);
    }

    /**
     * @param $array
     * @param $value
     * @param $key
     */
    private static function i($array, $value, &$key) {
        if(ComplexArraySearch::$found === 1) return;

        foreach($array as $current_key => $current_item) {
            $key .= "[$current_key]";

            if($value == $current_item) {
                ComplexArraySearch::$key = $key;

                ComplexArraySearch::$found = 1;
            } else {
                if(is_array($current_item)) {
                    ComplexArraySearch::i($current_item, $value, $key);
                }

                $key = substr($key, 0, strrpos($key, '['));
            }
        }
    }
}