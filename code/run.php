<?php
define("BP","/Applications/MAMP/htdocs/pypher/");
require_once BP."/code/Connection/AbstractConnector.php";
require_once BP."/code/Connection/Connector.php";
use Connection\Connector;


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