<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Portal\Inventory\Search\Listing\Renderer;


/**
 * Currency display, converts a row value to currency display
 *
 * @author Gareth.James
 */
class Pacstatic extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;    

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
       $helper = $this->commMessagingHelper;
       $index = $this->getColumn()->getIndex();
       $pacAttributeVals = $row->getData($index);
       $dataType = $this->getColumn()->getDatatypejson();
       $jsonDecode = json_decode($dataType,true);
       $pacAttribute = $jsonDecode['pacattribute'];
       $parentClass  = $jsonDecode['parentclass']; 
       $dataTypeMode = $jsonDecode['datatype']; 
       $attributes   = ($row->getAttributes()) ? $row->getAttributes()->getasarrayAttribute() : array();
       $getParams    = $jsonDecode['pacattributeName'];
       foreach ($attributes as $attributeVals) {
            if ((!empty($attributeVals['code'])) && ($attributeVals['code'] == $getParams) && ($attributeVals['class'] == $parentClass)) {
                if($dataTypeMode =="date") {
                  return $this->renderDate($attributeVals['value']);  
                } else {
                  return $attributeVals['value'];  
                }
            }
        }
    }
    
    
    public function renderDate($date)
    {
        $helper = $this->customerconnectHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Data */
        $data = '';
        if (!empty($date)) {
            try {
                //M1 > M2 Translation Begin (Rule 32)
                //$data = $helper->getLocalDate($row->getData($index), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
                $data = $helper->getLocalDate($date, \IntlDateFormatter::MEDIUM);
                //M1 > M2 Translation End
            } catch (\Exception $ex) {
               // $data = $row->getData($index);
            }
        }

        return $data;
    }    

}