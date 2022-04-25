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
class Pac extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->commMessagingHelper;
        $index = $this->getColumn()->getIndex();
       
        $options = $this->getColumn()->getOptions();
        
        if(!empty($options)) {
            $explodeVals = json_decode($this->getColumn()->getDatatypejson(),true);
            $attributes = ($row->getAttributes()) ? $row->getAttributes()->getasarrayAttribute() : array();
            foreach ($attributes as $attributeVals) {
                if ((!empty($attributeVals['code'])) && ($explodeVals['parentclass'] ==$attributeVals['class']) && ($attributeVals['code'] ==$explodeVals['pacattributeName'])){
                   $arrayKeys = array_keys($options);
                   if(in_array($attributeVals['value'], $arrayKeys)) {
                       return $options[$attributeVals['value']];
                   }
                } 
            }
        }
    }

}