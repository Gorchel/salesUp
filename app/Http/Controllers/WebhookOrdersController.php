<?php

namespace App\Http\Controllers;

use App\Classes\SalesUp\SalesupMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Classes\SalesUp\SalesupHandler;
use App\Classes\Filters\MainFilter;
use App\Classes\Filters\FilterOrders;
use App\Classes\Filters\FilterCompany;

/**
 * Class WebhookObjectsController
 * @package App\Http\Controllers
 */
class WebhookOrdersController extends Controller
{
//    /**
//     * @var array
//     */
//    protected $filterCustomsFields = [
//        [
//            'type' => 'custom-64185', 'footage_before' => 'custom-63697', 'footage_after' => 'custom-63698',
//            'budget_volume_before' => 'custom-63699', 'budget_volume_after' => 'custom-63700',
//            'budget_footage_before' => 'custom-63701', 'budget_footage_after' => 'custom-63702',
//            'district' => [
//                ['custom' => 'custom-65520', 'type' => 'array'],
//                ['custom' => 'custom-63707', 'type' => 'str']
//            ],
//            'metro_1' => 'custom-66364',
//            'metro_2' => 'custom-65524',
//            'street' => [
//                ['custom' => 'custom-64954', 'type' => 'str'],
//                ['custom' => 'custom-64951', 'type' => 'str']
//            ],
//            'enabled_field' => 'custom-63697', 'brand' => 'custom-63694',
//        ],
//        [
//            'type' => 'custom-64184', 'footage_before' => 'custom-64187', 'footage_after' => 'custom-64188',
//            'budget_volume_before' => 'custom-64189', 'budget_volume_after' => 'custom-64190',
//            'budget_footage_before' => 'custom-64191', 'budget_footage_after' => 'custom-64192',
//            'district' => [
//                ['custom' => 'custom-65521', 'type' => 'array'],
//                ['custom' => 'custom-65932', 'type' => 'str']
//            ],
//            'metro_1' => 'custom-66365',
//            'metro_2' => 'custom-65526',
//            'street' => [
//                ['custom' => 'custom-65933', 'type' => 'str'],
//                ['custom' => 'custom-65823', 'type' => 'str']
//            ],
//            'enabled_field' => 'custom-64187', 'brand' => 'custom-64183',
//        ],
//    ];

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
     * @var string
     */
    protected $objectDistrictField = 'custom-64791';

