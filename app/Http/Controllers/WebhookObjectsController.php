<?php

namespace App\Http\Controllers;

use App\Classes\SalesUp\SalesupMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Classes\SalesUp\SalesupHandler;

/**
 * Class WebhookObjectsController
 * @package App\Http\Controllers
 */
class WebhookObjectsController extends Controller
{
    /**
     * @var array
     */
    protected $filterCustomsFields = [
        [
            'type' => 'custom-64185', 'footage_before' => 'custom-63697', 'footage_after' => 'custom-63698',
            'budget_volume_before' => 'custom-63699', 'budget_volume_after' => 'custom-63700',
            'budget_footage_before' => 'custom-63701', 'budget_footage_after' => 'custom-63702',
        ],
        [
            'type' => 'custom-64184', 'footage_before' => 'custom-64187', 'footage_after' => 'custom-64188',
            'budget_volume_before' => 'custom-64189', 'budget_volume_after' => 'custom-64190',
            'budget_footage_before' => 'custom-64191', 'budget_footage_after' => 'custom-64192',
        ],
    ];

    /**
     * @var array
     */
    protected $objectFields = [
        'footage' => 'custom-64803', 'budget_volume' => 'custom-61758', 'budget_footage' => 'custom-61759'
    ];
    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function webhookEstateFilter(Request $request)
    {
        Log::info(json_encode($request->all()));

        $id = $request->get('ids')[0];
        $token = $request->get('token');
        $type = $request->get('type');

        $data = [
            'token' => $token,
            'id' => $id,
            'type' => $type,
            'objectTypes' => config('company_types'),
        ];

        return view('objects.filter', $data);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function webhookEstateGet(Request $request)
    {
        $handler = new SalesupHandler($request->get('token'));
        $methods = $handler->methods;
        $object = $methods->getObject($request->get('id'));

        $objectData = [];

        foreach ($this->objectFields as $key => $field) {
            $objectData[$key] = $this->getArrayByPercent($object['attributes']['customs'][$field], $key, $request);
        }

        $objectData['type'] = $request->get('type');
//        dd($objectData);
//        $filter = [
//            'q' => 'id > 10000',
//        ];

        //Подбираем компании
        $companies = $methods->getCompanies();

//        dd($companies);
//
//        if (!empty($companies)) {
//            foreach ($companies as $company) {
//                $orderData = $company['relationships']['orders']['data'];
//
//                if (empty($orderData)) {
//                    continue;
//                }
//
//                dd($orderData);
//            }
//        }

        //Получаем контакты по компаниям
        $companyContacts = [];
        $companyData = [];

        foreach ($companies as $company) {
            $response = $handler->getContactByCompany($company, $companyContacts);

            if (!empty($response)) {
                $companyData[] = [
                    'type' => 'companies',
                    'id' => $company['id'],
                ];
            }
        }

        if (empty($companyContacts)) {
            return null;
        }

        $contactData = [];

        foreach ($companyContacts as $contactId) {
            $contactData[] = [
                'type' => 'contacts',
                'id' => $contactId
            ];
        }

        $data = [
            'attributes' => [
                'name' => 'Сделка по объекту',
                'description' => $object['attributes']['name'],
            ],
            'relationships' => [
                'contacts' => [
                    'data' => $contactData,
                ],
                'companies' => [
                    'data' => $companyData,
                ],
            ],
        ];

        $dealResponse = $methods->dealCreate($data);

        $objectResponse = $methods->attachDealToObject($dealResponse['id'], $object['id']);

        $viewData = [
            'deal' => $objectResponse,
            'object' => $object,
            'companyCount' => count($companyContacts),
        ];

        return view('objects.success', $viewData);
    }

    protected function filterCompany(array $objectData, $company)
    {

    }

    /**
     * @param $value
     * @param string $key
     * @param Request $request
     * @return array
     */
    protected function getArrayByPercent($value, string $key, Request $request)
    {
        $percentArr = explode(',', $request->get($key));

        return [
            $this->percent($value, $percentArr[0]),
            $this->percent($value, $percentArr[1]),
        ];
    }

    /**
     * @param $number
     * @param $percent
     * @return float|int
     */
    protected function percent($number, $percent) {
        $numberPercent = ($number / 100) * $percent;

        return intval($number + $numberPercent);
    }
}
