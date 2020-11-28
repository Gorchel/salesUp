<?php

namespace App\Classes\Filters;

class FilterCompany
{
    /**
     * @var string
     */
    protected $disabledCompaniesNameField = 'custom-65680';

    public function filter($company, $objData)
    {
        $attributes = $company['attributes'];

        //Не предлагать компаниям
//        if (isset($objData['disabled_company'])) {
//            $disabledCompanyArray = array_map('trim', explode(',',trim(mb_strtolower($objData['disabled_company']))));
//            $brandField = trim(mb_strtolower($attributes['customs'][$filterField['brand']]));
//
//            foreach ($disabledCompanyArray as $disabledCompany) {
//                if (empty($disabledCompany)) {
//                    continue;
//                }
//
//                if (strpos($brandField, $disabledCompany) !== false) {
//                    $checker = 0;
//                }
//            }
//        }

        return true;
    }
}
