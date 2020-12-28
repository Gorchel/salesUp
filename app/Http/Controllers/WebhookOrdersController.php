<?php

namespace App\Http\Controllers;

use App\Classes\SalesUp\SalesupMethods;
use App\Orders;
use App\Properties;
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
     * @throws \Exception
     */
    public function webhookOrdersFilter(Request $request)
    {
        Log::info(json_encode($request->all()));

        $id = $request->get('ids')[0];
//        $token = $request->get('token');
        $token = env('API_TOKEN');
        $type = $request->get('type');
        $objectTypeId = $request->has('object_type') ? $request->get('object_type') : 1;
        $cityTypeId = $request->has('city_type') ? $request->get('city_type') : 1;

        $filterOrdersClass = new FilterOrders;

        $handler = new SalesupHandler($token);
        $methods = $handler->methods;

        $order = $methods->getOrder($id);
        $orderCustoms = $order['attributes']['customs'];

//        $address = $orderCustoms[$filterOrdersClass->getCustomArray($objectTypeId, 'address')];
        $filterClass = new MainFilter();
//
//        //Конфиги
        $metroSelect = config('metro')[$cityTypeId];//Метро по городу
        $companyTypes = config('company_types');//Вид деятельности
        $typeOfProperties = config('type_of_property')[$objectTypeId];//Тип недвижимости

//        $city = $filterOrdersClass->getCustomArray($objectTypeId, 'city');

//        $metro = $orderCustoms[$city['metro'][$filterClass->checkCity($address)]];

////        $disabledCompanies = strip_tags(str_replace('&nbsp;','',$object['attributes']['customs'][$this->disabledCompaniesNameField]));//Не предлагать компаниям
//
//        $districtArray = explode(',', str_replace('район','', $object['attributes']['district']));
//        $districtArray = array_map('trim', $districtArray);//Район
//
//        $addressArray = explode(',', str_replace('пр-кт','', $address));//Адрес
//        $addressArray = array_map('trim', $addressArray);

//        if (count($addressArray) == 3) {
//            $address = $addressArray[1].' '.$addressArray[2];
//        } else if (count($addressArray) == 4) {
//            $address = $addressArray[2].' '.$addressArray[3];
//        } else {
//            $address = implode(' ', $addressArray);
//        }//Адрес

        $type_of_activity = $filterOrdersClass->getCustomArray($objectTypeId, 'type_of_activity');
        $profileCompanies = [];

//        if (!empty($type_of_activity)) {
//            $profileCompanies = $orderCustoms[$type_of_activity];//Вид деятельности
//        }

//
        $objectSlider = $filterClass->getSliderOrderData($objectTypeId, $orderCustoms);

        //Вид деятельности
//        $typeOfPropertyObj = $orderCustoms[$filterOrdersClass->getCustomArray($objectTypeId, 'type_of_property')];

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
            'metro' => [],
            'districtArray' => $districtArray,
            'regionArray' => $regionArray,
            'address' => null,
            'profileCompanies' => $profileCompanies,
            'objectSlider' => $objectSlider,
            'typeOfPropertyObj' => [],
            'objectType' => $objectTypeId,
            'isLandlord' => $isLandlord,
            'cityTypeId' => $cityTypeId,
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
        $cityTypeId = $request->get('$cityTypeId');

        $filterClass = new MainFilter;
        $filterOrdersClass = new FilterOrders;

        $order = $methods->getOrder($request->get('id'));

        //Данные по фильтрам
        $objData = $filterClass->prepareData($request,$order, 'order', $object_type);

        if (empty($objData)) {
            $msg = "Выберите фильтры";
            return view('objects.error_page', ['msg' => $msg]);
        }

        $objData['object_type'] = $object_type;
        $filterOrders = [];

        if (in_array($object_type, [1,2])) {
            //Получаем Список заявок
            Orders::where('type',$object_type)
                ->chunk(1000, function($orders) use (&$filterOrders, $filterOrdersClass, $cityTypeId, $objData) {
                    foreach ($orders as $order) {
                        $orderResponse = $filterOrdersClass->filter($order, $objData, $cityTypeId);
                    }

                    if (!empty($orderResponse)) {
                        $filterOrders[] = $order;
                    }
                });

            if (empty($filterOrders)) {
                $msg = "Заявки не найдены";
                return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objData)]);
            }
        } else {
            //Получаем Список заявок
            Properties::where('type', $object_type)
                ->chunk(1000, function($properties) use (&$filterOrders, $filterOrdersClass, $cityTypeId, $objData, $object_type) {
                foreach ($properties as $property) {
                    $orderResponse = $filterOrdersClass->filterProperty($property, $objData, $cityTypeId, $object_type);

                    if (!empty($orderResponse)) {
                        $filterOrders[] = $property;
                    }
                }
            });

            if (empty($filterOrders)) {
                $msg = "Объекты недвижимости не найдены";
                return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objData)]);
            }
        }
//        dd($filterOrders);

        //прописываем связи
        $companies = [];
        $contacts = [];

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
        }

        $orderData = [];

        if (in_array($object_type, [1,2])) {
            foreach ($filterOrders as $order) {
                $orderData[] = [
                    'type' => 'orders',
                    'id' => $order['id'],
                ];
            }
        } else {
            $orderData[] = [
                'type' => 'orders',
                'id' => $order['id'],
            ];
        }

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

        switch ($object_type) {
            case 1:
            case 2:
                $stage = 32745;
                break;
            case 3:
                $stage = 32747;
                break;
            default:
                $stage = 32746;
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
                    'id' => $stage,//Воронка Аренда/Продажа
                ],
            ],
        ];

        $dealResponse = $methods->dealCreate($data);

        if (in_array($object_type, [3,4])) {
            foreach ($filterOrders as $order) {
                $methods->attachDealToObject($dealResponse['id'], $order['id']);
            }
        }

        $viewData = [
            'deal' => $dealResponse,
            'objectsCount' => count($filterOrders),
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
