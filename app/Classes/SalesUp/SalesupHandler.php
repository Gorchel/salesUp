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

//    /**
//     * @return \Illuminate\Config\Repository|mixed
//     */
//    public function getSalesapOffers()
//    {
//        return config('salesap.offers');
//    }
//
//    /**
//     * Проверка отношения оффера к салону
//     * @param int $offer_id
//     * @return bool
//     */
//    public function check(int $offer_id): bool
//    {
//        if (in_array($offer_id, config('salesap.offers'))) {
//            return 1;
//        }
//
//        return 0;
//    }
//
//    /**
//     * Отправка на прозвон в салон
//     *
//     * @param Order $order
//     * @return bool
//     */
//    public function sendOrder(Order $order)
//    {
//        //Создаем контакт
//        $contactResponse = $this->createContact($order);
//
//        if (!isset($contactResponse['id'])) {
//            throw new \Exception("SalesapSender: Order #".$order->order_id.'. Contact not create in salesap.');
//        }
//
//        $dealsResponse = $this->createDeal($order, $contactResponse['id']);
//
//        if ($dealsResponse) {
//            \DB::beginTransaction();
//
//            try {
//                //Меняем статус на аутсорс
//                $order->order_status_id = OrderStatus::OUTSOURCE;
//                if ($order->save()) {
//                    if (!$order->tags()->pluck('slug')->contains('outsource')) {
//                        $order->tags()->attach(Tag::OUTSOURCE_TAG);
//                    }
//
//                    OrderHistory::addHistory(1, $order->order_id, "Salesap. Заказ отправлен на прозвон в salesap");
//                }
//
//                \Log::info('SalesapSender: Заказ №'.$order->order_id.' отправлен на прозвон в салон.');
//
//                \DB::commit();
//            } catch (\Exception $e) {
//                \Log::error($e->getMessage());
//
//                \DB::rollBack();
//            }
//
//            return true;
//        } else {
//            throw new \Exception("SalesapSender: Order #".$order->order_id.' not send in salesap.');
//        }
//    }
//
//    /**
//     * @param Order $order
//     * @return mixed
//     */
//    public function createContact(Order $order)
//    {
//        $path = 'contacts';
//        $body = [
//            'data' => [
//                'type' => 'contacts',
//                'attributes' => [
//                    'general-phone' => $order->order_client_phone,
//                    'mobile-phone' => $order->order_client_phone,
//                    'last-name' => $order->order_client_name,
//                    'first-name' => '',
//                    'description' => '',
//                ],
//                'relationships' => [
//                    'contact-type' => [
//                        'data' => [
//                            'type' => 'contact-types',
//                            'id' => '155910'
//                        ]
//                    ],
//                    'source' => [
//                        'data' => [
//                            'type' => 'sources',
//                            'id' => '254929' // Лид-форма (Inst|FB)
//                        ]
//                    ],
////                    "responsible" => [
////                        'type' => "users",
////                        'id' => 43703
////                    ],
//                ]
//            ]
//        ];
//
//        $jsonResponse = $this->postRequest($path, json_encode($body));
//
//        $response = json_decode($jsonResponse, true);
//
//        $this->handleError($response, 'Order #'.$order->order_id);
//
//        return $response['data'];
//    }
//
//    /**
//     * @param int $contact_id
//     * @return mixed
//     */
//    protected function createDeal(Order $order, int $contact_id)
//    {
//        $offer = $order->offer;
//
//        $description = '';
//
//        if (!empty($offer)) {
//            $description .=  $offer->offer_name;
//        }
//
//        $path = 'deals';
//        $body = [
//            'data' => [
//                'type' => 'deals',
//                'attributes' => [
//                    'name' => $order->order_client_name.' из КЦ',
//                    'description' => $description,
//                    'customs' => [
//                        config('salesap.order_id_field') => $order->order_id,
//                    ],
//                ],
//                'relationships' => [
//                    'contact' => [
//                        'data' => [
//                            'type' => 'contacts',
//                            'id' => $contact_id
//                        ]
//                    ],
////                    "responsible" => [
////                        'type' => "users",
////                        'id' => 43703
////                    ],
//                ]
//            ]
//        ];
//
//        $jsonResponse = $this->postRequest($path, json_encode($body));
//
//        $response = json_decode($jsonResponse, true);
//
//        $this->handleError($response, 'Order #'.$order->order_id);
//
//        return $response['data'];
//    }
//
//    /**
//     * @param $createdAtGte /Вывести объекты созданные после указанного времени "2017.08.01 12:00"
//     * @param $createdAtLte /Вывести объекты созданные до указанного времени
//     */
//    public function getDeals($createdAtGte, $createdAtLte)
//    {
//        $path = 'deals';
//
//        $data = [
//            "filter" => [
//                "created-at-gte" => $createdAtGte,
//                "created-at-lte" => $createdAtLte,
////                "responsible-id" => 43703,
//                'table-state-id' => 1680424,
//            ],
//        ];
//
//        $jsonResponse = $this->getRequest($path, $data);
//
//        $response = json_decode($jsonResponse, true);
//
//        $this->handleError($response, '. Method getDeals.');
//
//        return $response['data'];
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getStagesCategories()
//    {
//        $path = 'deal-stage-categories';
//        $response = $this->getRequest($path);
//
//        return json_decode($response, true)['data'];
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getStagesUsers()
//    {
//        $path = 'users';
//        $response = $this->getRequest($path);
//
//        return json_decode($response, true)['data'];
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getApiRequest($path)
//    {
//        $response = $this->getRequest($path);
//
//        return json_decode($response, true)['data'];
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getStages()
//    {
//        $path = 'deal-stages';
//        $response = $this->getRequest($path);
//
//        $stages = [];
//
//        foreach (json_decode($response, true)['data'] as $stage) {
//            $stages[$stage['id']] = $stage['attributes']['name'];
//        }
//
//        return $stages;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getDealStage(int $deal_id)
//    {
//        $path = "deals/{$deal_id}/relationships/stage";
//        $jsonResponse = $this->getRequest($path);
//
//        $response = json_decode($jsonResponse, true);
//
//        $this->handleError($response, '. Method getDealStage.');
//
//        $stage = $response['data'];
//
//        if (empty($stage)) {
//            return null;
//        }
//
//        $salesapRelations = new SalesapRelations();
//        $cc_status = $salesapRelations->getStage($stage['id']);
//
//        return $cc_status;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getStatuses()
//    {
//        $path = 'deal-statuses';
//        $response = $this->getRequest($path);
//
//        return json_decode($response, true)['data'];
//    }
//
//    /**
//     * @param $path
//     * @param array $params
//     * @return bool|string
//     */
//    protected function postRequest($path, $params = [])
//    {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $this->url.$path);
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, [
//            'Authorization: Bearer '.$this->token,
//            'Content-Type: application/vnd.api+json',
//        ]);
//        curl_setopt($ch, CURLOPT_POST, 1);
//
//        if (!empty($params)) {
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
//        }
//
//        $response = curl_exec($ch);
//        curl_close($ch);
//
//        return $response;
//    }
//
//    /**
//     * @param $path
//     * @param array $params
//     * @return bool|string
//     */
//    protected function getRequest($path, $params = [])
//    {
//
//        if( $params !== false && is_array($params) && count($params) ) {
//            $paramsStr = '?'.http_build_query( $params );
//        } else {
//            $paramsStr = '';
//        }
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $this->url.$path.$paramsStr);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, [
//            'Authorization: Bearer '.$this->token,
//            'Content-Type: application/vnd.api+json',
//        ]);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//
//        $response = curl_exec($ch);
//        curl_close($ch);
//
//        return $response;
//    }
//
//    /**
//     * @param Order $order
//     * @param $response
//     * @throws \Exception
//     */
//    protected function handleError($response, string $text)
//    {
//        if (isset($response['errors'])) {
//            throw new \Exception("SalesapSender: ".json_encode($response['errors']).'. '.$text);
//        }
//    }
}
