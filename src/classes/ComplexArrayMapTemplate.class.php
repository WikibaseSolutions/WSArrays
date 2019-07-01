<?php

/**
 * Class ComplexArrayMapTemplate
 *
 * Defines the parser function {{#complexarraymaptemplate:}}, which allows users to map a multidimensional array to a list of templates.
 *
 * @extends WSArrays
 */
class ComplexArrayMapTemplate extends WSArrays
{
    /**
     * Define all allowed parameters.
     *
     * @param Parser $parser
     * @param string $name
     * @param string $template
     * @param string $options
     * @return array
     */
    public static function defineParser( Parser $parser, $name = '', $template = '', $options = '') {
        GlobalFunctions::fetchSemanticArrays();

        if(empty($name)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Name' );

            return GlobalFunctions::error($ca_omitted);
        }

        if(empty($template)) {
            $ca_omitted = wfMessage( 'ca-omitted', 'Template' );

            return GlobalFunctions::error($ca_omitted);
        }

        return ComplexArrayMapTemplate::arrayMapTemplate($name, $template, $options);
    }

    /**
     * @param $name
     * @param $template
     * @param $options
     * @return array
     */
    private static function arrayMapTemplate($name, $template, $options = '') {
        $base_array = GlobalFunctions::calculateBaseArray($name);

        if(!isset(WSArrays::$arrays[$base_array])) {
            $ca_undefined_array = wfMessage('ca-undefined-array');

            return GlobalFunctions::error($ca_undefined_array);
        }

        $array = GlobalFunctions::getArrayFromArrayName($name);

        if(!$array) {
                $ca_undefined_array = wfMessage('ca-undefined-array');

                return GlobalFunctions::error($ca_undefined_array);
        }

        $return = null;
        if(GlobalFunctions::containsArray($array) && $options !== "condensed") {
            foreach($array as $value) {
                ComplexArrayMapTemplate::map($value, $return, $template);
            }
        } else {
            ComplexArrayMapTemplate::map($array, $return, $template);
        }

        return array($return, "noparse" => false);
    }

    /**
     * @param $value
     * @param $return
     * @param $template
     */
    private static function map($value, &$return, $template) {
        $t = "{{".$template;

        if(is_array($value)) {
            foreach($value as $key => $subvalue) {
                if(is_array($subvalue)) {
                    $json = json_encode($subvalue);
                    GlobalFunctions::JSONtoWSON($json);

                    $subvalue = $json;
                }

                $t .= "|$key=$subvalue";
            }
        } else {
            $t .= "|$value";
        }

        $return .= $t."}}";
    }
}