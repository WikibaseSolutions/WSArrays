<?php

/**
 *
 * Initialization file for WSArrays.
 *
 * @license GPL-2.0-or-later
 * @version: 0.4.7
 *
 * @author Xxmarijnw <marijn@wikibase.nl>
 *
 */

if (!defined( 'MEDIAWIKI' ) ) {
    die();
};

require 'GlobalFunctions.class.php';

$GLOBALS['smwgResultFormats']['complexarray'] = 'SMW\Query\ResultPrinters\ComplexArrayPrinter';

/**
 * Class WSArrays
 *
 * Defines all parser functions.
 *
 * @extends GlobalFunctions
 */
class WSArrays extends GlobalFunctions {

    /**
     * This variable holds all defined arrays. If an array is defined called "array", the array will be stored in self::$arrays["array"].
     *
     * @var array
     */
    public static $arrays = [];

    /**
     * Holds defined options.
     *
     * @var array
     */
    public static $options = [
        "max_defined_arrays" => -1
    ];

    /**
     * This function is called on every page.
     *
     * @param Parser $parser
     * @return boolean
     * @throws Exception
     */
    public static function onParserFirstCallInit( Parser $parser ) {
        global $wfMaxDefinedArrays;
        if(is_numeric($wfMaxDefinedArrays) && $wfMaxDefinedArrays >= 0) self::$options['max_defined_arrays'] = $wfMaxDefinedArrays;

        try {
            require 'include.php';

            // complexarraydefine alias cadefine
            $parser->setFunctionHook( 'complexarraydefine', [ComplexArrayDefine::class, 'defineParser'] );
            $parser->setFunctionHook( 'cadefine', [ComplexArrayDefine::class, 'defineParser'] );

            // complexarrayprint alias caprint
            $parser->setFunctionHook( 'complexarrayprint', [ComplexArrayPrint::class, 'defineParser'] );
            $parser->setFunctionHook( 'caprint', [ComplexArrayPrint::class, 'defineParser'] );

            // complexarraysize alias casize
            $parser->setFunctionHook( 'complexarraysize', [ComplexArraySize::class, 'defineParser'] );
            $parser->setFunctionHook( 'casize', [ComplexArraySize::class, 'defineParser'] );

            // complexarraysearch alias casearch
            $parser->setFunctionHook( 'complexarraysearch', [ComplexArraySearch::class, 'defineParser'] );
            $parser->setFunctionHook( 'casearch', [ComplexArraySearch::class, 'defineParser'] );

            // complexarraysearcharray alias casearcharray, casearcha
            $parser->setFunctionHook( 'complexarraysearcharray', [ComplexArraySearchArray::class, 'defineParser'] );
            $parser->setFunctionHook( 'casearcharray', [ComplexArraySearchArray::class, 'defineParser'] );
            $parser->setFunctionHook( 'casearcha', [ComplexArraySearchArray::class, 'defineParser'] );

            // complexarrayaddvalue alias caaddvalue, caadd, caaddv
            $parser->setFunctionHook( 'complexarrayaddvalue', [ComplexArrayAddValue::class, 'defineParser'] );
            $parser->setFunctionHook( 'caaddvalue', [ComplexArrayAddValue::class, 'defineParser'] );
            $parser->setFunctionHook( 'caadd', [ComplexArrayAddValue::class, 'defineParser'] );
            $parser->setFunctionHook( 'caaddv', [ComplexArrayAddValue::class, 'defineParser'] );

            // complexarrayslice alias caslice
            $parser->setFunctionHook( 'complexarrayslice', [ComplexArraySlice::class, 'defineParser'] );
            $parser->setFunctionHook( 'caslice', [ComplexArraySlice::class, 'defineParser'] );

            // complexarraycut alias cacut
            $parser->setFunctionHook( 'complexarraycut', [ComplexArrayCut::class, 'defineParser'] );
            $parser->setFunctionHook( 'cacut', [ComplexArrayCut::class, 'defineParser'] );

            // complexarrayunique alias caunique
            $parser->setFunctionHook( 'complexarrayunique', [ComplexArrayUnique::class, 'defineParser'] );
            $parser->setFunctionHook( 'caunique', [ComplexArrayUnique::class, 'defineParser'] );

            // complexarrayreset alias careset, caclear
            $parser->setFunctionHook( 'complexarrayreset', [ComplexArrayReset::class, 'defineParser'] );
            $parser->setFunctionHook( 'careset', [ComplexArrayReset::class, 'defineParser'] );
            $parser->setFunctionHook( 'caclear', [ComplexArrayReset::class, 'defineParser'] );

            // complexarraymerge alias camerge
            $parser->setFunctionHook( 'complexarraymerge', [ComplexArrayMerge::class, 'defineParser'] );
            $parser->setFunctionHook( 'camerge', [ComplexArrayMerge::class, 'defineParser'] );

            // complexarraysort alias casort
            $parser->setFunctionHook( 'complexarraysort', [ComplexArraySort::class, 'defineParser'] );
            $parser->setFunctionHook( 'casort', [ComplexArraySort::class, 'defineParser'] );

            // complexarraymaptemplate alias camaptemplate, camapt, catemplate
            $parser->setFunctionHook( 'complexarraymaptemplate', [ComplexArrayMapTemplate::class, 'defineParser'] );
            $parser->setFunctionHook( 'camaptemplate', [ComplexArrayMapTemplate::class, 'defineParser'] );
            $parser->setFunctionHook( 'camapt', [ComplexArrayMapTemplate::class, 'defineParser'] );
            $parser->setFunctionHook( 'catemplate', [ComplexArrayMapTemplate::class, 'defineParser'] );

            // complexarraypushvalue alias complexarraypush, capush
            $parser->setFunctionHook( 'complexarraypushvalue', [ComplexArrayPushValue::class, 'defineParser'] );
            $parser->setFunctionHook( 'complexarraypush', [ComplexArrayPushValue::class, 'defineParser'] );
            $parser->setFunctionHook( 'capush', [ComplexArrayPushValue::class, 'defineParser'] );
        } catch(Exception $e) {
            return false;
        }

        return true;
    }

}
