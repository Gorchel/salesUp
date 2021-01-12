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

        if (isset($data['footage']) && !empty($data['footage'])) {
            if (($type == 'order') && !empty($data['footage'])) {
                if (isset($sliderData['footage'])) {
                    $data['footage'] = $this->getArrayByPercent($sliderData['footage'], 'footage', $data);
                } else {
                    $data['footage'] = null;
                }
            } else {
                $data['footage'] = $this->getArrayByPercent($order['attributes']['total-area'], 'footage', $data);
            }
        }

        if (isset($data['budget_volume']) && !empty($sliderData['budget_volume'])) {
            $budget_volume = $this->getArrayByPercent($sliderData['budget_volume'], 'budget_volume', $data);

            if (!empty($budget_volume)) {
                $data['budget_volume'] = $budget_volume;
            } else {
                $data['budget_volume'] = null;
            }
        }

        if (isset($sliderData['budget_footage']) && !empty($sliderData['budget_footage'])) {
            $budget_volume = $this->getArrayByPercent($sliderData['budget_footage'], 'budget_footage', $data);

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
