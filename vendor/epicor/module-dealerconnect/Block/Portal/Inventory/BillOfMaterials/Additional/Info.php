<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Additional;

/**
 * Column Renderer for Sales ERP Account Select Grid Address
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Info extends \Epicor\Dealerconnect\Block\Portal\Inventory\BillOfMaterials\Generic\Listing\Info
{
    protected $dateFormates = array('warranty_start','warranty_expiration', 'dealer_warranty_start','dealer_warranty_expiration');

    protected $materialTransDateFormats = [
        'warranty_start' => 'warranty_start_date',
        'warranty_expiration' => 'warranty_expiration_date',
        'dealer_warranty_start' => 'dealer_warranty_start_date',
        'dealer_warranty_expiration' => 'dealer_warranty_expiration_date'
    ];

    protected $ignoreAttributes = [
        'unit_of_measure_code',
        'quantity',
        'material_id',
        'job_num',
        'assembly_seq',
        'revision_num',
        'lot_numbers_lot_number'
    ];

    protected $warrantyInfo = array('warranty_code', 'warranty_comment', 'warranty_expiration_date', 'warranty_start_date');

    public function _construct()
    {
        parent::_construct();
        $debm = $this->registry->registry('debm_info_details');
        $details = $this->getGridDetails();
        $attributes = $debm->getAttributes();
        $conversionAttributes = array();
        $conversionAttrChk = array();
        foreach($details as $convertedVals) {
            if(!$convertedVals['pac']) {
                if(in_array($convertedVals['index'], $this->dateFormates)) {
                    $_index = isset($this->materialTransDateFormats[$convertedVals['index']]) ? $this->materialTransDateFormats[$convertedVals['index']] : $convertedVals['index'];
                    $DebmVals = $this->renderDate($debm->getData($_index));
                } else {
                    switch (true) {
                        case ($convertedVals['index'] == "serial_numbers_serial_number"):
                            $DebmVals = $debm->getData($convertedVals['index'])?:$debm->getData('new_serial_number');
                            if(is_array($DebmVals)){
                                $DebmVals = implode(',', $DebmVals);
                            }
                            break;
                        case ($convertedVals['index'] == "lot_numbers_lot_number"):
                            $DebmVals = $debm->getData($convertedVals['index'])?:$debm->getData('new_lot_number');
                            if(is_array($DebmVals)){
                                $DebmVals = implode(',', $DebmVals);
                            }
                            break;
                        case ($convertedVals['index'] == "material_iD"):
                            $DebmVals = $debm->getData('material_id');
                            break;
                        case ($convertedVals['index'] == "warranty_code"):
                            $DebmVals = $debm->getData($convertedVals['index']);
                            $warranty = $this->warranty->create()->load($DebmVals, 'code');
                            if ($warranty->getId()) {
                                $DebmVals = $warranty->getDescription();
                            }
                            break;
                        case ($convertedVals['index'] == "product_code"):
                            $DebmVals = $debm->getData('new_product_code');
                            break;
                        default:
                            $index= $convertedVals['index'];
                            if (strpos($index, '>') !== false) {
                                $getUserDefined =  explode( ">", $index,4 );
                                if(isset($getUserDefined[1])) {
                                    $decamelize = $this->decamelize($getUserDefined[0]);
                                    $decamelizeValues = $this->decamelize($getUserDefined[1]);
                                    if(count($getUserDefined) =="3") {
                                        $decamelizeValues1 = $this->decamelize($getUserDefined[2]);
                                        $DebmVals = (isset($debm->getData($decamelize)[$decamelizeValues][$decamelizeValues1]))? $debm->getData($decamelize)[$decamelizeValues][$decamelizeValues1]: '';
                                    } elseif(count($getUserDefined) =="4") {
                                        $decamelizeValues1 = $this->decamelize($getUserDefined[2]);
                                        $decamelizeValues2 = $this->decamelize($getUserDefined[3]);
                                        $DebmVals = (isset($debm->getData($decamelize)[$decamelizeValues][$decamelizeValues1][$decamelizeValues2]))? $debm->getData($decamelize)[$decamelizeValues][$decamelizeValues1][$decamelizeValues2]: '';
                                    } else {
                                        $DebmVals = (isset($debm->getData($decamelize)[$decamelizeValues]))? $debm->getData($decamelize)[$decamelizeValues]: '';
                                    }
                                    if ($this->check_your_datetime($DebmVals)) {
                                        $DebmVals = $this->renderDate($DebmVals);
                                    }
                                } else {
                                    $DebmVals ='';
                                }
                            } else {
                                $DebmVals = $debm->getData($convertedVals['index']);
                            }
                            break;
                    }
                }
                if (!in_array($convertedVals['index'], $this->ignoreAttributes)) {
                    $conversionAttributes[$convertedVals['index']] = array($convertedVals['header'],$DebmVals);
                    $conversionAttrChk[$convertedVals['index']] = $DebmVals;
                }
            } else {
                $getAttributeValues = $this->pacAttributesRenderer($attributes,$convertedVals);
                $conversionAttributes[$convertedVals['index']] = array($convertedVals['header'],$getAttributeValues);
            }
        }
        $this->_infoData = $conversionAttributes;
        $this->_infoDataCheck = $conversionAttrChk;
        $this->setTitle(__(''));
    }

    public function check_your_datetime($myDateString) {
        return (bool)strtotime($myDateString);
    }

    public function decamelize($string) {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }

}