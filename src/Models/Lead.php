<?php

namespace AmoCRM\Models;

use AmoCRM\Models\Traits\SetNote;
use AmoCRM\Models\Traits\addTags;
use AmoCRM\Models\Traits\addCompany;
use AmoCRM\Models\Traits\addContact;
use AmoCRM\Models\Traits\SetDateCreate;
use AmoCRM\Models\Traits\SetLastModified;

use AmoCRM\Exception;

/**
 * Class Lead
 *
 * Класс модель для работы со Сделками
 *
 * @package AmoCRM\Models
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Lead extends AbstractModel
{
    use SetNote, addTags, addCompany, addContact;

    protected $name = 'leads';
    protected $path = 'leads';

    protected $fields = [
        'id',
        'price',
        'name',
        'responsible_user_id',
        'group_id',
        'status_id',
        'pipeline_id',
        'loss_reason_id',
        'source_id',
        'created_by',
        'created_at',
        'updated_at',
        'closest_task_at',
        'is_deleted',
        'custom_fields_values',
        'score',
        'account_id',
        'is_price_modified_by_robot',
        '_embedded',
    ];

    protected $avaliable_with_params = [
        'catalog_elements',
        'is_price_modified_by_robot',
        'loss_reason',
        'contacts',
        'only_deleted',
        'source_id'
    ];

    protected $avaliable_order_params = [
        'created_at',
        'updated_at',
        'id'
    ];

    protected $params = [];

    public static function saveCollection(Array $collection){
        $leadsForUpdate = [];
        $leadsForCreate = [];

        foreach ($collection as $item) {
            if ($item->id){
                $leadsForUpdate[] = $item->getValues();
            } else {
                $leadsForCreate[] = $item->getValues();
            }
        }

        $response1; $response2;

        if (!empty($leadsForUpdate)){
            $response1 = $this->patchRequest('/api/v4/leads', $leadsForUpdate);
        }

        if (!empty($leadsForCreate)){
            $response2 = $this->postRequest('/api/v4/leads', $leadsForCreate);
        }

        return array_merge($response1, $response2);
    }

    /**
     * Список сделок
     *
     * Метод для получения списка сделок с возможностью фильтрации и постраничной выборки.
     * Ограничение по возвращаемым на одной странице (offset) данным - 500 сделок
     *
     * @link https://developers.amocrm.ru/rest_api/leads_list.php
     * @param array $parameters Массив параметров к amoCRM API
     * @param null|string $modified Дополнительная фильтрация по (изменено с)
     * @return array Ответ amoCRM API
     */
    public function apiList($params = [], $modified = null)
    {

        $parameters = $this->setWithParams($params, $this->avaliable_params);

        if (!empty($params['page'])){
            $parameters['page'] = $params['page'];
        }

        if (!empty($params['limit'])){
            $parameters['limit'] = $params['limit'];
        }

        if (!empty($params['filter'])){
            $parameters['filter'] = $params['filter'];
        }

        if (!empty($params['order'])){
            $parameters['order'] = $params['order'];
        }

        $response = $this->getRequest('/api/v4/leads', $parameters, $modified);

        return isset($response['_embedded']['leads']) ? $response['_embedded']['leads'] : [];
    }

    /**
     * Добавление сделки
     *
     * Метод позволяет добавлять сделки по одной или пакетно
     *
     * @link https://developers.amocrm.ru/rest_api/leads_set.php
     * @param array $leads Массив сделок для пакетного добавления
     * @return int|array Уникальный идентификатор сделки или массив при пакетном добавлении
     */
    public function apiAdd(Array $leads = [])
    {
        if (empty($leads)) {
            $leads = [$this];
        }

        $parameters = [];

        foreach ($leads AS $lead) {
            $parameters[] = $lead->getValues();
        }

        $response = $this->postRequest('/api/v4/leads', $parameters);

        isset($response['_embedded']['leads']) ? $response['_embedded']['leads'] : [];

        return count($leads) == 1 ? array_shift($result) : $result;
    }

    /**
     * Обновление сделки
     *
     * Метод позволяет обновлять данные по уже существующим сделкам
     *
     * @link https://developers.amocrm.ru/rest_api/leads_set.php
     * @param int $id Уникальный идентификатор сделки
     * @param string $modified Дата последнего изменения данной сущности
     * @return bool Флаг успешности выполнения запроса
     * @throws \AmoCRM\Exception
     */
    public function apiUpdate($id, $modified = 'now')
    {
        $this->checkId($id);

        $parameters = [];

        $lead = $this->getValues();
        $lead->id = $id;
        $lead->last_modified = strtotime($modified);

        $parameters[] = $lead;

        $response = $this->patchRequest("/api/v4/leads/{$id}", $parameters);

        return empty($response);
    }
}
