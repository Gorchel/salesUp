<?php

namespace App\Http\Controllers;

use App\Classes\SalesUp\SalesupMethods;
use App\Jobs\OrderJobs;
use App\Properties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Classes\SalesUp\SalesupHandler;
use App\Classes\Filters\MainFilter;
use App\Classes\Filters\FilterOrders;
use App\Classes\Filters\FilterCompany;
use App\Orders;

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
            'enabled_field' => 'custom-63697', 'brand' => 'custom-63694',
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
            'enabled_field' => 'custom-64187', 'brand' => 'custom-64183',
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
     * @var string
     */
    protected $objectDistrictField = 'custom-64791';
    /**
     * @var array
     */
    protected $status2id = [
        'ППА' => 112196,
        'Аренда' => 112197,
        'Продажа' => 112198,
        'Управление' => 112199,
        'Комиссия' => 120414,
    ];

    /**
     * @var string
     */
    protected $objectProfileOfCompany = 'custom-61774';
    /**
     * @var string
     */
    protected $typeOfPropertyField = 'custom-61755';
    /**
     * @var string
     */
    protected $typeOfDealField = 'custom-62518';//Тип сделки

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function webhookEstateFilter(Request $request)
    {
        Log::info(json_encode($request->all()));

        $id = $request->get('ids')[0];
        $token = env('API_TOKEN');
        $type = $request->get('type');

        $handler = new SalesupHandler($token);
        $methods = $handler->methods;
        $object = $methods->getObject($id);

        $address = $object['attributes']['address'];

        $objectType = $request->has('object_type') ? $request->get('object_type') : 4;

        $filterClass = new MainFilter();

        //Конфиги
        $metroSelect = config('metro')[$filterClass->checkCity($address)];//Метро по городу
        $companyTypes = config('company_types');//Вид деятельности
        $typeOfProperties = config('type_of_property')[$objectType];//Тип недвижимости

        //Подготовка значений
        $metro = trim(mb_strtolower($object['attributes']['subway-name']));//Метро
//        $disabledCompanies = strip_tags(str_replace('&nbsp;','',$object['attributes']['customs'][$this->disabledCompaniesNameField]));//Не предлагать компаниям

        $districtArray = explode(',', str_replace('район','', $object['attributes']['district']));
        $districtArray = array_map('trim', $districtArray);//Район

        $addressArray = explode(',', str_replace('пр-кт','', $address));//Адрес
        $addressArray = array_map('trim', $addressArray);

        if (count($addressArray) == 3) {
            $address = $addressArray[1].' '.$addressArray[2];
        } else if (count($addressArray) == 4) {
            $address = $addressArray[2].' '.$addressArray[3];
        } else {
            $address = implode(' ', $addressArray);
        }//Адрес

        $profileCompanies = $object['attributes']['customs'][$this->objectProfileOfCompany];//Вид деятельности

        $objectSlider = [];
        $objectSlider['footage'] = !empty($object['attributes']['total-area']) ? $object['attributes']['total-area'] : 100;//Слайдеры
        $objectSlider['budget_volume'] = !empty($object['attributes']['customs']['custom-61758']) ? $object['attributes']['customs']['custom-61758'] : 150000;//Слайдеры
        $objectSlider['budget_footage'] = !empty($object['attributes']['customs']['custom-61759']) ? $object['attributes']['customs']['custom-61759'] : 1500;//Слайдеры

        $typeOfPropertyObj = $object['attributes']['customs'][$this->typeOfPropertyField];//Вид деятельности

        $data = [
            'token' => $token,
            'id' => $id,
            'type' => $type,
            'metroSelect' => $metroSelect,
            'objectTypes' => $companyTypes,
            'typeOfProperties' => $typeOfProperties,
            'attributes' => $object['attributes'],
//            'disabledCompanies' => $disabledCompanies,
            'metro' => $metro,
            'districtArray' => $districtArray,
            'address' => $address,
            'profileCompanies' => $profileCompanies,
            'objectSlider' => $objectSlider,
            'typeOfPropertyObj' => $typeOfPropertyObj,
            'objectType' => $objectType,
        ];

        return view('objects.filter', $data);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function webhookEstateGet(Request $request)
    {
        $handler = new SalesupHandler(env('API_TOKEN'));
        $methods = $handler->methods;

        $filterClass = new MainFilter;

        $object = $methods->getObject($request->get('id'));
        $address = $object['attributes']['address'];
        $object_type = $request->get('object_type');
        $typeOfObject = $filterClass->checkCity($address);

        //Данные по фильтрам
        $objData = $filterClass->prepareData($request, $object, 'object', $object_type);

        if (empty($objData)) {
            $msg = "Выберите фильтры";
            return view('objects.error_page', ['msg' => $msg]);
        }

        $objData['object_type'] = $request->get('object_type');

        //Фильтрация по заявкам
        $filterOrdersClass = new FilterOrders;
        $filterOrders = [];

        //Получаем Список заявок
        Orders::query()->where('type', $object_type)
            ->chunk(1000, function($orders) use (&$filterOrders, $filterOrdersClass, $typeOfObject, $objData, &$count) {
                foreach ($orders as $order) {
                    $orderResponse = $filterOrdersClass->filter($order, $objData, $typeOfObject);

                    if (!empty($orderResponse)) {
                        $filterOrders[] = $order;
                    }
                }
            });

        if (empty($filterOrders)) {
            $msg = "Заявки не найдены";
            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objData)]);
        }

