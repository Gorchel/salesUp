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
            'metro_1' => 'custom-66364',
            'metro_2' => 'custom-65524',
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
            'metro_1' => 'custom-66365',
            'metro_2' => 'custom-65526',
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
    protected $messages = [
        'footage' => 'По площади (кв/м)',
        'budget_volume' => 'Арендная ставка в месяц',
        'budget_footage' => 'Арендная ставка за кв. м в месяц',
        'district' => 'Район', 'metro' => 'Метро', 'street' => 'Улица',
        'type' => 'По профилю компании',
    ];

    /**
     * @var array
     */
    protected $objectFields = [
//        'footage' => 'custom-64803',
        'budget_volume' => 'custom-61758', 'budget_footage' => 'custom-61759'
    ];

    /**
     * @var string
     */
    protected $disabledCompaniesNameField = 'custom-65680';

    /**
     * @var string
     */
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

        $handler = new SalesupHandler($request->get('token'));
        $methods = $handler->methods;
        $object = $methods->getObject($id);

        $address = $object['attributes']['address'];
        $metroSelect = config('metro')[$this->checkCity($address)];

        $data = [
            'token' => $token,
            'id' => $id,
            'type' => $type,
            'metroSelect' => $metroSelect,
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

        $objectData['footage'] = $this->getArrayByPercent($object['attributes']['total-area'], 'footage', $request);;

        foreach ($this->objectFields as $key => $field) {
            $objectData[$key] = $this->getArrayByPercent($object['attributes']['customs'][$field], $key, $request);
        }

        $objectData['type'] = $request->get('type');

        if (!empty($object['attributes']['customs'][$this->disabledCompaniesNameField])) {
            $objectData['disabledCompaniesName'] = explode(',',strip_tags($object['attributes']['customs'][$this->disabledCompaniesNameField]));
        } else {
            $objectData['disabledCompaniesName'] = [];
        }

        $objectData['address'] = $object['attributes']['address'];

        if (
            empty($request->get('type')) &&
            empty($request->get('footage_check')) &&
            empty($request->get('budget_volume_check')) &&
            empty($request->get('budget_footage_check')) &&
            empty($request->get('district')) &&
            empty($request->get('metro')) &&
            empty($request->get('street'))
        ) {
            $msg = "Выберите фильтр";
            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objectData)]);
        }

        //Подбираем компании
        $companies = $methods->getCompanies();

        if (empty($companies)) {
            $msg = "Компании не найдены";
            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objectData)]);
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

        if (empty($companyContacts)) {
            $msg = "Контакты отсутствуют";
            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objectData)]);
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

                $before = intval($attributes['customs'][$filterField[$key.'_before']]);
                $after = intval($attributes['customs'][$filterField[$key.'_after']]);

                if ($key == 'budget_volume') {
                    $before = $before * 1000;
                    $after = $after * 1000;
                }

                $crossInterval = $this->crossingInterval($objectData[$key][0], $objectData[$key][1], $before, $after);

                if (!empty($crossInterval)) {
                    continue;
                } else {
                    $checker = 0;
                }
            }

            //Проверяем район/метро/дом/кв
            foreach (['district','street'] as $key) {
                if (empty($request->get($key))) {
                    continue;
                }

                $keyArray = array_map('trim', explode(',',trim(mb_strtolower($request->get($key)))));

                $localChecker = 0;

                foreach ($filterField[$key] as $customArray) {
                    if (empty($attributes['customs'][$customArray['custom']])) {
                       continue;
                    }

                    $value = $attributes['customs'][$customArray['custom']];

                    if ($customArray['type'] != 'array') {
                        $value = array_map('trim', explode(',',trim(mb_strtolower($value))));
                    } else {
                        $value = array_map('mb_strtolower', $value);
                    }

                    foreach ($value as $valueEl) {
                        foreach ($keyArray as $keyEl) {
                            if (strpos($valueEl, $keyEl) !== false) {
                                $localChecker = 1;
                            }
                        }
                    }
                }

                if (empty($localChecker)) {
                    $checker = 0;
                }
            }

            //Метро
            if (!empty($request->get('metro'))) {
                $metroSelectId = $this->checkCity($objectData['address']);
                $metroValue = $attributes['customs'][$filterField['metro_'.$metroSelectId]];

                if (empty($metroValue) || empty(array_intersect($metroValue, $request->get('metro')))) {
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
     * @param $request
     */
    protected function getErrors($request, $objectData)
    {
        $errors = [];

        foreach (['footage','budget_volume','budget_footage'] as $key) {
            if (!$request->has($key.'_check')) {
                continue;
            }

            $errors[] = [
                'name' => $this->messages[$key],
                'text' => 'Диапазон: '.$objectData[$key][0].' - '.$objectData[$key][1],
            ];
        }

        foreach (['district','street'] as $key) {
            if (empty($request->get($key))) {
                continue;
            }

            $errors[] = [
                'name' => $this->messages[$key],
                'text' => $request->get($key),
            ];
        }

        if (!empty($request->get('metro'))) {
            $errors[] = [
                'name' => $this->messages['metro'],
                'text' => implode(',',$request->get($key)),
            ];
        }

        return $errors;
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
     * @param $startInt
     * @param $finishInt
     * @param $startValue
     * @param $finishValue
     * @return int
     */
    public function crossingInterval($startInt, $finishInt, $startValue, $finishValue) {
       if (
            ($startValue >= $startInt && $startValue <= $finishInt) ||
            ($finishValue <= $finishInt && $finishValue >= $startInt) ||
            ($startValue <= $finishInt && $finishValue >= $startInt) ||
            ($startValue >= $startInt && $finishValue <= $finishInt)
       ) {
           return 1;
       }

       return 0;
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

    /**
     * @param $address
     * @return int
     */
    protected function checkCity($address)
    {
        if (strpos($address,'Петербург') == true) {
            return 2;
        }
        return 1;
    }
}
