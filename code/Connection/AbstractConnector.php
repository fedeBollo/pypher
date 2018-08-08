<?php

namespace Connection;
use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Bolt\Configuration;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use Pypher\PypherClient;
require_once "/Applications/MAMP/htdocs/pypher/vendor/autoload.php";

abstract class AbstractConnector {

    const HTTP_PORT = 7474;
    const BOLT_PORT = 7684;

    private $_password;
    private $_address;
    private $_port;
    private $_client;
    private $_configConnection = null;

    public function __construct(){
        list($pwd,$adr) = self::getConfig();
        $this->setConfigValue($pwd,$adr);
        return $this;
    }

    public function __get($attr) {
        return (property_exists($this, $attr)) ? $this->$attr : null;
    }

    public function __set($attr, $val) {
        return (property_exists($this, $attr)) ? $this->$attr = $val : null;
    }

    /**
     * @return PypherClient
     * @throws \PypherException
     */
    public function _startConnectionHttp(){
        if(!isset($this->_client)){
            $this->_port = self::HTTP_PORT;
            return PypherClient::createBuilder($this->_password, $this->_address, $this->_port);
        } else {
            throw new \PypherException("Client already set!");
        }
    }

    /**
     * @return PypherClient
     * @throws \PypherException
     */
    public function _startConnectionBolt(){
        if(!isset($this->_client)){
            $this->_port = self::BOLT_PORT;
            return PypherClient::createBuilder($this->_password, $this->_address, $this->_port);
        } else {
            throw new \PypherException("Client already set!");
        }
    }


    public function setConfigValue($pwd,$adr){
        $this->_password = $pwd;
        $this->_address = $adr;
    }

    public function setConfigConfiguration($credentials = null, $TLSmode = Configuration::TLSMODE_REQUIRED){
        $config = Configuration::newInstance();
        if($credentials != null){
            $config->withCredentials($credentials["username"],$credentials["password"]);
        }
        $config->withTLSMode($TLSmode);
        $this->_configConnection = $config;
        return $this;
    }

    public static function getConfig(){
        $_configuration = json_decode(file_get_contents(BP."/etc/config.json"), true);
        if(isset($_configuration["password"]) && isset($_configuration["address"])){
            return $_configuration;
        }
        return null;
    }
}