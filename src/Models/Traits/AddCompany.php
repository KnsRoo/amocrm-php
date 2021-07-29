<?php

namespace AmoCRM\Models\Traits;

trait AddCompany
{
    /**
     * Сеттер для списка тегов
     *
     * @param Company $value компания
     * @return $this
     */
    public function addCompany(Company $company)
    {
        if (!$company instanceof Company){
            throw new Exception('value must be instance of Company');
        }

        $this->_embedded['companies'][] = ['id' => $company->id];

        return $this;
    }
}
