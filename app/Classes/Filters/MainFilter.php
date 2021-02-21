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
        'budget_volume' => 'custom-61759', 'budget_footage' => 'custom-61758'
    ];

    /**
     * @return array
     */
    public function prepareData(Request $request, $order, $type = "order", $object_type = null)
    {
        $data = [];

        foreach ($this->filterList as $key) {
            if (!empty($request->get($key.'_check')) && !empty($request->get($key))) {
                $data[$key] = $request->get($key);
            }
        }

        $orderCustoms = $order['attributes']['customs'];

        $sliderData = $this->getSliderOrderData($object_type, $orderCustoms, $type, $order);

        $totalArea = 100;

        if (isset($data['footage']) && !empty($data['footage'])) {
            if (($type == 'order') && !empty($data['footage'])) {
                if (isset($sliderData['footage'])) {
                    $totalArea = $sliderData['footage'];
                }

//                if (isset($sliderData['footage'])) {
                    $data['footage'] = $this->getArrayByPercent($totalArea, 'footage', $data);
//                } else {
//                    $data['footage'] = null;
//                }
            } else {
                if (!empty($order['attributes']['total-area'])) {
                    $totalArea = $order['attributes']['total-area'];
                }

                $data['footage'] = $this->getArrayByPercent($totalArea, 'footage', $data);
            }
        }

        $budgetVolume = 150000;

        if (!empty($sliderData['budget_volume'])) {
            $budgetVolume = $sliderData['budget_volume'];
        }

        if (isset($data['budget_volume']) && !empty($budgetVolume)) {
            $budget_volume = $this->getArrayByPercent($budgetVolume, 'budget_volume', $data);

            if (!empty($budget_volume)) {
                $data['budget_volume'] = $budget_volume;
            } else {
                $data['budget_volume'] = null;
            }
        }

        $budgetFootage = 1500;

        if (!empty($sliderData['budget_footage'])) {
            $budgetFootage = $sliderData['budget_footage'];
        }

        if (isset($data['budget_footage']) && !empty($budgetFootage)) {
            $budget_volume = $this->getArrayByPercent($budgetFootage, 'budget_footage', $data);

            if (!empty($budget_volume)) {
                $data['budget_footage'] = $budget_volume;
            } else {
                $data['budget_footage'] = null;
            }
        }

        if (isset($sliderData['payback_period'])) {
            $data['payback_period'] = explode(',', $sliderData['payback_period']);
        }

        return $data;
    }

    public function getSliderOrderData($objectTypeId, $orderCustoms, $type = 'order', $order = null)
    {
        $objectSlider = [];

        $filterOrdersClass = new FilterOrders;

        if ($type == 'order') {
            $ranges = $filterOrdersClass->getCustomArray($objectTypeId, 'ranges');

            $defaultData = [
                'footage' => 100,
                'budget_volume' => 150000,
                'budget_footage' => 1500,
            ];

            foreach (['footage', 'budget_volume', 'budget_footage'] as $key) {
                if (isset($ranges[$key])) {
                    if (isset($ranges[$key]['value']) && !empty($ranges[$key]['value'])) {
                        $keyValue = $orderCustoms[$ranges[$key]['value']];

                        if (empty($keyValue)) {
                            $keyValue = $defaultData[$key];
                        }

                        $objectSlider[$key] = $keyValue;
                    } else {
                        $from = (int) $orderCustoms[$ranges[$key]['from']];
                        $to = (int) $orderCustoms[$ranges[$key]['to']];

                        $value = ($from + $to) / 2;

                        if (empty($keyValue)) {
                            $value = $defaultData[$key];
                        }

                        $objectSlider[$key] = $value;
                    }
                }
            }//Слайдеры
        } else {
            if ($objectTypeId == 1) {
                $objectTypeId = 4;
            } else if ($objectTypeId == 2) {
                $objectTypeId = 3;
            }

            $ranges = $filterOrdersClass->getCustomPropertyArray($objectTypeId);

            foreach (['budget_volume', 'budget_footage'] as $key) {
                if (isset($ranges[$key])) {
                    $objectSlider[$key] = $orderCustoms[$ranges[$key]];
                }
            }
        }

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
