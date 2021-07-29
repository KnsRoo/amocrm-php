<?php

namespace AmoCRM\Models;

use ArrayAccess;
use AmoCRM\Exception;
use AmoCRM\Helpers\Format;
use AmoCRM\Request\Request;

/**
 * Class AbstractModel
 *
 * Абстрактный класс для всех моделей
 *
 * @package AmoCRM\Models
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class AbstractModel extends Request implements ModelInterface
{
    protected $name = '';
    protected $path = '';
    /**
     * @var array Список доступный полей для модели (исключая кастомные поля)
     */
    protected $fields = [];
    /**
     * @var array Список GET параметров
     */
    protected $params = [];

    /**
     * @var array Список доступный полей для параметра with
     */
    protected $avaliable_with_params = [];

    /**
     * @var array Список правил для полей модели
     */
    protected $rules = [];

    /**
     * Возвращает название Модели
     *
     * @return mixed
     */

    public function __toString()
    {
        return static::class;
    }

    public function find(Array $params = []){
        foreach ($params as $key => $value) {
            $this->params['filter['.$key.']'] = $value;
        }

        return $this;
    }

    public function with(Array $params = []){
        $this->params = array_merge($this->params,$this->setWithParams($params, $this->avaliable_params));
        return $this;
    }

    public function limit($limit = 250){
        $this->params['limit'] = $limit;
        return $this; 
    }

    public function page($page = 1){
        $this->params['page'] = $page;
        return $this;
    }

    public function order($field, $sort){
        if (!in_array($sort,['asc','desc'])){
            throw new Exception('unsupported sort type');
        }
        if (!in_array($field, $this->avaliable_order_params)){
            throw new Exception('unsupported field type');
        }
        $this->params['order['.$field.']' ] = $sort;
        return $this;
    }

    public function save(){
        $parameters = $this->getValues();
        $response = null;

        if (method_exists($this, 'id') && (!empty($this->id))){
            $response = $this->patchRequest('/api/v4/'.$this->path, [$parameters]);
        } else {
            $response = $this->postRequest('/api/v4/'.$this->path, [$parameters]);
        }

        return $response['_embedded'][$this->name][0]['id'] ? $response['_embedded'][$this->name][0]['id'] : false;
    }

    public function getAll(){
        $entities = [];
        $page = 1;
        while (true) {
            $this->page($page);
            $response = $this->getRequest('/api/v4/'.$this->path, $this->params);
            if (!$response) break;
            $entities = array_merge($entities, $response['_embedded'][$this->name]);
            $page++;
        } 

        $result = [];

        foreach ($entities as $item) {
            $result[] = $this->getModelFromData($item);
        }

        return $result;
    }

    public function get(){
        $response = $this->getRequest('/api/v4/'.$this->path, $this->params, null);
        $result = [];

        if (isset($response['_embedded'][$this->name])){
            foreach ($response['_embedded'][$this->name] as $item) {
                $result[] = $this->getModelFromData($item);
            }
        }

        return $result;
    }

    public function first(){
        $this->params['limit'] = 1;
        $response = $this->getRequest('/api/v4/'.$this->name, $this->params, null);
        $result = null;

        if (isset($response['_embedded'][$this->name]) && (!empty($response['_embedded'][$this->name]))){
            $result = $this->getModelFromData($response['_embedded'][$this->name][0]);
        }

        return $result;
    }

    public function getValues(){
        $parameters = [];

        foreach ($this->fields as $field) {
            if (method_exists($this, $field)){
                $parameters[$field] = $this->$field;
            }
        }

        return $parameters;
    }

    public function asArray(){
        $result = [];

        foreach ($this->fields as $field) {
            if (method_exists($this, $field)){
                $result[$field] = $this->$field;
            }
        }

        return $result;
    }

    protected function getModelFromData($data){
        $className = get_class($this);
        $model = new $className($this->parameters, $this->curlHandle);
        foreach ($data as $field => $value) {
            $model->$field = $value; 
        }        
        return $model;
    }

    protected function setWithParams($params, $available){

        if (!array_key_exists('with', $params)){
            $params['with'] = [];
        }

        if (!empty($params)){
            $params = $params['with'];

            if (in_array('all', $params)){
                $params = $this->available;
            }
            $params = [ 'with' => implode(',', $params)];
        }
        return $params;
    }

    public function __set($field, $value){
        if (method_exists($this, 'id') && !empty($this->id)){
            return new Exception('id is readonly');
        }

        if (in_array($field, $this->fields)){
            $this->$field = $value;
        } else{
            return new Exception('field is not exists');
        }
    }

    public function __get($field){

        if (method_exists($this, $field)){
            return call_user_func([$this, $field]);
        } else {
            return new Exception('field is not exists');
        }

        return null;
    }

    /**
     * Удаляет поле модели
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $field Название поля для удаления
     */
    public function __unset($field)
    {
        unset($this->$field);
    }

    /**
     * Добавление кастомного поля модели
     *
     * @param int $id Уникальный идентификатор заполняемого дополнительного поля
     * @param mixed $value Значение заполняемого дополнительного поля
     * @param mixed $enum Тип дополнительного поля
     * @param mixed $subtype Тип подтипа поля
     * @return $this
     */
    public function addCustomField($id, $value, $enum = false, $subtype = false)
    {
        $field = [
            'id' => $id,
            'values' => [],
        ];

        if (!is_array($value)) {
            $values = [[$value, $enum]];
        } else {
            $values = $value;
        }

        foreach ($values as $val) {
            list($value, $enum) = $val;

            $fieldValue = [
                'value' => $value,
            ];

            if ($enum !== false) {
                $fieldValue['enum'] = $enum;
            }

            if ($subtype !== false) {
                $fieldValue['subtype'] = $subtype;
            }

            $field['values'][] = $fieldValue;
        }

        $this->values['custom_fields'][] = $field;

        return $this;
    }

    /**
     * Добавление кастомного поля типа мультиселект модели
     *
     * @param int $id Уникальный идентификатор заполняемого дополнительного поля
     * @param mixed $values Значения заполняемого дополнительного поля типа мультиселект
     * @return $this
     */
    public function addCustomMultiField($id, $values)
    {
        $field = [
            'id' => $id,
            'values' => [],
        ];

        if (!is_array($values)) {
            $values = [$values];
        }

        $field['values'] = $values;

        $this->values['custom_fields'][] = $field;

        return $this;
    }

    /**
     * Проверяет ID на валидность
     *
     * @param mixed $id ID
     * @return bool
     * @throws Exception
     */
    protected function checkId($id)
    {
        if (intval($id) != $id || $id < 1) {
            throw new Exception('Id must be integer and positive');
        }

        return true;
    }
}
