<?php

namespace App\Classes\Filters;

use Illuminate\Http\Request;
use App\Classes\Filters\MainFilter;

/**
 * Class FilterOrders
 * @package App\Classes\Filters;
 */
class FilterOrders
{
    /**
     * @var array
     */
    protected $customFields = [
        1 => [//сдам
            'type_of_property' => 'custom-67826',
            'address' => 'custom-67827',
            'city' => [
                'street' => [
                    1 => ['custom' => 'custom-67921', 'type' => 'str'],//msk
                    2 => ['custom' => 'custom-67916', 'type' => 'str'],//spb
                ],
                'district' => [
                    1 => ['custom' => 'custom-67942', 'type' => 'array'],//msk
                    2 => ['custom' => 'custom-67941', 'type' => 'array'],//spb
                ],
                'metro' => [
                    1 => 'custom-67940',//msk
                    2 => 'custom-67939',//spb
                ],
            ],
            'address_program' => 'custom-67911',
            'client_type' => 'custom-67822',
            'type_of_activity' => 'custom-67947',
            'footage' => 'custom-67828',
            'budget_volume' => 'custom-67829',
            'budget_footage' => 'custom-67829',//????
            'ranges' => [
                'footage' => [
                    'value' => 'custom-67828',
                ], //По площади (кв/м)
                'budget_volume' => [
                    'value' => 'custom-67829',
                ],//По бюджету, руб.мес.  в диапазоне от до
                'payback_period' => 'custom-67892',//Предполагаемый срок окупаемости
            ],
        ],
        2 => [//продам
            'type_of_property' => 'custom-67849',
            'address' => 'custom-67850',
            'city' => [
                'district' => [
                    1 => ['custom' => 'custom-67945', 'type' => 'array'],//msk
                    2 => ['custom' => 'custom-67943', 'type' => 'array'],//spb
                ],
                'metro' => [
                    1 => 'custom-67946',//msk
                    2 => 'custom-67944',//spb
                ],
            ],
            'address_program' => 'custom-67911',
            'client_type' => 'custom-67822',
            'footage' => 'custom-67851',
            'budget_volume' => 'custom-67853',
            'payback_period' => 'custom-67853',
            'is_landlord' => 'custom-67855',
            'ranges' => [
                'footage' => [
                    'value' => 'custom-67851',
                ], //По площади (кв/м)
                'budget_volume' => [
                    'value' => 'custom-67853',
                ],//По бюджету, руб.мес.  в диапазоне от до
                'budget_footage' => [
                    'value' => 'custom-67854',
                ],//По бюджету за 1 кв/м в мес
                'payback_period' => 'custom-67892',//Предполагаемый срок окупаемости
            ],
        ],
        3 => [//куплю
            'type_of_property' => 'custom-61755',
            'address' => 'custom-67827',
            'ranges' => [
                'footage' => [
                    'from' => 'custom-67882',
                    'to' => 'custom-67883'
                ], //По площади (кв/м)
                'budget_volume' => [
                    'from' => 'custom-67880',
                    'to' => 'custom-67881'
                ],// По бюджету, руб.мес.  в диапазоне от до
            ],
        ],
        4 => [//сниму
            'type_of_property' => 'custom-61755',
            'address' => 'custom-67827',
            'ranges' => [
                'footage' => [
                    'from' => 'custom-67904',
                    'to' => 'custom-67905'
                ], //По площади (кв/м)
                'budget_volume' => [
                    'from' => 'custom-67906',
                    'to' => 'custom-67907'
                ],// По бюджету, руб.мес.  в диапазоне от до
                'budget_footage' => [
                    'from' => 'custom-67908',
                    'to' => 'custom-67909'
                ],//По бюджету за 1 кв/м в мес
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $customPropertyFields = [
        3 => [//Куплю
            'budget_volume' => 'custom-61706',
            'budget_footage' => 'custom-61708',
            'payback_period' => 'custom-61718',
            'type_of_property' => 'custom-61755',
            'type_of_activity' => 'custom-61774',
            'metro' => 'custom-65155',
            'district' => 'custom-65154',
//            'address' => 'custom-65154',
            'is_landlord' => 'custom-61757',
        ],
        4 => [//сниму
            'budget_volume' => 'custom-61758',
            'budget_footage' => 'custom-61759',
            'payback_period' => 'custom-61718',
            'type_of_property' => 'custom-61755',
            'type_of_activity' => 'custom-61774',
            'metro' => 'custom-65155',
            'district' => 'custom-65154',
//            'address' => 'custom-65154',
            'is_landlord' => 'custom-61757',
        ]
    ];

    /**
     * @param $order
     * @param $objData
     * @param int $typeOfObjectAddress
     * @return bool
     */
    public function filter($order, $objData, $typeOfObjectAddress = 1)
    {
        $attributes = json_decode($order['attributes'], true);
        $customs = json_decode($order['customs'], true);
        $mainChecker = 0;

        $customFields = $this->customFields[$objData['object_type']];//Массив с ключами

        //Тип недвижимости / вид деятельности
        foreach (['type_of_property','type_of_activity'] as $key) {
            if (!empty($objData[$key])) {
                $ordersValues = $this->getValue($key, $customs, $customFields);
                if (is_array($ordersValues)) {
                    $ordersValues = array_diff($this->getValue($key, $customs, $customFields), ['']);

                    if (!empty($ordersValues)) {
                        $mainChecker = 1;

                        if (is_array($objData[$key]) && empty(array_intersect($ordersValues, $objData[$key]))) {
                            return false;
                        }
                    }
                }
            }
        }

        //Значение/массив / Адресная программа / тип клиента
        foreach (['address_program', 'client_type'] as $key) {
            if (!empty($objData[$key])) {
                $ordersValues = $this->getValue($key, $customs, $customFields);

                if (is_array($ordersValues)) {
                    $ordersValues = array_diff(array_map('trim', $ordersValues), ['']);

                    if (!empty($ordersValues)) {
                        $mainChecker = 1;

                        if (!in_array($objData[$key], $ordersValues)) {
                            return false;
                        }
                    }
                }
            }
        }

        //Улица, Дом, район
        foreach (['district','street'] as $key) {
            if (empty($objData[$key])) {//Если пустое значение поля
                continue;
            }

            $valueArray = array_map('trim', explode(',',trim(mb_strtolower($objData[$key]))));//Значение в фильтре

            if (!isset( $customFields['city'][$key])) {
                continue;
            }

            $customArray = $customFields['city'][$key][$typeOfObjectAddress];//Значения в поле

            //проверяем по городам
            $checker = 0;

            //Проверяем наличие
            if (!isset($customs[$customArray['custom']])) {
                continue;
            }

            $objectValue = $customs[$customArray['custom']];//Значение в заявке

            if ($customArray['type'] != 'array') {
                $objectValue = array_diff(array_map('trim', explode(',',trim(mb_strtolower($objectValue)))),['']);
            } else {
                $objectValue = array_diff(array_map('mb_strtolower', $objectValue),['']);
            }

            if (empty($objectValue)) {
                continue;
            }

            $mainChecker = 1;

            foreach ($objectValue as $objVal) {//Поиск по полю в заявке
                foreach ($valueArray as $value) {//Значение в фильтре
                    if (empty($keyEl)) {
                        continue;
                    }

                    if (strpos($objVal, $value) === false) {
                        $checker = 1;
                    }
                }
            }

            if ($checker == 0) {
                return false;
            }
        }

        //Проверяем метро
        if (!empty($objData['metro'])) {
            $valueArray = $objData['metro'];//Значение в фильтре
            $objectValue = array_diff($customs[$customFields['city']['metro'][$typeOfObjectAddress]], ['']);//Значение в заявке

            if (!empty($objectValue)) {
                $mainChecker = 1;

                if (empty(array_intersect($objectValue, $valueArray))) {
                    return false;
                }
            }
        }

        //проверяем по площади
        foreach (['footage','budget_volume','budget_footage'] as $key) {
            if (empty($objData[$key])) {//Если пустое значение поля
                continue;
            }

            if (!isset($customFields['ranges'][$key])) {
                continue;
            }

            $mainChecker = 1;

            $ranges = $customFields['ranges'][$key];//from/to

            $from = intval($customs[$ranges['from']]);
            $to = intval($customs[$ranges['to']]);

            //Корректировка тысяч
            if ($key == 'budget_volume') {
                $from = $from * 1000;
                $to = $to * 1000;
            }

            $crossInterval = $this->crossingInterval($objData[$key][0], $objData[$key][1], $from, $to);

            if (empty($crossInterval)) {
                return false;
            }
        }

        //Предполагаемый срок окупаемости в мес
        if (!empty($objData['payback_period'])) {
            if (isset($customFields['ranges']['payback_period'])) {
                $mainChecker = 1;
                $paybackValue = intval($customs[$customFields['ranges']['payback_period']]);//payback_period

                $from = intval($objData['payback_period'][0]);
                $to = intval($objData['payback_period'][1]);

                if ($paybackValue < $from || $to < $paybackValue) {
                    return false;
                }
            }
        }

        if ($mainChecker == 0) {
            return false;
        }

        return true;
    }

    /**
     * @param $object
     * @param $objData
     * @param int $typeOfObjectAddress
     * @return bool
     */
    public function filterProperty($property, $objData, $typeOfObjectAddress = 1, $object_type)
    {
        $customFields = $this->customPropertyFields[$object_type];//Массив с ключами

        $attributes = json_decode($property['attributes'], true);
        $customs = json_decode($property['customs'], true);
        $mainChecker = 0;

        //Тип недвижимости / Адресная программа / тип клиента / вид деятельности
        foreach (['type_of_property','type_of_activity','metro'] as $key) {
            if (!empty($objData[$key])) {
                $ordersValues = array_diff($this->getValue($key, $customs, $customFields), ['']);

                if (!empty($ordersValues)) {
                    $mainChecker = 1;

                    if (empty(array_intersect($ordersValues, $objData[$key]))) {
                        return false;
                    }
                }
            }
        }

        //проверка есть ли арендатор
        if (!empty($objData['is_landlord'])) {
            $value = trim($objData['is_landlord']);

            $ordersValues = array_diff($this->getValue('is_landlord', $customs, $customFields), ['']);
//            dd($ordersValues);
            if (!empty($ordersValues)) {
                $mainChecker = 1;

                if (!in_array($value, $ordersValues)) {
                    return false;
                }
//                dd($ordersValues);
            }
        }

        //проверяем по площади
        if (!empty($objData['footage'])) {//Если пустое значение поля
            $value = intval($attributes['total-area']);
            if (!empty($value)) {
                $mainChecker = 1;

                $crossInterval = $this->crossingIntervalByValue($value, $objData['footage'][0], $objData['footage'][1]);

                if (empty($crossInterval)) {
                    return false;
                }
            }
        }

        foreach (['budget_volume','budget_footage'] as $key) {
            if (empty($objData[$key])) {//Если пустое значение поля
                continue;
            }

            $mainChecker = 1;

            $value = intval($customs[$customFields[$key]]);

            if (empty($value)) {
                continue;
            }

            //Корректировка тысяч
            if ($key == 'budget_volume') {
                $value = $value / 1000;
            }

            $crossInterval = $this->crossingIntervalByValue($value, $objData[$key][0], $objData[$key][1]);

            if (empty($crossInterval)) {
                return false;
            }
        }

        //район
        foreach (['district'] as $key) {
            if (empty($objData[$key])) {//Если пустое значение поля
                continue;
            }

            $valueArray = array_map('trim', explode(',',trim(mb_strtolower($objData[$key]))));//Значение в фильтре

            if (!isset( $customFields[$key])) {
                continue;
            }

            $customArray = $customFields[$key];//Значения в поле

            //проверяем по городам
            $checker = 0;

            //Проверяем наличие
//            if (!isset([$customs])) {
//                continue;
//            }

            $objectValue = $customs[$customArray];//Значение в заявке
            $objectValue = array_diff(array_map('mb_strtolower', $objectValue),['']);

            if (empty($objectValue)) {
                continue;
            }

            $mainChecker = 1;

            foreach ($objectValue as $objVal) {//Поиск по полю в заявке
                foreach ($valueArray as $value) {//Значение в фильтре
                    if (empty($value)) {
                        continue;
                    }

                    if (strpos($objVal, $value) !== false) {
                        $checker = 1;
                    }
                }
            }

            if ($checker == 0) {
                return false;
            }
        }

        //Проверяем по адресу
        foreach (['street','region'] as $key) {
            if (empty($objData[$key])) {//Если пустое значение поля
                continue;
            }

            $valueArray = array_map('trim', explode(',',trim(mb_strtolower($objData[$key]))));//Значение в фильтре

            if (empty($valueArray)) {//Если пустое значение поля
                continue;
            }

            $checker = 0;
            $mainChecker = 1;

            $objectValue = array_map('trim', explode(',',trim(mb_strtolower($attributes['address']))));//Значение в поле
            $objectValue = array_diff(array_map('mb_strtolower', $objectValue),['']);

            if (empty($objectValue)) {
                continue;
            }

            foreach ($objectValue as $objVal) {//Поиск по полю в заявке
                foreach ($valueArray as $value) {//Значение в фильтре
                    if (empty($value)) {
                        continue;
                    }

                    if (strpos($objVal, $value) !== false) {
                        $checker = 1;
                    }
                }
            }

            if ($checker == 0) {
                return false;
            }
        }

        //Предполагаемый срок окупаемости в мес
        if (!empty($objData['payback_period'])) {
            if (!empty($objData['payback_period'])) {//Если пустое значение поля
                $value = intval($customs[$customFields['payback_period']]);

                if (!empty($value)) {
                    $mainChecker = 1;
                    $crossInterval = $this->crossingIntervalByValue($value, $objData['payback_period'][0], $objData['payback_period'][1]);

                    if (empty($crossInterval)) {
                        return false;
                    }
                }
            }
        }

        if ($mainChecker == 0) {
            return false;
        }

        return true;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getCustomArray($type, $field)
    {
        if (isset($this->customFields[$type][$field])) {
            return $this->customFields[$type][$field];
        }

        return null;
    }

    /**
     * @param $type
     * @param $field
     * @return mixed|null
     */
    public function getCustomPropertyArray($type)
    {
        if (isset($this->customPropertyFields[$type])) {
            return $this->customPropertyFields[$type];
        }

        return null;
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
     * @param $field
     * @param $startInt
     * @param $finishInt
     * @return int
     */
    public function crossingIntervalByValue($field, $startInt, $finishInt) {
        if (
            $field >= $startInt && $field <= $finishInt
        ) {
            return 1;
        }

        return 0;
    }

    /**
     * @param $key
     * @param $customs
     * @param $customArray
     * @return mixed
     */
    protected function getValue($key, $customs, $customArray)
    {
        if (!isset($customArray[$key])) {
            return false;
        }

        return $customs[$customArray[$key]];
    }
}
