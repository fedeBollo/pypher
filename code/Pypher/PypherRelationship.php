<?php

namespace Pypher\Relationship;

use GraphAware\Bolt\Result\Type\Relationship;

class PypherRelationship extends Relationship{

    public function getData($data = null) {
        if(null == $data){
            return $this->values();
        }
        return $this->value($data);
    }

    public function __call($name, $arguments) {
        if(strpos($name,"get" && strlen($name) > 3)){
            $_paramName = substr($name,3);
            return $this->value($_paramName);
        }
        return null;
    }

}