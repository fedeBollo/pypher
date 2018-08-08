<?php

namespace Pypher\Node;

use GraphAware\Bolt\Result\Type\Node;

class PypherNode extends Node{

    public function getData($data = null) {
        if(null == $data){
            return $this->values();
        }
        return $this->value($data);
    }

    public function __call($name, $arguments) {
       if(strpos($name,"get") && strlen($name) > 3){
           $_paramName = substr($name,3);
           return $this->value($_paramName);
       }
       return null;
    }

}