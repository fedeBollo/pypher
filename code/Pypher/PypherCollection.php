<?php

namespace Pypher\Collection;

use GraphAware\Neo4j\Client\Formatter\RecordView;
use Pypher\Node\PypherNode;
use Pypher\PypherClient;
use GraphAware\Bolt\Result;
use \GraphAware\Common\Result\Record;
use \GraphAware\Neo4j\Client\Formatter\Type\Node;
use \GraphAware\Neo4j\Client\Formatter\Type\Relationship;
use Pypher\Relationship\PypherRelationship;

class PypherCollection extends RecordView {


    private $_instances = array();
    private $_records   = array();
    private $_values    = array();

    public function __construct(PypherClient $client,array $_records) {
        foreach ($client->getParameters() as $parameter) {
            $this->hydrate($parameter, $_records);
        }
        $this->_records = $_records;
        parent::__construct($client->getParameters(), $this->values);
        return $this;
    }

    /**
     * @return PypherNode[]
     */
    public function getNodes() {
        return $this->_instances["nodes"];
    }

    public function getRelations() {
        return $this->_instances["relations"];
    }

    public function getInstances() {
        return $this->_instances;
    }

    /**
     * @return Record[]
     */
    public function getRecord() {
        return $this->_records;
    }

    /**
     * @param int    $identity
     * @param int    $startNodeIdentity
     * @param int    $endNodeIdentity
     * @param string $type
     * @param array  $properties
     */


    private function hydrate($parameter, $_records){
        /** @var Record $_record */
        foreach ($_records as $_record){
            $_instance = $_record->get($parameter);
            if( null !== $_instance ){
                $this->_values[] = $_record->values();
                $_obj = null;
                switch ($_instance) {
                    case is_a($_instance,Node::class):
                        /** @var Node $_instance */
                        $_obj = new PypherNode(
                            $_instance->identity(),
                            $_instance->labels(),
                            $_instance->properties);
                        $this->_instances["node"][$parameter] = $_obj;
                        break;
                    case is_a($_instance, Relationship::class):
                        /** @var Relationship $_instance */
                        $_obj = new PypherRelationship(
                            $_instance->identity(),
                            $_instance->startNodeIdentity(),
                            $_instance->endNodeIdentity(),
                            $_instance->type(),
                            $_instance->properties
                        );
                        $this->_instances["relations"][$parameter] = $_obj;
                        break;
                    default:
                        null;
                }
            }
        }
    }


}