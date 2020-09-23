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
    protected $methods;

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

            $companyRelations = $company['relationships'];
            $companyCompaniesRelations = $companyRelations['contacts'];

            if (!isset($companyCompaniesRelations['data'])) {
                continue;
            }

            foreach ($companyCompaniesRelations['data'] as $contacts) {
                $companyContacts[] = $contacts['id'];
            }
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
     * @param int $dealId
     * @return array
     */
    public function getObjects(int $objectId)
    {
        return $this->methods->getObject($objectId);
    }
}
