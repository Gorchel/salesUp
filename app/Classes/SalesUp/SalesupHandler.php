<?php

namespace App\Classes\SalesUp;

/**
 * Class SalesupHandler
 * @package App\Classes\SalesUp;
 */
class SalesupHandler
{
    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $url;
    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $token;
    /**
     * @var SalesupMethods
     */
    public $methods;

    /**
     * SalesupHandler constructor.
     */
    public function __construct(string $token)
    {
        $this->url = config('main.url');
        $this->token = $token;
        $this->methods = new SalesupMethods($token);

    }

    /**
     * @param int $dealId
     * @return array
     */
    public function updateDeals(int $dealId)
    {
        $deal = $this->methods->getDeal($dealId);
        $dealRelations = $deal['relationships'];
        $dealCompaniesRelations = $dealRelations['companies'];

        if (!isset($dealCompaniesRelations['data'])) {
            return [];
        }

        $companyData = $dealCompaniesRelations['data'];
        $companyContacts = [];

        foreach ($companyData as $companyId) {
            $company = $this->methods->getCompany($companyId['id']);

            $companyContacts = $this->getContactByCompany($company, $companyContacts);
        }

        //Получаем контакты сделки
        $dealContactsRelations = $dealRelations['contacts'];
//        $dealContacts = [];

        if (isset($dealContactsRelations['data'])) {
            foreach ($dealContactsRelations['data'] as $dealContact) {
//                $dealContacts[] = $dealContact['id'];
                $companyContacts[] = $dealContact['id'];
            }
        }

        //Обновляем контакты
        $response = $this->methods->dealUpdate($dealId, array_unique($companyContacts));

        return $response;
    }

    /**
     * @param $company
     * @return array|mixed
     */
    public function getContactByCompany($company, array &$outputContacts, $additionalData = [])
    {
        $contactDistrictField = 'custom-65519';

        $companyRelations = $company['relationships'];
        $companyCompaniesRelations = $companyRelations['contacts'];

        if (!isset($companyCompaniesRelations['data'])) {
            return 0;
        }

        foreach ($companyCompaniesRelations['data'] as $contacts) {
            //Проверяем контакт
            $contact = $this->methods->getContact($contacts['id']);

            //Преобразуем массив
            $filterDistricts = array_filter($contact['attributes']['customs'][$contactDistrictField], function($value) {
               if (!empty($value)) {
                   return 1;
               }

               return 0;
            });

            if (!empty($filterDistricts) && isset($additionalData['district'])) {
                $districtChecker = 0;

                foreach ($filterDistricts as $district) {
                    if (strpos($additionalData['district'], $district) == true) {
                        $districtChecker = 1;
                    }
                }

                if (empty($districtChecker)) {
                    return 0;
                }
            }

            $outputContacts[] = $contacts['id'];
        }

        return 1;
    }

    /**
     * @param int $objectId
     * @return array
     */
    public function getObjects(int $objectId)
    {
        return $this->methods->getObject($objectId);
    }

    /**
     * @param int $objectId
     * @return array
     */
    public function updateObject(int $objectId, array $updateData)
    {
        return $this->methods->objectUpdate($objectId, $updateData);
    }
}
