<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Erp\Mapping;


class Pac extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory 
     */
    protected $eccPacAttributesOption;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory $eccPacAttributesOption,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->eccPacAttributesOption = $eccPacAttributesOption;
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption');
    }



    public function toOptionArray()
    {
        $arr = array();
        $items = $this->getCollection()->getItems();
        foreach ($items as $item) {
            $arr[] = array('value' => $item->getCode(), 'label' => $item->getCode());
        }
        return $arr;
    }

    public function toGridArray($column)
    {
        $arr = array(''=>' ');
        if(isset($column['pacattributes'])) {
            $explodeAttributes = explode('_',$column['pacattributes']);
            $getAttributeValues = $explodeAttributes[1];
            $collection = $this->eccPacAttributesOption->create();
            $collection->addFieldToFilter('parent_id', $getAttributeValues);
            foreach ($collection as $item) {
                $arr[$item->getCode()] = $item->getDescription();
            }
        }  
        return $arr;
    }

}
