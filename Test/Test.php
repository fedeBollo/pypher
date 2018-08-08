<?php

use GraphAware\Neo4j\Client\Connection\Connection;
use Connection\Connector;
use Pypher\PypherClient;
use PHPUnit\Util\PHP;

class Test extends PHPUnit\Framework\TestCase
{
    /**
     * @throws Exception
     * @throws \GraphAware\Neo4j\Client\Exception\Neo4jExceptionInterface
     */
    public function testExecution(){
        /**
         * @TODO implementare multiple connection con array
         *
         */


        $connector = new Connector();
        /** @var PypherClient $client */
        $client = $connector->startConnection(Connector::HTTP_TYPE);

        $statement = array(
            "name" => "usertest",
            "email" => "usertest@mail.it"
        );

        $_result = $client->createNode("User", "u", $statement)
            ->rightRelation("WRITE_TO")
            ->addNode("User", "t", $statement)
            ->runQuery("u,t");

        $_nodes = $_result->getNodes();

        foreach ($_nodes as $_node){
            $_node->getData();
            $_node->identity();
            $_node->getLogin();
        }


    }
}
