<?php

namespace AmoCRM\Models;

use AmoCRM\Collections\Collection;

class Product extends AbstractModel
{

    protected $fields = [
        'id',
        'catalog_id',
        'name',
        'created_by',
        'updated_by',
        'created_at',
        'updated_by',
        'sort',
        'type',
        'sku',
        'description',
        'price',
        'group',
        'is_deleted',
        'external_id',
        'account_id',
        'custom_fields_values',
    ];

    protected $avaliable_with_params = [
        'invoice_link'
    ];

    public function collection(String $catalogId, Array $items){
        foreach ($items as $item){
            $item->catalog_id = $catalogId;
        }
        return new Collection($this->parameters, $this->curlHandle,  'catalogs/'.$catalogId.'/elements', $items);
    }

}