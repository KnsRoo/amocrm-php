<?php
namespace AmoCRM\Collections;

use AmoCRM\Request\Request;
use AmoCRM\Request\ParamsBag;

class Collection extends Request
{
    private $path;
    private $items;

    public function __construct(ParamsBag $parameters, $curlHandle, String $class, Array $items){
        $this->parameters = $parameters;
        $this->curlHandle = $curlHandle;
        $this->path = $class;
        $this->items = $items;
    }

    public function push(CatalogElement $element){
        $this->items[] = $element;
    }

    public function extend(Array $items){
        $this->items = array_merge($this->items, $items);
    }

    public function save(){
        $items2create = [];

        foreach ($this->items as $item) {
            $items2create[] = $item->getValues();
        }

        $response = $this->postRequest('/api/v4/'.$this->path, $items2create);

        return $response;
    }

    public function update(){
        $items2update = [];

        foreach ($this->items as $item) {
            $items2update[] = $item->getValues();
        }

        $response = $this->patchRequest('/api/v4/'.$path, $items2update);

        return $response;
    }
}