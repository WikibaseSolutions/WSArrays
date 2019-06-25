<?php

/**
 * Class ComplexArrayMerge
 *
 * @extends WSArrays
 */
class ComplexArrayMerge extends WSArrays
{
    public static function defineParser( Parser $parser ) {
        GlobalFunctions::fetchSemanticArrays();

        // TODO: In release 0.6.0
    }
}