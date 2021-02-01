<?php

namespace App\Jobs;

use App\Classes\SalesUp\SalesupHandler;

class OrderJobs extends Job
{
    public $dealId;
    public $filterOrders;

    /**
     * Create a new job instance.
     *
     * @param $dealId
     * @param $filterOrders
     */
    public function __construct($dealId, $filterOrders)
    {
        $this->dealId = $dealId;
        $this->filterOrders = $filterOrders;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        //прописываем связи
        $companies = [];
        $contacts = [];
        $orderData = [];

        $filterOrders = json_decode($this->filterOrders, true);

        if (empty($filterOrders)) {
            return;
        }

        $handler = new SalesupHandler(env('API_TOKEN'));
        $methods = $handler->methods;

        foreach ($filterOrders as $filterOrder) {
            $relationships = json_decode($filterOrder['relationships'], true);

            //Компании
            if (!empty($relationships['companies']['data'])) {
                foreach ($relationships['companies']['data'] as $company) {
                    $companies[$company['id']] = $company['id'];
                }
            }

            //Контакты
            if (!empty($relationships['contacts']['data'])) {
                foreach ($relationships['contacts']['data'] as $contact) {
                    $contacts[$contact['id']] = $contact['id'];
                }
            }

            $orderData[] = [
                'type' => 'orders',
                'id' => $filterOrder['id'],
            ];
        }

        //Проверяем компании
        $companiesData = [];

        if (!empty($companies)) {
            foreach ($companies as $companyId) {
//                $company = $methods->getCompany($companyId);
//
//                if (!empty($company['relationships']['contacts']['data'])) {
//                    foreach ($company['relationships']['contacts']['data'] as $contact) {
//                        $contacts[$contact['id']] = $contact['id'];
//                    }
//                }

                $companiesData[] = [
                    'type' => 'companies',
                    'id' => $companyId,
                ];
            }
        }

        $contactsData = [];

        if (!empty($contacts)) {
            foreach ($contacts as $contactsId) {
                $contactsData[] = [
                    'type' => 'contacts',
                    'id' => $contactsId,
                ];
            }
        }

        $relationships = [
            'contacts' => [
                'data' => $contactsData,
            ],
            'companies' => [
                'data' => $companiesData,
            ],
            'orders' => [
                'data' => $orderData,
            ],
        ];

        $methods->dealDataUpdate($this->dealId, $relationships);

        return true;
    }
}
