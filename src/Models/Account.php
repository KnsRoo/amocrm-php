<?php

namespace AmoCRM\Models;

/**
 * Class Account
 *
 * Класс модель для работы с Аккаунтом
 *
 * @package AmoCRM\Models
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Account extends AbstractModel
{
    private $params = [
        'amojo_id',
        'amojo_rights',
        'users_groups',
        'task_types',
        'version',
        'entity_names',
        'datetime_settings'
    ];

    /**
     * Данные по аккаунту
     *
     * Получение информации по аккаунту в котором произведена авторизация
     *
     * @link https://www.amocrm.ru/developers/content/crm_platform/account-info
     * @param array $parameters массив параметров для GET-параметра with
     * @return array Ответ amoCRM API
     */
    public function apiCurrent($params = [])
    {
        $params = $this->setWithParams($params);

        return $result = $this->getRequest('/api/v4/account', $params);
    }

    /** НЕ АКТУАЛЬНО!!!
     * Возвращает сведения о пользователе по его логину.
     * Если не указывать логин, вернутся сведения о владельце API ключа.
     *
     * @param null|string $login Логин пользователя
     * @return mixed Данные о пользователе
     */
    public function getUserByLogin($login = null)
    {
        if ($login === null) {
            $login = $this->getParameters()->getAuth('login');
        }

        $login = strtolower($login);
        $result = $this->apiCurrent();

        foreach ($result['users'] as $user) {
            if (strtolower($user['login']) == $login) {
                return $user;
            }
        }

        return false;
    }
}
