<?php

namespace App\Classes\Filters;

use Illuminate\Http\Request;

/**
 * Class MainFilter
 * @package App\Classes\Filters;
 */
class MainFilter
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $filterList = [
        'type_of_property','street','type_of_activity','address_program','district','metro',
        'footage','budget_volume','budget_footage','payback_period','client_type','region','is_landlord',
        'client_type'
//        'disabled_company',
    ];

    /**
     * @var array
     */
    public $objectFields = [
//        'footage' => 'custom-64803',
        'budget_volume' => 'custom-61758', 'budget_footage' => 'custom-61759'
    ];

    /**
     * @return array
     */
    public function prepareData(Request $request, $order, $type = "object", $object_type = null)
    {
        $data = [];

        foreach ($this->filterList as $key) {
            if (!empty($request->get($key.'_check')) && !empty($request->get($key))) {
                $data[$key] = $request->get($key);
            }
        }

        $orderCustoms = $order['attributes']['customs'];

        $sliderData = $this->getSliderOrderData($object_type, $orderCustoms);

        if (isset($data['footage']) && !empty($sliderData['footage'])) {
            $data['footage'] = $this->getArrayByPercent($sliderData['footage'], 'footage', $data);;
        }

        if (isset($data['budget_volume']) && !empty($sliderData['budget_volume'])) {
            $budget_volume = $this->getArrayByPercent($sliderData['budget_volume'], 'budget_volume', $data);

            if (!empty($budget_volume)) {
                $data['budget_volume'] = $budget_volume;
            } else {
                $data['budget_volume'] = null;
            }
        }

        if (isset($data['budget_footage']) && !empty($sliderData['budget_footage'])) {
            $budget_volume = $this->getArrayByPercent($sliderData['budget_footage'], 'budget_footage', $data);

            if (!empty($budget_volume)) {
                $data['budget_footage'] = $budget_volume;
            } else {
                $data['budget_footage'] = null;
            }
        }

//        if ($type == 'object') {
//            if (isset($data['footage'])) {
//                $data['footage'] = $this->getArrayByPercent($object['attributes']['total-area'], 'footage', $data);;
//            }
//
//            foreach ($this->objectFields as $key => $field) {
//                if (isset($data[$key])) {
//                    $data[$key] = $this->getArrayByPercent($object['attributes']['customs'][$field], $key, $data);
//                }
//            }
//        } else {
//            $filterOrdersClass = new FilterOrders;
//
//            if (isset($data['footage'])) {
//                $footage = $this->getArrayByPercent($object['attributes']['customs'][$filterOrdersClass->getCustomArray($object_type, 'footage')], 'footage', $data);
//
//                if (!empty($footage)) {
//                    $data['footage'] = $footage;
//                } else {
//                    $data['footage'] = null;
//                }
//            }
//
//            if (isset($data['budget_volume'])) {
//                $budget_volume = $this->getArrayByPercent($object['attributes']['customs'][$filterOrdersClass->getCustomArray($object_type, 'budget_volume')], 'budget_volume', $data);
//
//                if (!empty($budget_volume)) {
//                    $data['budget_volume'] = $budget_volume;
//                } else {
//                    $data['budget_volume'] = null;
//                }
//            }
//
//            if (isset($data['budget_footage'])) {
//                $key = $filterOrdersClass->getCustomArray($object_type, 'budget_footage');
//
//                if (!empty($key)) {
//                    $budget_footage = $this->getArrayByPercent($object['attributes']['customs'][$key], 'budget_footage', $data);
//
//                    if (!empty($budget_footage)) {
//                        $data['budget_footage'] = $budget_footage;
//                    } else {
//                        $data['budget_footage'] = null;
//                    }
//                }
//            }
//        }

        if (isset($data['payback_period'])) {
            $data['payback_period'] = explode(',', $data['payback_period']);
        }

        return $data;
    }

    public function getSliderOrderData($objectTypeId, $orderCustoms)
    {
        $objectSlider = [];

        $filterOrdersClass = new FilterOrders;
        $ranges = $filterOrdersClass->getCustomArray($objectTypeId, 'ranges');

        foreach (['footage', 'budget_volume', 'budget_footage'] as $key) {
            if (isset($ranges[$key])) {
                if (isset($ranges[$key]['value']) && !empty($ranges[$key]['value'])) {
                    $keyValue = $orderCustoms[$ranges[$key]['value']];

                    if (!empty($keyValue)) {
                        $objectSlider[$key] = $keyValue;
                    }
                } else {
                    $from = (int) $orderCustoms[$ranges[$key]['from']];
                    $to = (int) $orderCustoms[$ranges[$key]['to']];

                    $value = ($from + $to) / 2;

                    if (!empty($value)) {
                        $objectSlider[$key] = $value;
                    }
                }
            }
        }//Слайдеры

        return $objectSlider;
    }

    /**
     * @param $address
     * @return int
     */
    public function checkCity($address)
    {
        if (strpos($address,'Петербург') == true) {
            return 2;
        }
        return 1;
    }

    /**
     * @param $value
     * @param string $key
     * @param array $data
     * @return array
     */
    protected function getArrayByPercent($value, string $key, $data)
    {
        if (empty($value)) {
            return [];
        }

        $percentArr = explode(',', $data[$key]);

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
