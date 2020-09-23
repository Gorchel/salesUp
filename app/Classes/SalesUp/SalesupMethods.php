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
     * @param int $objectId
     */
    public function getObject(int $objectId)
    {
        $path = 'estate-properties/'.$objectId;

        $data = [
//            'include' => 'companies,contacts',
        ];

        $jsonResponse = $this->getRequest($path, $data);

        $response = json_decode($jsonResponse, true);

        $this->handleError($response, '. Method getDeals.');

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