    /**
     * @var string
     */
    protected $objectProfileOfCompany = 'custom-61774';
    protected $typeOfPropertyField = 'custom-61755';

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function webhookOrdersFilter(Request $request)
    {
        Log::info(json_encode($request->all()));

        $id = $request->get('ids')[0];
        $token = $request->get('token');
        $type = $request->get('type');
        $objectType = $request->has('object_type') ? $request->get('object_type') : 1;

        if (in_array($objectType, [1,2])) {
            $objectTypeId = 1;
        } else {
            $objectTypeId = 2;
        }

        $filterOrdersClass = new FilterOrders;

        $handler = new SalesupHandler($request->get('token'));
        $methods = $handler->methods;
        $orders = $methods->getOrder($id);
        $orderCustoms = $orders['attributes']['customs'];

        $address = $orderCustoms[$filterOrdersClass->getCustomArray($objectTypeId, 'address')];
        $filterClass = new MainFilter();
//
//        //Конфиги
        $metroSelect = config('metro')[$filterClass->checkCity($address)];//Метро по городу
        $companyTypes = config('company_types');//Вид деятельности
        $typeOfProperties = config('type_of_property');//Тип недвижимости

        $city = $filterOrdersClass->getCustomArray($objectTypeId, 'city');

        $metro = $orderCustoms[$city['metro'][$filterClass->checkCity($address)]];

////        $disabledCompanies = strip_tags(str_replace('&nbsp;','',$object['attributes']['customs'][$this->disabledCompaniesNameField]));//Не предлагать компаниям
//
//        $districtArray = explode(',', str_replace('район','', $object['attributes']['district']));
//        $districtArray = array_map('trim', $districtArray);//Район
//
        $addressArray = explode(',', str_replace('пр-кт','', $address));//Адрес
        $addressArray = array_map('trim', $addressArray);

        if (count($addressArray) == 3) {
            $address = $addressArray[1].' '.$addressArray[2];
        } else if (count($addressArray) == 4) {
            $address = $addressArray[2].' '.$addressArray[3];
        } else {
            $address = implode(' ', $addressArray);
        }//Адрес

        $type_of_activity = $filterOrdersClass->getCustomArray($objectTypeId, 'type_of_activity');
        $profileCompanies = [];

        if (!empty($type_of_activity)) {
            $profileCompanies = $orderCustoms[$type_of_activity];//Вид деятельности
        }

//
        $objectSlider = [];

        foreach ($filterClass->objectFields as $key => $field) {
//            $objectSlider[$key] = $object['attributes']['customs'][$field];
            $objectSlider[$key] = 100;
        }//Слайдеры

        $objectSlider['footage'] = 100;

        if (!empty($orderCustoms[$filterOrdersClass->getCustomArray($objectTypeId, 'budget_volume')])) {
            $objectSlider['footage'] = $orderCustoms[$filterOrdersClass->getCustomArray($objectTypeId, 'footage')];
        }

        $objectSlider['budget_volume'] = 100;

        if (!empty($orderCustoms[$filterOrdersClass->getCustomArray($objectTypeId, 'budget_volume')])) {
            $objectSlider['budget_volume'] = $orderCustoms[$filterOrdersClass->getCustomArray($objectTypeId, 'budget_volume')];
        }

        $objectSlider['budget_footage'] = 100;
        $budget_footage = $filterOrdersClass->getCustomArray($objectTypeId, 'budget_footage');

        if (isset($budget_footage) && !empty($orderCustoms[$budget_footage])) {
            $objectSlider['budget_footage'] = $orderCustoms[$budget_footage];
        }

        //Вид деятельности
        $typeOfPropertyObj = $orderCustoms[$filterOrdersClass->getCustomArray($objectTypeId, 'type_of_property')];

        //Срок окупаемости
//        $objectSlider['payback_period'] = 16;
//        $payback_period = $filterOrdersClass->getCustomArray($objectTypeId, 'payback_period');
//
//        if (isset($payback_period) && !empty($orderCustoms[$payback_period])) {
//            $objectSlider['payback_period'] = $orderCustoms[$payback_period];
//        }

        $districtArray = [];
        $regionArray = [];

        //С арендодатором
        $isLandlord = [];

        $is_landlord = $filterOrdersClass->getCustomArray($objectTypeId, 'is_landlord');

        if (isset($is_landlord) && !empty($orderCustoms[$is_landlord])) {
            $isLandlord = $orderCustoms[$is_landlord];
        }

        $data = [
            'token' => $token,
            'id' => $id,
            'type' => $type,
            'metroSelect' => $metroSelect,
            'objectTypes' => $companyTypes,
            'typeOfProperties' => $typeOfProperties,
//            'attributes' => $object['attributes'],
            'metro' => $metro,
            'districtArray' => $districtArray,
            'regionArray' => $regionArray,
            'address' => $address,
            'profileCompanies' => $profileCompanies,
            'objectSlider' => $objectSlider,
            'typeOfPropertyObj' => $typeOfPropertyObj,
            'objectType' => $objectType,
            'isLandlord' => $isLandlord,
        ];

        return view('orders.filter', $data);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function webhookOrdersGet(Request $request)
    {
        $handler = new SalesupHandler($request->get('token'));
        $methods = $handler->methods;

        $object_type = $request->get('object_type');

        if (in_array($object_type, [1,2])) {
            $object_type_id = 1;
        } else {
            $object_type_id = 2;
        }

        $filterClass = new MainFilter;
        $filterOrdersClass = new FilterOrders;

        $order = $methods->getOrder($request->get('id'));
        $orderCustoms = $order['attributes']['customs'];
        $address = $orderCustoms[$filterOrdersClass->getCustomArray($object_type_id, 'address')];
        $typeOfObject = $filterClass->checkCity($address);

        //Данные по фильтрам
        $objData = $filterClass->prepareData($request, $order,'order', $object_type_id);

        if (empty($objData)) {
            $msg = "Выберите фильтры";
            return view('objects.error_page', ['msg' => $msg]);
        }

        $objData['object_type'] = $object_type_id;

        //Получаем Список недвижки
        $objects = $methods->getObjects();

        if (empty($objects)) {
            $msg = "Недвижимость не найдена";
            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objData)]);
        }

        //Фильтрация по заявкам
        $filterObjects = [];

        foreach ($objects as $objectKey => $object) {
            $objectResponse = $filterOrdersClass->filterObject($object, $objData, $typeOfObject);

            if (!empty($objectResponse)) {
                $filterObjects[] = $object;
            }
        }

        if (empty($filterObjects)) {
            $msg = "Недвижимость не найдена";
            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objData)]);
        }

        //прописываем связи
        $companies = [];
        $contacts = [];

        foreach ($filterObjects as $object) {
            //Компании
            if (!empty($object['relationships']['companies']['data'])) {
                foreach ($object['relationships']['companies']['data'] as $company) {
                    $companies[$company['id']] = $company['id'];
                }
            }

            //Контакты
            if (!empty($object['relationships']['contacts']['data'])) {
                foreach ($object['relationships']['contacts']['data'] as $contact) {
                    $contacts[$contact['id']] = $contact['id'];
                }
            }
        }

        //Проверяем контакты и компании в заявке
        if (!empty($order['relationships']['contact']['data'])) {
            $contacts[$order['relationships']['contact']['data']['id']] = $order['relationships']['contact']['data']['id'];
        }

        if (!empty($order['relationships']['company']['data'])) {
            $companies[$order['relationships']['company']['data']['id']] = $order['relationships']['company']['data']['id'];
        }

        $orderData = [
            [
                'type' => 'orders',
                'id' => $order['id'],
            ]
        ];

        //Проверяем компании
        $companiesData = [];

        if (!empty($companies)) {
            foreach ($companies as $companyId) {
                $company = $methods->getCompany($companyId);

                if (!empty($company['relationships']['contacts']['data'])) {
                    foreach ($company['relationships']['contacts']['data'] as $contact) {
                        $contacts[$contact['id']] = $contact['id'];
                    }
                }

                $companiesData[] = [
                    'type' => 'companies',
                    'id' => $companyId,
                ];
            }
        }

        $contactsData = [];

        if (!empty($companies)) {
            foreach ($contacts as $contactsId) {
                $contactsData[] = [
                    'type' => 'contacts',
                    'id' => $contactsId,
                ];
            }
        }

        $data = [
            'attributes' => [
                'name' => 'Сделка по заявке',
                'description' => $order['id'],
            ],
            'relationships' => [
                'contacts' => [
                    'data' => $contactsData,
                ],
                'companies' => [
                    'data' => $companiesData,
                ],
                'orders' => [
                    'data' => $orderData,
                ],
                'stage-category' => [
                    'type' => 'stage-category',
                    'id' => $object_type_id == 1 ? 32746 : 32747,//Воронка Аренда/Продажа
                ],
            ],
        ];

        $dealResponse = $methods->dealCreate($data);

        //Закрепление за объектом
        foreach ($filterObjects as $object) {
            $objectResponse = $methods->attachDealToObject($dealResponse['id'], $object['id']);
        }

        $viewData = [
            'deal' => $dealResponse,
            'order' => $order,
            'objectsCount' => count($objects),
        ];

        return view('orders.success', $viewData);
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
                'text' => implode(',',$request->get('metro')),
            ];
        }

        return $errors;
    }
}
