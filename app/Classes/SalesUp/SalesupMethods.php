<?php

namespace App\Classes\SalesUp;

/**
 * Class SalesupMethods
 * @package App\Classes\SalesUp;
 */
class SalesupMethods
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
     * SalesupHandler constructor.
     */
    public function __construct(string $token)
    {
        $this->url = config('main.url');
        $this->token = $token;
    }

    /**
     * @param int $dealId
     */
    public function getDeal(int $dealId)
    {
        $path = 'deals/'.$dealId;

        $data = [
            'include' => 'companies,contacts',
        ];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getDeals.');

        return $response['data'];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getOrders($number = 1, $size = 100, $filters = [])
    {
        $path = 'orders';

        $data = [
            'include' => 'companies,contacts',
            'page' => [
                'number' => $number,
                'size' => $size,
            ],
        ];

        if (!empty($filters)) {
            $data['filter'] = $filters;
        }

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getOrders.');

        return $response;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPaginationObjects($number = 1, $size = 100, $filters = [])
    {
        $path = 'estate-properties';

        $data = [
            'include' => 'companies,contacts',
            'page' => [
                'number' => $number,
                'size' => $size,
            ],
        ];

        if (!empty($filters)) {
            $data['filter'] = $filters;
        }

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getPaginationObjects.');

        return $response;
    }

    /**
     * @param $orderId
     * @return mixed
     * @throws \Exception
     */
    public function getOrder($orderId)
    {
        $path = 'orders/'.$orderId;

        $data = [
            'include' => 'company,contact',
        ];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getOrders.');

        return $response['data'];
    }

    /**
     * @param int $dealId
     */
    public function dealUpdate(int $dealId, array $contacts)
    {
        $path = 'deals/'.$dealId;

        $data = [];

        foreach ($contacts as $contact) {
            $data[] = [
                'type' => 'contacts',
                'id' => $contact
            ];
        }

        $body = [
            'data' => [
                'type' => 'deals',
                'id' => $dealId,
                'relationships' => [
                    'contacts' => [
                        'data' => $data,
                    ],
                ],
            ],
        ];

        $jsonResponse = $this->patchRequest($path, json_encode($body));

        $response = json_decode($jsonResponse, true);

        $this->handleError($response);

        return $response['data'];
    }

    /**
     * @param int $dealId
     * @param array $relationships
     * @return
     * @throws \Exception
     */
    public function dealDataUpdate(int $dealId, array $relationships)
    {
        $path = 'deals/'.$dealId;

        $body = [
            'data' => [
                'type' => 'deals',
                'id' => $dealId,
                'relationships' => $relationships,
            ],
        ];

        $jsonResponse = $this->patchRequest($path, json_encode($body));

        $response = json_decode($jsonResponse, true);

        $this->handleError($response);

        return $response['data'];
    }

    /**
     * @param int $dealId
     */
    public function dealCreate(array $data)
    {
        $path = 'deals';

        $data['type'] = 'deals';

        $body = ['data' => $data];

        $jsonResponse = $this->postRequest($path, json_encode($body));

        $response = json_decode($jsonResponse, true);

        $this->handleError($response);

        return $response['data'];
    }

    /**
     * @param int $companyId
     */
    public function getCompany(int $companyId)
    {
        $path = 'companies/'.$companyId;

        $data = [
            'include' => 'contacts',
        ];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getDeals.');

        return $response['data'];
    }

    /**
     * @param array|null $filter
     * @return
     * @throws \Exception
     */
    public function getCompanies(array $filter = null)
    {
        $path = 'companies';

        $data = [
            'include' => 'contacts',
        ];

        if (!empty($filter)) {
            $data['filter'] = $filter;
        }

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getCompanies.');

        return $response['data'];
    }

    /**
     * @param int $contactId
     * @return mixed
     * @throws \Exception
     */
    public function getContact(int $contactId)
    {
        $path = 'contacts/'.$contactId;

        $data = [];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getDeals.');

        return $response['data'];
    }

    /**
     * @param array $filters
     * @return mixed
     * @throws \Exception
     */
    public function getContacts(array $filters = [])
    {
        $path = 'contacts';

        $data = [
            'filter' => $filters
        ];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getDeals.');

        return $response['data'];
    }

    /**
     * @param int $objectId
     */
    public function getObject(int $objectId)
    {
        $path = 'estate-properties/'.$objectId;

        $data = [
            'include' => 'deals',
        ];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getObject.');

        return $response['data'];
    }


    /**
     * @return mixed
     * @throws \Exception
     */
    public function getObjects()
    {
        $path = 'estate-properties';

        $data = [
            'include' => 'companies,contacts',
        ];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getObjects.');

        return $response['data'];
    }

    /**
     * @param int $objectId
     */
    public function getDealStages()
    {
        $path = 'deal-stage-categories';

        $data = [
//            'include' => 'deals',
        ];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getObject.');

        return $response['data'];
    }

    /**
     * @param int $objectId
     */
    public function getDealStatuses()
    {
        $path = 'deal-statuses';

        $data = [
//            'include' => 'deals',
        ];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getDealStatuses.');

        return $response['data'];
    }

    /**
     * @param int $dealId
     * @param int $objectId
     * @return mixed
     * @throws \Exception
     */
    public function attachDealToObject(int $dealId, int $objectId) {
        $path = 'estate-properties/'.$objectId;

        $body = [
            'data' => [
                'type' => 'estate-properties',
                'id' => $objectId,
                'relationships' => [
                    'deals' => [
                        'data' => [
                            [
                                'id' => $dealId,
                                'type' => 'deals',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $jsonResponse = $this->patchRequest($path, json_encode($body));

        $response = json_decode($jsonResponse, true);

        $this->handleError($response);

        return $response['data'];
    }

    /**
     * @param array $deals
     * @param int $objectId
     * @return mixed
     * @throws \Exception
     */
    public function attachDealsToObject(array $deals, int $objectId)
    {
        $path = 'estate-properties/'.$objectId;

        $data = [];

        foreach ($deals as $deal) {
            $data[] = [
                'id' => $deal,
                'type' => 'deals',
            ];
        }

        $body = [
            'data' => [
                'type' => 'estate-properties',
                'id' => $objectId,
                'relationships' => [
                    'deals' => [
                        'data' => $data,
                    ],
                ],
            ],
        ];

        $jsonResponse = $this->patchRequest($path, json_encode($body));

        $response = json_decode($jsonResponse, true);

        $this->handleError($response);

        return $response['data'];
    }

    /**
     * @param int $dealId
     */
    public function objectUpdate(int $objectId, array $updateData)
    {
        $path = 'estate-properties/'.$objectId;

        $data = [];

        if (isset($updateData['district'])) {
            $data['district'] = $updateData['district'];

            if (!isset($data['customs'])) {
                $data['customs'] = [];
            }

            $data['customs']['custom-64791'] = $updateData['district'];
        }

        if (isset($updateData['metro'])) {
            $data['subway-name'] = $updateData['metro'];

            if (!isset($data['customs'])) {
                $data['customs'] = [];
            }

            $data['customs']['custom-64792'] = $updateData['metro'];
        }

        if (isset($updateData['latitude'])) {
            $data['latitude'] = $updateData['latitude'];
        }

        if (isset($updateData['longitude'])) {
            $data['longitude'] = $updateData['longitude'];
        }

        if (isset($updateData['latitude']) && isset($updateData['longitude'])) {
            if (!isset($data['customs'])) {
                $data['customs'] = [];
            }

            $data['customs']['custom-65599'] = $updateData['latitude'].','.$updateData['longitude'];
        }

        $body = [
            'data' => [
                'type' => 'estate-properties',
                'id' => $objectId,
                'attributes' => $data,
            ],
        ];

        $jsonResponse = $this->patchRequest($path, json_encode($body));

        $response = json_decode($jsonResponse, true);

        $this->handleError($response);

        return $response['data'];
    }

        /**
     * @param $path
     * @param array $params
     * @return bool|string
     */
    protected function getRequest($path, $params = [])
    {
        if ( $params !== false && is_array($params) && count($params) ) {
            $paramsStr = '?'.http_build_query( $params );
        } else {
            $paramsStr = '';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$path.$paramsStr);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$this->token,
            'Content-Type: application/vnd.api+json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @param $path
     * @param array $params
     * @return bool|string
     */
    protected function postRequest($path, $params = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$this->token,
            'Content-Type: application/vnd.api+json',
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);

        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

        /**
     * @param $path
     * @param array $params
     * @return bool|string
     */
    protected function patchRequest($path, $params = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$this->token,
            'Content-Type: application/vnd.api+json',
        ]);
//        curl_setopt($ch, CURLOPT_POST, 1);

        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @param $response
     * @throws \Exception
     */
    protected function handleError($response, string $text = '')
    {
        if (isset($response['errors'])) {
            throw new \Exception("SalesapSender: ".json_encode($response['errors']).'. '.$text);
        }
    }
}
