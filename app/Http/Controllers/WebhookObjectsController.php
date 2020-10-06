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
            'district' => [
                ['custom' => 'custom-65520', 'type' => 'array'],
                ['custom' => 'custom-63707', 'type' => 'str']
            ],
            'metro' => [
                ['custom' => 'custom-65524', 'type' => 'array'],
                ['custom' => 'custom-64950', 'type' => 'str']
            ],
            'street' => [
                ['custom' => 'custom-64954', 'type' => 'str'],
                ['custom' => 'custom-64951', 'type' => 'str']
            ],
            'enabled_field' => 'custom-63697',
        ],
        [
            'type' => 'custom-64184', 'footage_before' => 'custom-64187', 'footage_after' => 'custom-64188',
            'budget_volume_before' => 'custom-64189', 'budget_volume_after' => 'custom-64190',
            'budget_footage_before' => 'custom-64191', 'budget_footage_after' => 'custom-64192',
            'district' => [
                ['custom' => 'custom-65521', 'type' => 'array'],
                ['custom' => 'custom-65932', 'type' => 'str']
            ],
            'metro' => [
                ['custom' => 'custom-65526', 'type' => 'array'],
                ['custom' => 'custom-64197', 'type' => 'str']
            ],
            'street' => [
                ['custom' => 'custom-65933', 'type' => 'str'],
                ['custom' => 'custom-65823', 'type' => 'str']
            ],
            'enabled_field' => 'custom-64187',
        ],
    ];

    /**
     * @var array
     */
    protected $objectFields = [
        'footage' => 'custom-64803', 'budget_volume' => 'custom-61758', 'budget_footage' => 'custom-61759'
    ];

    /**
     * @var string
     */
    protected $disabledCompaniesNameField = 'custom-65680';

    protected $objectDistrictField = 'custom-64791';

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

        if (!empty($object['attributes']['customs'][$this->disabledCompaniesNameField])) {
            $objectData['disabledCompaniesName'] = explode(',',strip_tags($object['attributes']['customs'][$this->disabledCompaniesNameField]));
        } else {
            $objectData['disabledCompaniesName'] = [];
        }

        //Подбираем компании
        $companies = $methods->getCompanies();

        if (empty($companies)) {
            return "Компании не найдены";
        }

        //Получаем контакты по компаниям
        $companyContacts = [];
        $companyData = [];
        $additionalContactData = [
            'district' => $object['attributes']['customs'][$this->objectDistrictField],
        ];

        foreach ($companies as $company) {
            $filterResponse = $this->filterCompany($objectData, $request,  $company);

            if (empty($filterResponse)) {
                continue;
            }

            $response = $handler->getContactByCompany($company, $companyContacts, $additionalContactData);

            if (!empty($response)) {
                $companyData[] = [
                    'type' => 'companies',
                    'id' => $company['id'],
                ];
            }
        }

//        dd($companyContacts);

        if (empty($companyContacts)) {
            return "Контакты отсутствуют";
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
            'companyCount' => count($companyData),
        ];

        return view('objects.success', $viewData);
    }

    /**
     * @param array $objectData
     * @param $company
     */
    protected function filterCompany(array $objectData, $request, $company)
    {
        $attributes = $company['attributes'];

        //Проверяем исключение по названию
        if (count($objectData['disabledCompaniesName']) > 0 && in_array($attributes['name'], $objectData['disabledCompaniesName'])) {
            return 0;
        }

        $checkerArray = [];

        foreach ($this->filterCustomsFields as $filterField) {
            $checker = 1;

            if (empty($attributes['customs'][$filterField['enabled_field']])) {
                continue;
            }

            //Проверяем исключение по типу недвижимости
            if (!empty($objectData['type']) && !empty($attributes['customs'][$filterField['type']])) {
                if (in_array($attributes['customs'][$filterField['type']], $objectData['type'])) {
                    $checker = 0;
                }
            }

            //проверяем по площади
            foreach (['footage','budget_volume','budget_footage'] as $key) {
                if (!$request->has($key.'_check')) {
                    continue;
                }

                $before = $attributes['customs'][$filterField[$key.'_before']];
                $after = $attributes['customs'][$filterField[$key.'_before']];

                if (
                    !empty($before) && !empty($after))
                {

                    if ($key == 'budget_volume') {
                        $before = $before * 1000;
                        $after = $after * 1000;
                    }

                    if (
                        $objectData[$key][0] >= $before &&
                        $objectData[$key][1] <= $after
                    ) {
                        continue;
                    } else {
                        $checker = 0;
                    }
                }
            }

//            if ($checker == 1) {
                dd($company);
//            }

            //Проверяем район/метро/дом/кв
            foreach (['district','metro','street'] as $key) {
                if (empty($request->get($key))) {
                    continue;
                }

                $localChecker = 0;

                foreach ($filterField[$key] as $customArray) {
                    if (!isset($attributes['customs'][$customArray['custom']])) {
                       continue;
                    }

                    $value = $attributes['customs'][$customArray['custom']];

                    if ($customArray['type'] == 'array') {
                        if (in_array($request->get($key), $value)) {
                            $localChecker = 1;
                        }
                    } else {
                        if (strpos($value, $request->get($key)) == true) {
                            $localChecker = 1;
                        }
                    }
                }

                if (empty($localChecker)) {
                    $checker = 0;
                }
            }

            $checkerArray[] = $checker;
        }

        if (in_array(1, $checkerArray)) {
            return 1;
        } else {
            return 0;
        }
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
            $this->percent(intval($value), intval($percentArr[0])),
            $this->percent(intval($value), intval($percentArr[1])),
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