//        dd(count($filterOrders));
//
//        $footage = [];
//
//        foreach ($filterOrders as $order) {
//            $customs = json_decode($order->customs, true);
//
//            $footage[] = $customs['custom-67904'].' '.$customs['custom-67905'];
//        }
//
//        dd($footage);

//        foreach (array_chunk($filterOrders, 50) as $filterChunkOrders) {
//            dispatch(new OrderJobs($dealResponse['id'], json_encode($filterChunkOrders)));
//        }

        $dealResponses = [];

//        foreach (array_chunk($filterOrders, 100) as $filterChunkOrders) {
            //прописываем связи
            $companies = [];
            $contacts = [];
            $orderData = [];

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
                    'name' => $address,
                    'description' => $object['id'],
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

            $methods->attachDealToObject($dealResponse['id'], $object['id']);

            $dealResponses[] = $dealResponse;
//        }

        $viewData = [
            'deals' => $dealResponses,
            'object' => $object,
            'ordersCount' => count($filterOrders),
        ];

        return view('objects.success', $viewData);


//        if (count($filterOrders) > 50) {
//            return view('count', ['count' => count($filterOrders)]);
//        }



//        //прописываем связи
//        $companies = [];
//        $contacts = [];
//
//        foreach ($filterOrders as $filterOrder) {
//            $relationships = json_decode($filterOrder['relationships'], true);
//
//            //Компании
//            if (!empty($relationships['companies']['data'])) {
//                foreach ($relationships['companies']['data'] as $company) {
//                    $companies[$company['id']] = $company['id'];
//                }
//            }
//
//            //Контакты
//            if (!empty($relationships['contacts']['data'])) {
//                foreach ($relationships['contacts']['data'] as $contact) {
//                    $contacts[$contact['id']] = $contact['id'];
//                }
//            }
//        }
//
//        $orderData = [];
//
//        foreach ($filterOrders as $order) {
//            $orderData[] = [
//                'type' => 'orders',
//                'id' => $order['id'],
//            ];
//        }
//
//        //Проверяем компании
//        $companiesData = [];
//
//        if (!empty($companies)) {
//            foreach ($companies as $companyId) {
////                $company = $methods->getCompany($companyId);
////
////                if (!empty($company['relationships']['contacts']['data'])) {
////                    foreach ($company['relationships']['contacts']['data'] as $contact) {
////                        $contacts[$contact['id']] = $contact['id'];
////                    }
////                }
//
//                $companiesData[] = [
//                    'type' => 'companies',
//                    'id' => $companyId,
//                ];
//            }
//        }
//
//        $contactsData = [];
//
//        if (!empty($companies)) {
//            foreach ($contacts as $contactsId) {
//                $contactsData[] = [
//                    'type' => 'contacts',
//                    'id' => $contactsId,
//                ];
//            }
//        }
//
//        switch ($object_type) {
//            case 1:
//            case 2:
//                $stage = 32745;
//                break;
//            case 3:
//                $stage = 32747;
//                break;
//            default:
//                $stage = 32746;
//        }
//
//        $data = [
//            'attributes' => [
//                'name' => $address,
//                'description' => $object['id'],
//            ],
//            'relationships' => [
//                'contacts' => [
//                    'data' => $contactsData,
//                ],
//                'companies' => [
//                    'data' => $companiesData,
//                ],
//                'orders' => [
//                    'data' => $orderData,
//                ],
//                'stage-category' => [
//                    'type' => 'stage-category',
//                    'id' => $stage,//Воронка Аренда/Продажа
//                ],
//            ],
//        ];
//
//        $dealResponse = $methods->dealCreate($data);
//
//        foreach ($filterOrders as $order) {
//            $methods->attachDealToObject($dealResponse['id'], $object['id']);
//        }
//
//        $viewData = [
//            'deal' => $dealResponse,
//            'object' => $object,
//            'ordersCount' => count($filterOrders),
//        ];
//
//        return view('objects.success', $viewData);
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
