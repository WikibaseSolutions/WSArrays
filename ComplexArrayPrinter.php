<?php

/*
 *
 * PLACE THIS FILE IN THE FOLDER "{mediawiki_dir}/extensions/SemanticMediaWiki/src/Query/ResultPrinters"
 *
 */

namespace SMW\Query\ResultPrinters;

/**
 * Class ComplexArrayPrinter
 * @package SMW\Query\ResultPrinters
 * @extends ResultPrinter
 */
class ComplexArrayPrinter extends ResultPrinter {
    private $name;
    private $unassociative;
    private $hide_meta;

    /**
     * Define the name of the format.
     *
     * @return string
     */
    public function getName() {
        return 'complexarray';
    }

    /**
     * @param array $definitions
     * @return array
     */
    public function getParamDefinitions( array $definitions ) {
        $definitions = parent::getParamDefinitions($definitions);

        $definitions[] = [
            'name' => 'name',
            'message' => 'ca-smw-paramdesc-name',
            'default' => ''
        ];

        $definitions[] = [
            'name' => 'hide',
            'message' => 'ca-smw-paramdesc-hide',
            'default' => 'false'
        ];

        $definitions[] = [
            'name' => 'unassociative',
            'message' => 'Converts the named keys to integer keys',
            'default' => 'false'
        ];

        return $definitions;
    }

    /**
     * @param \SMWQueryResult $queryResult
     * @param $outputMode
     * @return bool|string
     */
    protected function getResultText( \SMWQueryResult $queryResult, $outputMode ) {
        return $this->buildContents( $queryResult );
    }

    /**
     * @param \SMWQueryResult $queryResult
     * @return bool|string
     */
    private function buildContents( \SMWQueryResult $queryResult ) {
        global $wfDefinedArraysGlobal;

        $this->name = $this->params['name'];
        $this->hide_meta = filter_var($this->params['hide'], FILTER_VALIDATE_BOOLEAN);
        $this->unassociative = filter_var($this->params['unassociative'], FILTER_VALIDATE_BOOLEAN);

        if(!$this->name) {
            $json = json_encode($this->buildResultArray( $queryResult ));

            $json = preg_replace("/(?!\B\"[^\"]*){(?![^\"]*\"\B)/i", "((", $json);
            $json = preg_replace("/(?!\B\"[^\"]*)}(?![^\"]*\"\B)/i", "))", $json);

            return $json;
        }

        $wfDefinedArraysGlobal[$this->name] = $this->buildResultArray( $queryResult );
    }

    /**
     * @param \SMWQueryResult $res
     * @return array
     */
    private function buildResultArray( \SMWQueryResult $res ) {
        /**
         *
         */
        $res = array_merge( $res->serializeToArray(), [ 'rows' => $res->getCount() ] );

        /**
         * Create an empty array that needs to be returned.
         */
        $return = [];

        foreach($res['results'] as $result) {
            $r = [];

            $printouts = $result['printouts'];

            if(count($printouts) !== 0) {
                foreach($printouts as $key => $printout) {
                    if(isset($printout[0])) {
                        switch($printout[0]) {
                            case 'f':
                                $printout[0] = 0;
                                break;
                            case 't':
                                $printout[0] = 1;
                                break;
                        }

                        if($this->unassociative) {
                            array_push($r, $printout[0]);
                        } else {
                            $r[$key] = $printout[0];
                        }
                    }
                }
            }

            if(!$this->hide_meta) {
                if(isset($result['fulltext'])) $r['catitle'] = $result['fulltext'];
                if(isset($result['fullurl'])) $r['cafullurl'] = $result['fullurl'];
                if(isset($result['namespace'])) $r['canamespace'] = $result['namespace'];
                if(isset($result['exists'])) $r['caexists'] = $result['exists'];
                if(isset($result['displaytitle'])) $r['cadisplaytitle'] = $result['displaytitle'];
            }

            array_push($return, $r);
        }

        return $return;
    }
}
