<?php

namespace Pypher;

use GraphAware\Neo4j\Client\Client;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use Pypher\Collection\PypherCollection;
use Pypher\PypherStatement\PypherStatement;

class PypherClient extends Client {

    private $_query = "";
    private $_nodes = array();
    private $_relations = array();
    private $_parameters = array();

    public static function createBuilder($adr, $pwd, $port){
        $cl = PypherClientBuilder::create()
            ->addConnection("default", "http://neo4j:{$pwd}@{$adr}:{$port}")
            ->build();
        $cl->_query = "";
        return $cl;
    }

    public function getQuery(){
        return $this->_query;
    }

    public function with($state){
        if(null !== $state){
            $this->_query .= PypherStatement::WITH.$state;
        }
    }

    public function where($state) {
        if(null !== $state){
            $this->_query .= PypherStatement::WHERE.$state;
        }
    }

    /**
     * @param string $nodeName
     * @param null $variable
     * @param array $statement
     * @return PypherClient
     */
    public function createNode(string $nodeName, $variable = null, $statement = array()) {
        $this->_query .=  PypherStatement::CREATE;
        return $this->_queryNodes($nodeName, $variable, $statement);
    }

    public function mergeNode(string $nodeName, $variable = null, $statement = array()) {
        $this->_query .= PypherStatement::MERGE;
        return $this->_queryNodes($nodeName, $variable, $statement);
    }


    public function addNode(string $nodeName, $variable = null, $statement = array()) {
        return $this->_queryNodes($nodeName, $variable, $statement);
    }

    private function _queryNodes($nodeName, $variable, $statement) {
        $_internalStatement = $this->_manageStatement($statement);
        $variable = (null !== $variable) ?: uniqid("node_");
        array_push($this->_nodes, array($variable => $nodeName));
        if (!is_null($variable)){
            $this->_query .= " ({$variable}:{$nodeName} {$_internalStatement})";
        } else {
            $this->_query .= " (:{$nodeName} {$_internalStatement})";
        }

        return $this;
    }

    public function rightRelation($relationsName = null, $variable = null, $statement = array()) {
        $this->_query .= PypherStatement::RIGHT_RELATION;
        $this->addRelation($relationsName, $variable, $statement);
        return $this;
    }

    public function leftRelation($relationsName = null, $variable = null, $statement = array()) :string {
        $this->addRelation($relationsName, $variable, $statement);
        $this->_query .= PypherStatement::LEFT_RELATION;
        return $this;
    }

    /**
     * @param null $relationsName
     * @param null $variable
     * @param array $statement
     * @return $this
     */
    public function addRelation($relationsName = null, $variable = null, $statement = array()) {
        $this->_query .= "-[";
        $variable = ($variable) ?: uniqid("relation_");
        array_push($this->_relations, array($variable => $relationsName));
        if(null !== $relationsName){
            $this->_query .= (string) $relationsName;
        }
        $_internalStatement = $this->_manageStatement($statement);
        $this->_query .= $_internalStatement . PypherStatement::END_RELATION;
        return $this;
    }

    /**
     * @param array $statement
     * @return string
     */
    private function _manageStatement(array $statement) :string {
        $_stringState = "";
        if(is_array($statement) && count($statement) > 0) {
            $_stringState = " { ";
            foreach ($statement as $_keyState => $_valueState){
                $_stringState .= " {$_keyState}: {$_valueState}";
            }
            $_stringState .= " } ";
        }
        return $_stringState;
    }

    public function clean(){
        $this->_query = "";
        return $this;
    }

    /**
     * @param null $parameters
     * @param null $tag
     * @param null $connectionAlias
     * @return PypherCollection|null
     * @throws \GraphAware\Neo4j\Client\Exception\Neo4jExceptionInterface
     */
    public function runQuery($parameters = null, $tag = null, $connectionAlias = null) {
        $this->_query .= PypherStatement::RETURN . $parameters;
        $this->_parameters = explode(",", $parameters);
        $_return =  parent::run($this->_query, $parameters, $tag, $connectionAlias);
        if($_return->size() <= 0 ){
            throw new Neo4jException("Query not produced result");
        }
        $_collection = new PypherCollection($this, $_return->records());
        $this->clean();
        return $_collection;
    }

    public function getNodes(){
        return $this->_nodes;
    }

    public function getRelations(){
        return $this->_relations;
    }

    public function getParameters(){
        return $this->_parameters;
    }

}