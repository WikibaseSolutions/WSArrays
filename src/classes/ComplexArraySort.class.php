<?php

/**
 * Class ComplexArraySort
 *
 * Defines the parser function {{#complexarraysort:}}, which allows users to sort arrays.
 *
 * @extends WSArrays
 */
class ComplexArraySort extends WSArrays
{
    private static $key;
    private static $name;
    private static $array;

    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $name
     * @param string $options
     * @param string $key
     * @return array|null
     */
    public static function defineParser( Parser $parser, $name = '', $options = '', $key = '') {
        GlobalFunctions::fetchSemanticArrays();

        $ca_omitted = wfMessage('ca-omitted');
        if(empty($name)) return GlobalFunctions::error($ca_omitted);

        return ComplexArraySort::arraySort($name, $options, $key);
    }

    /**
     * @param $name
     * @param string $options
     * @param string $key
     * @return array|null
     */
    private static function arraySort($name, $options = '', $key = '') {
        $ca_undefined_array = wfMessage('ca-undefined-array');
        if(!isset(WSArrays::$arrays[$name])) return GlobalFunctions::error($ca_undefined_array);
        ComplexArraySort::$array = WSArrays::$arrays[$name];

        ComplexArraySort::$name = $name;
        if(!empty($key)) ComplexArraySort::$key = $key;

        if(empty($options)) {
            $result = ComplexArraySort::sortArray("sort");
        } else {
            GlobalFunctions::serializeOptions($options);

            if(count($options) === 1) {
                $result = ComplexArraySort::sortArray($options[0]);
            } else {
                if($options[0] !== "keysort") {
                    $result = ComplexArraySort::sortArray($options[0]);
                } else {
                    $result = ComplexArraySort::keysort($options[1]);
                }
            }
        }

        if($result === true) {
            return null;
        } else {
            return GlobalFunctions::error($result);
        }
    }

    /**
     * @param $algo
     * @return bool|string
     */
    private static function sortArray($algo) {
        switch($algo) {
            case 'multisort':
                $array = ComplexArraySort::multisort();
                break;
            case 'asort':
                $array = ComplexArraySort::asort();
                break;
            case 'arsort':
                $array = ComplexArraySort::arsort();
                break;
            case 'krsort':
                $array = ComplexArraySort::krsort();
                break;
            case 'natcasesort':
                $array = ComplexArraySort::natcasesort();
                break;
            case 'natsort':
                $array = ComplexArraySort::natsort();
                break;
            case 'rsort':
                $array = ComplexArraySort::rsort();
                break;
            case 'shuffle':
                $array = ComplexArraySort::shuffle();
                break;
            case 'keysort':
                $array = ComplexArraySort::keysort(null);
                break;
            case 'sort':
            default:
                $array = ComplexArraySort::sort();
                break;
        }

        if($array === true) {
            return true;
        } else {
            return $array;
        }
    }

    /**
     * Sort array using multisort
     *
     * @return bool|string
     */
    private static function multisort() {
        $ca_sort_broken = wfMessage('ca-sort-broken', 'multisort');
        if(!array_multisort(ComplexArraySort::$array)) return $ca_sort_broken;

        return true;
    }

    /**
     * Sort array using asort
     *
     * @return bool|string
     */
    private static function asort() {
        $ca_sort_broken = wfMessage('ca-sort-broken', 'asort');
        if(!asort(ComplexArraySort::$array)) return $ca_sort_broken;

        return true;
    }

    /**
     * Sort array using arsort
     *
     * @return bool|string
     */
    private static function arsort() {
        $ca_sort_broken = wfMessage('ca-sort-broken', 'arsort');
        if(!arsort(ComplexArraySort::$array)) return $ca_sort_broken;

        return true;
    }

    /**
     * Sort array using krsort
     *
     * @return bool|string
     */
    private static function krsort() {
        $ca_sort_broken = wfMessage('ca-sort-broken', 'krsort');
        if(!krsort(ComplexArraySort::$array)) return $ca_sort_broken;

        return true;
    }

    /**
     * Sort array using natcasesort
     *
     * @return bool|string
     */
    private static function natcasesort() {
        $ca_sort_broken = wfMessage('ca-sort-broken', 'natcasesort');
        if(!natcasesort(ComplexArraySort::$array)) return $ca_sort_broken;

        return true;
    }

    /**
     * Sort array using natsort
     *
     * @return bool|string
     */
    private static function natsort() {
        $ca_sort_broken = wfMessage('ca-sort-broken', 'natsort');
        if(!natsort(ComplexArraySort::$array)) return $ca_sort_broken;

        return true;
    }

    /**
     * Sort array using rsort
     *
     * @return bool|string
     */
    private static function rsort() {
        $ca_sort_broken = wfMessage('ca-sort-broken', 'rsort');
        if(!rsort(ComplexArraySort::$array)) return $ca_sort_broken;

        return true;
    }

    /**
     * Sort array using shuffle
     *
     * @return bool|string
     */
    private static function shuffle() {
        $ca_sort_broken = wfMessage('ca-sort-broken', 'shuffle');
        if(!shuffle(ComplexArraySort::$array)) return $ca_sort_broken;

        return true;
    }

    /**
     * Sort array using sort
     *
     * @return bool|string
     */
    private static function sort() {
        $ca_sort_broken = wfMessage('ca-sort-broken', 'sort');
        if(!sort(ComplexArraySort::$array)) return $ca_sort_broken;

        return true;
    }

    /**
     * Sort array using keysort
     *
     * @param $order
     *
     * @return bool|string
     */
    private static function keysort($order) {
        $ca_sort_missing_key = wfMessage('ca-sort-missing-key');
        if(!ComplexArraySort::$key) return $ca_sort_missing_key;

        $ca_sort_array_too_deep = wfMessage('ca-sort-array-too-deep');
        foreach(ComplexArraySort::$array as $value) {
            if(is_array($value[ComplexArraySort::$key])) return $ca_sort_array_too_deep;
        }

        ComplexArraySort::ksort(ComplexArraySort::$array, ComplexArraySort::$key);

        $i = 0;
        $temp = [];
        foreach(ComplexArraySort::$array as $key => $item) {
            $temp[$i] = $item;
            $i++;
        }

        ComplexArraySort::$array = $temp;

        if($order == "desc") {
            ComplexArraySort::$array = array_reverse(ComplexArraySort::$array);
        }

        WSArrays::$arrays[ComplexArraySort::$name] = ComplexArraySort::$array;

        return true;
    }

    /**
     * User-defined sorting function which sorts based on key.
     *
     * @param $array
     * @param $key
     * @return bool|string
     */
    private static function ksort(&$array, $key) {
        $sorter = array();
        $ret = array();

        reset($array);

        foreach($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }

        asort($sorter);

        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }

        $array = $ret;
    }
}