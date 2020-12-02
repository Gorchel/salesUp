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

        $filterOrdersClass = new FilterOrders;

        $handler = new SalesupHandler($request->get('token'));
        $methods = $handler->methods;
        $orders = $methods->getOrder($id);
        $orderCustoms = $orders['attributes']['customs'];

        $address = $orderCustoms[$filterOrdersClass->getCustomArray($objectType, 'address')];
        $filterClass = new MainFilter();
//
//        //Конфиги
        $metroSelect = config('metro')[$filterClass->checkCity($address)];//Метро по городу
        $companyTypes = config('company_types');//Вид деятельности
        $typeOfProperties = config('type_of_property');//Тип недвижимости

        $city = $filterOrdersClass->getCustomArray($objectType, 'city');

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

        $type_of_activity = $filterOrdersClass->getCustomArray($objectType, 'type_of_activity');
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

        if (!empty($orderCustoms[$filterOrdersClass->getCustomArray($objectType, 'budget_volume')])) {
            $objectSlider['footage'] = $orderCustoms[$filterOrdersClass->getCustomArray($objectType, 'footage')];
        }

        $objectSlider['budget_volume'] = 100;

        if (!empty($orderCustoms[$filterOrdersClass->getCustomArray($objectType, 'budget_volume')])) {
            $objectSlider['budget_volume'] = $orderCustoms[$filterOrdersClass->getCustomArray($objectType, 'budget_volume')];
        }

        $objectSlider['budget_footage'] = 100;
        $budget_footage = $filterOrdersClass->getCustomArray($objectType, 'budget_footage');

        if (isset($budget_footage) && !empty($orderCustoms[$budget_footage])) {
            $objectSlider['budget_footage'] = $orderCustoms[$budget_footage];
        }

        //Вид деятельности
        $typeOfPropertyObj = $orderCustoms[$filterOrdersClass->getCustomArray($objectType, 'type_of_property')];

        //Срок окупаемости
//        $objectSlider['payback_period'] = 16;
//        $payback_period = $filterOrdersClass->getCustomArray($objectType, 'payback_period');
//
//        if (isset($payback_period) && !empty($orderCustoms[$payback_period])) {
//            $objectSlider['payback_period'] = $orderCustoms[$payback_period];
//        }

        $districtArray = [];
        $regionArray = [];

        //С арендодатором
        $isLandlord = '';

        $is_landlord = $filterOrdersClass->getCustomArray($objectType, 'is_landlord');

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

        $filterClass = new MainFilter;

        $object = $methods->getObject($request->get('id'));
        $address = $object['attributes']['address'];
        $typeOfObject = $filterClass->checkCity($address);

        //Данные по фильтрам
        $objData = $filterClass->prepareData($request, $object);

        if (empty($objData)) {
            $msg = "Выберите фильтры";
            return view('objects.error_page', ['msg' => $msg]);
        }

        $objData['object_type'] = $request->get('object_type');

        //Получаем Список заявок
        $orders = $methods->getOrders();

        if (empty($orders)) {
            $msg = "Заявки не найдены";
            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objData)]);
        }

        //Фильтрация по заявкам
        $filterOrdersClass = new FilterOrders;
        $filterOrders = [];

        foreach ($orders as $orderKey => $order) {
            $orderResponse = $filterOrdersClass->filter($order, $objData, $typeOfObject);

            if (!empty($orderResponse)) {
                $filterOrders[] = $order;
            }
        }

        if (empty($orders)) {
            $msg = "Заявки не найдены";
            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objData)]);
        }

        //прописываем связи
        $orderData = [];
        $companies = [];
        $contacts = [];

        foreach ($orders as $order) {
            $orderData[] = [
                'type' => 'orders',
                'id' => $order['id'],
            ];

            //Компании
            if (!empty($order['relationships']['companies']['data'])) {
                foreach ($order['relationships']['companies']['data'] as $company) {
                    $companies[$company['id']] = $company['id'];
                }
            }

            //Контакты
            if (!empty($order['relationships']['contacts']['data'])) {
                foreach ($order['relationships']['contacts']['data'] as $contact) {
                    $contacts[$contact['id']] = $contact['id'];
                }
            }
        }

        //Проверяем компании
        $companiesData = [];

        if (!empty($companies)) {
//            $filterCompanyClass = new FilterCompany;
            foreach ($companies as $companyId) {
                $company = $methods->getCompany($companyId);

                if (!empty($company['relationships']['contacts']['data'])) {
                    foreach ($company['relationships']['contacts']['data'] as $contact) {
                        $contacts[$contact['id']] = $contact['id'];
                    }
                }
//                $filterResponse = $filterCompanyClass->filter($company, $objData);
//
//                if (empty($filterResponse)) {
//                    unset($companies[$companyId]);
//                    continue;
//                }

//                $response = $handler->getContactByCompany($company, $companyContacts, $additionalContactData);
//
//                if (!empty($response)) {
//                    $companyData[] = [
//                        'type' => 'companies',
//                        'id' => $company['id'],
//                    ];
//                }

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
//        //Подбираем компании
//        $companies = $methods->getCompanies();
//
//        if (empty($companies)) {
//            $msg = "Компании не найдены";
//            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objectData)]);
//        }
//
//        //Получаем контакты по компаниям
//        $companyContacts = [];
//        $companyData = [];
//        $additionalContactData = [
//            'district' => $object['attributes']['customs'][$this->objectDistrictField],
//        ];
//
//        foreach ($companies as $company) {
//            $filterResponse = $this->filterCompany($objectData, $request,  $company);
//
//            if (empty($filterResponse)) {
//                continue;
//            }
//
//            $response = $handler->getContactByCompany($company, $companyContacts, $additionalContactData);
//
//            if (!empty($response)) {
//                $companyData[] = [
//                    'type' => 'companies',
//                    'id' => $company['id'],
//                ];
//            }
//        }
//
//        if (empty($companyContacts)) {
//            $msg = "Контакты отсутствуют";
//            return view('objects.error_page', ['msg' => $msg, 'errors' => $this->getErrors($request, $objectData)]);
//        }
//
//        $contactData = [];
//
//        foreach ($companyContacts as $contactId) {
//            $contactData[] = [
//                'type' => 'contacts',
//                'id' => $contactId
//            ];
//        }

        $data = [
            'attributes' => [
                'name' => 'Сделка по объекту',
                'description' => $object['attributes']['name'],
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
                    'id' => 32745,//Воронка постоянных клиентов
                ],
            ],
        ];

        $dealResponse = $methods->dealCreate($data);

        $objectResponse = $methods->attachDealToObject($dealResponse['id'], $object['id']);

        $viewData = [
            'deal' => $objectResponse,
            'object' => $object,
            'ordersCount' => count($orders),
        ];

        return view('objects.success', $viewData);
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
