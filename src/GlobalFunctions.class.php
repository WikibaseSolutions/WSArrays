<?php

/**
 * Class GlobalFunctions
 *
 * Grandfather class. These functions are available in all other classes.
 */
class GlobalFunctions
{
    /**
     * Print an error message.
     *
     * @param $message
     * @return array
     */
    public static function error($message) {
        $params = func_get_args();
        array_shift( $params );

        $msgHtml = Html::rawElement(
            'span',
            array( 'class' => 'error' ),
            wfMessage( $message, $params )->toString()
        );

        return array( $msgHtml, 'noparse' => true, 'isHTML' => false );
    }

    /**
     * Check if the given string $json is valid JSON.
     *
     * @param $json
     * @return bool
     */
    public static function isValidJSON($json) {
        json_decode($json);

        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Convert WSON (custom JSON) to JSON.
     *
     * @param $wson
     */
    public static function WSONtoJSON(&$wson) {
        $wson = preg_replace("/(?!\B\"[^\"]*)\(\((?![^\"]*\"\B)/i", "{", $wson);
        $wson = preg_replace("/(?!\B\"[^\"]*)\)\)(?![^\"]*\"\B)/i", "}", $wson);
    }

    /**
     * Convert JSON to WSON.
     *
     * @param $json
     */
    public static function JSONtoWSON(&$json) {
        $json = preg_replace("/(?!\B\"[^\"]*){(?![^\"]*\"\B)/i", "((", $json);
        $json = preg_replace("/(?!\B\"[^\"]*)}(?![^\"]*\"\B)/i", "))", $json);
    }

    /**
     * Convert an array to WSON.
     *
     * @param $array
     * @return null|string|string[]
     */
    public static function ArrayToWSON($array) {
        $json = json_encode($array);

        $wson = preg_replace("/(?!\B\"[^\"]*){(?![^\"]*\"\B)/i", "((", $json);
        return preg_replace("/(?!\B\"[^\"]*)}(?![^\"]*\"\B)/i", "))", $wson);
    }

    /**
     * Create an array from a comma-separated list.
     *
     * @param $options
     */
    public static function serializeOptions(&$options) {
        $options = explode(",", $options);
    }

    /**
     * Check if an array contains a subarray.
     *
     * @param $array
     * @return bool
     */
    public static function containsArray($array) {
        if(!is_array($array)) {
            return false;
        }

        foreach($array as $value) {
            if(is_array($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the contents of a subarray based on the name (basearray[subarray][subarray]...).
     *
     * @param $name
     * @return bool|array
     */
    public static function getArrayFromArrayName($name) {
        if(!strpos($name, "[")) {
            if(WSArrays::$arrays[$name]) return WSArrays::$arrays[$name];
        } else {
            $base_array = GlobalFunctions::calculateBaseArray($name);

            if(!isset(WSArrays::$arrays[$base_array])) {
                $ca_undefined_array = wfMessage('ca-undefined-array');

                return GlobalFunctions::error($ca_undefined_array);
            }

            if(preg_match_all("/(?<=\[).+?(?=\])/", $name, $matches) === 0) {
                return false;
            }

            $array = WSArrays::$arrays[$base_array];

            foreach($matches[0] as $match) {
                if(ctype_digit($match)) $match = intval($match);

                $current_array = $array;
                foreach($array as $key => $value) {
                    if($key === $match) {
                        $array = $value;
                        break;
                    }
                }

                if($current_array === $array) {
                    return false;
                }
            }

            return $array;
        }

        return false;
    }

    /**
     * Find the max depth of a multidimensional array.
     *
     * @param $array
     * @param int $depth
     * @return int|mixed
     */
    public static function arrayMaxDepth($array, $depth = 0) {
        $max_sub_depth = 0;
        foreach (array_filter($array, 'is_array') as $subarray) {
            $max_sub_depth = max(
                $max_sub_depth,
                self::arrayMaxDepth($subarray, $depth + 1)
            );
        }

        return $max_sub_depth + $depth;
    }

    /**
     * Checks whether the maximum number of arrays as defined by $wfMaxDefinedArrays has been reached.
     *
     * @return bool
     */
    public static function definedArrayLimitReached() {
        if(WSArrays::$options['max_defined_arrays'] !== -1) {
            if(count(WSArrays::$arrays) + 1 > WSArrays::$options['max_defined_arrays']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch any arrays defined by Semantic MediaWiki.
     *
     * Semantic MediaWiki stores all ComplexArrays in the configuration parameter $wfDefinedArraysGlobal. In order to allow access to these array, we need to move them to WSArrays::$arrays.
     *
     * @return void
     */
    public static function fetchSemanticArrays() {
        global $wfDefinedArraysGlobal;
        if($wfDefinedArraysGlobal !== null) {
            WSArrays::$arrays = array_merge(WSArrays::$arrays, $wfDefinedArraysGlobal);
        }
    }

    /**
     * Get the name of the base array from a full name.
     *
     * @param $array
     * @return string
     */
    public static function calculateBaseArray($array) {
        return strtok($array, "[");
    }

    public static function isValidArrayName($name) {
        return (ctype_alnum(trim($name)));
    }
}