<?php

/**
 * Class ComplexArrayUnique
 *
 * Defines the parser function {{#complexarrayunique:}}, which allows users to remove duplicate keys or values from a (sub)array.
 *
 * @extends WSArrays
 */
class ComplexArrayUnique extends WSArrays
{
    /**
     * Define parameters and initialize parser.
     *
     * @param Parser $parser
     * @param string $name
     * @return array|null
     */
    public static function defineParser( Parser $parser, $name = '' ) {
        GlobalFunctions::fetchSemanticArrays();

        $ca_omitted = wfMessage('ca-omitted', 'Name');
        if(empty($name)) return GlobalFunctions::error($ca_omitted);

        return self::arrayUnique($name);
    }

    /**
     * @param $name
     * @return array|null
     */
    private static function arrayUnique($name) {
        $ca_undefined_array = wfMessage('ca-undefined-array');
        if(!$array = GlobalFunctions::getArrayFromArrayName($name)) return GlobalFunctions::error($ca_undefined_array);

        if(GlobalFunctions::containsArray($array)) {
            $array = array_unique($array);

            WSArrays::$arrays[$name] = $array;
        } else {
            $array = self::arrayUniqueMultidimensional($array);

            WSArrays::$arrays[$name] = $array;
        }

        return null;
    }

    /**
     * @param $array
     * @return array
     */
    private static function arrayUniqueMultidimensional($array) {
        return array_intersect_key($array, array_unique(array_map('serialize', $array)));
    }
}
