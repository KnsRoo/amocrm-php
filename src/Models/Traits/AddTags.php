<?php

namespace AmoCRM\Models\Traits;

trait AddTags
{
    /**
     * Сеттер для списка тегов
     *
     * @param array $value массив тегов
     * @return $this
     */
    public function addTags(Array $value)
    {
        $this->_embedded['tags'] = $value;

        return $this;
    }
}
