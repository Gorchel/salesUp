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
        'footage','budget_volume','budget_footage','payback_period','client_type',
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
    public function prepareData(Request $request, $object)
    {
        $data = [];

        foreach ($this->filterList as $key) {
            if (!empty($request->get($key.'_check')) && !empty($request->get($key))) {
                $data[$key] = $request->get($key);
            }
        }

        if (isset($data['footage'])) {
            $data['footage'] = $this->getArrayByPercent($object['attributes']['total-area'], 'footage', $data);;
        }

        foreach ($this->objectFields as $key => $field) {
            if (isset($data[$key])) {
                $data[$key] = $this->getArrayByPercent($object['attributes']['customs'][$field], $key, $data);
            }
        }

        if (isset($data['payback_period'])) {
            $data['payback_period'] = explode(',', $data['payback_period']);
        }

        return $data;
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
