<?php

/**
 *
 * Initialization file for WSArrays.
 *
 * @license GPL-2.0-or-later
 * @version: 1.1.0
 *
 * @author Xxmarijnw <marijn@wikibase.nl>
 *
 */

if (!defined( 'MEDIAWIKI' ) ) {
    die();
} else {
    global $wgVersion;
    global $wfSkipVersionControl;

    if(!$wfSkipVersionControl) {
        if(version_compare($wgVersion, 1.27) < 0) {
            if(function_exists('wfMessage')) {
                $ca_unsupported_version = wfMessage('ca-unsopported-version', 'MediaWiki', $wgVersion, '1.27');
            } else {
                $ca_unsupported_version = "This version of MediaWiki is not supported by WSArrays (has version ".$wgVersion.", requires at least version 1.27)";
            }

            throw new Exception($ca_unsupported_version);
        }

        if(version_compare(PHP_VERSION, 5.4) < 0) {
            if(function_exists('wfMessage')) {
                $ca_unsupported_version = wfMessage('ca-unsopported-version', 'PHP', PHP_VERSION, '5.4');
            } else {
                $ca_unsupported_version = "This version of PHP is not supported by WSArrays (has version ".PHP_VERSION.", requires at least version 5.4)";
            }

            throw new Exception($ca_unsupported_version);
        }
    }
}

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
    const INCLUDE_DIR = 'classes/';
    const VERSION = '1.1.0';

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
        if(is_numeric($wfMaxDefinedArrays) && $wfMaxDefinedArrays >= 0) {
            self::$options['max_defined_arrays'] = $wfMaxDefinedArrays;
        }

        try {
            spl_autoload_register('WSArrays::autoload');

            $function_hooks = [
                [
                    "class" => "ComplexArrayDefine",
                    "hooks" => [
                        "complexarraydefine",
                        "cadefine"
                    ]
                ],
                [
                    "class" => "ComplexArrayPrint",
                    "hooks" => [
                        "complexarrayprint",
                        "caprint"
                    ]
                ],
                [
                    "class" => "ComplexArraySize",
                    "hooks" => [
                        "complexarraysize",
                        "casize"
                    ]
                ],
                [
                    "class" => "ComplexArraySearch",
                    "hooks" => [
                        "complexarraysearch",
                        "casearch"
                    ]
                ],
                [
                    "class" => "ComplexArraySearchArray",
                    "hooks" => [
                        "complexarraysearcharray",
                        "casearcharray",
                        "casearcha"
                    ]
                ],
                [
                    "class" => "ComplexArrayAddValue",
                    "hooks" => [
                        "complexarrayaddvalue",
                        "caaddvalue",
                        "caadd",
                        "caaddv"
                    ]
                ],
                [
                    "class" => "ComplexArraySlice",
                    "hooks" => [
                        "complexarrayslice",
                        "caslice"
                    ]
                ],
                [
                    "class" => "ComplexArrayUnique",
                    "hooks" => [
                        "complexarrayunique",
                        "caunique"
                    ]
                ],
                [
                    "class" => "ComplexArrayReset",
                    "hooks" => [
                        "complexarrayreset",
                        "careset",
                        "caclear"
                    ]
                ],
                [
                    "class" => "ComplexArrayMerge",
                    "hooks" => [
                        "complexarraymerge",
                        "camerge"
                    ]
                ],
                [
                    "class" => "ComplexArraySort",
                    "hooks" => [
                        "complexarraysort",
                        "casort"
                    ]
                ],
                [
                    "class" => "ComplexArrayMapTemplate",
                    "hooks" => [
                        "complexarraymaptemplate",
                        "camaptemplate",
                        "camapt",
                        "catemplate"
                    ]
                ],
                [
                    "class" => "ComplexArrayPushValue",
                    "hooks" => [
                        "complexarraypushvalue",
                        "complexarraypush",
                        "capush"
                    ]
                ],
                [
                    "class" => "ComplexArrayExtract",
                    "hooks" => [
                        "complexarrayextract",
                        "caextract"
                    ]
                ],
                [
                    "class" => "ComplexArrayPushArray",
                    "hooks" => [
                        "complexarraypusharray",
                        "capusharray"
                    ]
                ],
            ];

            $sfh_function_hooks = [
                [
                    "class" => "ComplexArrayMap",
                    "hooks" => [
                        "complexarraymap",
                        "camap"
                    ]
                ]
            ];

            WSArrays::setHooks($parser, $function_hooks);
            WSArrays::setSFHHooks($parser, $sfh_function_hooks);
        } catch(Exception $e) {
            return false;
        }

        return true;
    }

    protected static function setHooks(Parser $parser, $function_hooks) {
        if(!is_array($function_hooks)) return false;

        foreach($function_hooks as $function_hook) {
            $class = $function_hook['class'];
            $hooks = $function_hook['hooks'];

            foreach($hooks as $hook) {
                $parser->setFunctionHook( $hook, [$class, 'defineParser'] );
            }
        }

        return true;
    }

    protected static function setSFHHooks( Parser $parser, $sfh_function_hooks) {
        if(!is_array($sfh_function_hooks)) return false;

        foreach($sfh_function_hooks as $sfh_function_hook) {
            $class = $sfh_function_hook['class'];
            $hooks = $sfh_function_hook['hooks'];

            foreach($hooks as $hook) {
                $parser->setFunctionHook( $hook, [$class, 'defineParser'], Parser::SFH_OBJECT_ARGS );
            }
        }

        return true;
    }

    protected static function autoload($class) {
        $file = __DIR__ . '/' . WSArrays::INCLUDE_DIR . $class . '.class.php';

        if(file_exists($file)) {
            require $file;
        }
    }

}
