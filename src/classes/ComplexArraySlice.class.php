<?php

/**
 * Class ComplexArraySlice
 *
 * @extends WSArrays
 */
class ComplexArraySlice extends WSArrays
{
    public static function defineParser( Parser $parser ) {
        GlobalFunctions::fetchSemanticArrays();

        // TODO: In release 1.0.0
    }
}