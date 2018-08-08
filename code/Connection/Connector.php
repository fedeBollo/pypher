<?php

namespace Connection;

use GraphAware\Neo4j\Client\ClientInterface;
define("BP","/Applications/MAMP/htdocs/pypher/");
require_once "/Applications/MAMP/htdocs/pypher/vendor/autoload.php";

class Connector extends AbstractConnector {

    const HTTP_TYPE = 'http';
    const BOLT_TYPE = "bolt";

    /**
     * @param $type
     * @return ClientInterface
     * @throws \Exception
     */
    public function startConnection($type){
        switch ($type) {
            case self::HTTP_TYPE:
                return parent::_startConnectionHttp();
                break;
            case self::BOLT_TYPE:
                return parent::_startConnectionBolt();
                break;
            default:
                throw new \PypherException("Type {$type} not mapped or not exist");
        }
    }



}