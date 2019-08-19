<?php

/**
 * Class ExtensionFactory
 */
class ExtensionFactory extends WSArrays {
    /**
     * @var string
     */
    private static $extension_dir = '';

    /**
     * @param Parser $parser
     * @return bool
     */
    public static function loadExtensions( Parser &$parser ) {
        require_once("Extension.class.php");

        ExtensionFactory::$extension_dir = dirname( dirname( __FILE__ ) ) . '/extensions';

        spl_autoload_register("ExtensionFactory::autoload");

        $handles = glob(ExtensionFactory::$extension_dir . '/*.php');
        if ( !is_array( $handles ) ) {
            return false;
        }

        foreach ( $handles as $extension ) {
            if ( is_file($extension) ) {
                ExtensionFactory::loadExtension( $parser, $extension );
            }
        }

        return true;
    }

    /**
     * @param Parser $parser
     * @param $extension
     * @return bool
     */
    private static function loadExtension( Parser &$parser, $extension ) {
        $class_file = basename( $extension );
        $class = pathinfo($class_file, PATHINFO_FILENAME);

        // Is this actually an extension?
        if ( get_parent_class($class) !== "Extension" ) {
            return false;
        }

        $object = new $class();

        $parser_name = $object->getName();
        $parser_aliases = $object->getAliases();
        $parser_type = $object->getType();

        ExtensionFactory::setHook( $parser, $class, $parser_name, $parser_aliases, $parser_type );

        return true;
    }

    /**
     * @param Parser $parser
     * @param $class
     * @param $parser_name
     * @param array $parser_aliases
     * @param $parser_type
     */
    private static function setHook( Parser &$parser, $class, $parser_name, array $parser_aliases = array(), $parser_type = 'normal' ) {
        if($parser_type === 'sfh') {
            $parser->setFunctionHook( $parser_name, [ $class, 'getResult' ], Parser::SFH_OBJECT_ARGS );
        } else {
            $parser->setFunctionHook( $parser_name, [ $class, 'getResult' ] );
        }

        if ( count( $parser_aliases ) > 0 ) {
            foreach ( $parser_aliases as $alias ) {
                if($parser_type === 'sfh') {
                    $parser->setFunctionHook( $alias, [ $class, 'getResult' ], Parser::SFH_OBJECT_ARGS );
                } else {
                    $parser->setFunctionHook( $alias, [ $class, 'getResult' ] );
                }
            }
        }
    }

    /**
     * @param $class
     */
    protected static function autoload( $class ) {
        $file = ExtensionFactory::$extension_dir . '/' . $class . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}