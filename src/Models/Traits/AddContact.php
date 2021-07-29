<?php

namespace AmoCRM\Models\Traits;

trait addContact
{
    /**
     * Сеттер для списка тегов
     *
     * @param Contact $contact Контакт
     * @param Boolean $is_main Флаг, показывающий, является контакт главным или нет
     * @return $this
     */
    public function addContact(Contact $contact, $is_main = false)
    {
        if (!$contact instanceof Contact){
            throw new Exception('value must be instance of Contact');
        }

        $this->_embedded['contacts'][] = [ 'id' => $contact->id, 'is_main' => $is_main ];

        return $this;
    }
}
