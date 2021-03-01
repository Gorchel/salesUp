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
        $token = env('API_TOKEN');
        $type = $request->get('type');
        $objectTypeId = $request->has('object_type') ? $request->get('object_type') : 4;
        $cityTypeId = $request->has('city_type') ? $request->get('city_type') : 2;

        $filterOrdersClass = new FilterOrders;

        $handler = new SalesupHandler($token);
        $methods = $handler->methods;

        $order = $methods->getOrder($id);
        $orderCustoms = $order['attributes']['customs'];

        $filterClass = new MainFilter();
//
//        //Конфиги
        $metroSelect = config('metro')[$cityTypeId];//Метро по городу
        $companyTypes = config('company_types');//Вид деятельности
        $typeOfProperties = config('type_of_property')[$objectTypeId];//Тип недвижимости

//        $type_of_activity = $filterOrdersClass->getCustomArray($objectTypeId, 'type_of_activity');
        $profileCompanies = [];

//        if (!empty($type_of_activity)) {
//            $profileCompanies = $orderCustoms[$type_of_activity];//Вид деятельности
//        }

//
        $objectSlider = $filterClass->getSliderOrderData($objectTypeId, $orderCustoms);

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
        $cityTypeId = $request->get('cityTypeId');

        $filterClass = new MainFilter;
        $filterOrdersClass = new FilterOrders;

        $order = $methods->getOrder($request->get('id'));

        //Данные по фильтрам
        $objData = $filterClass->prepareData($request, $order, 'order', $object_type);

        if (empty($objData)) {
            $msg = "Выберите фильтры";
            return view('objects.error_page', ['msg' => $msg]);
        }

        $objData['object_type'] = $object_type;
        $filterOrders = [];

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
            return view('orders.error_page', [
                'msg' => $msg,
                'errors' => $this->getErrors($request, $objData),
                'request' => $this->prepareRequest($request)
            ]);
        }

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
                $objDeals = [];

                if (isset($order['relationships']['deals']['data'])) {
                    foreach ($order['relationships']['deals']['data'] as $objDeal) {
                        $objDeals[$objDeal['id']] = $objDeal['id'];
                    }
                }

                $objDeals[$dealResponse['id']] = $dealResponse['id'];

                $methods->attachDealsToObject($objDeals, $order['id']);
            }
        }

        $viewData = [
            'deal' => $dealResponse,
            'objectsCount' => count($filterOrders),
        ];

        return view('orders.success', $viewData);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function prepareRequest($request)
    {
        return [
            'token' => $request->get('token'),
            'ids' => [$request->get('id')],
        ];
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
