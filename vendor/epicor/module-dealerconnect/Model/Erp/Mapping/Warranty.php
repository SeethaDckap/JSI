<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Erp\Mapping;


class Warranty extends \Epicor\Common\Model\Erp\Mapping\AbstractModel
{

    /**
     * @var \Epicor\Dealerconnect\Model\ResourceModel\EccPacAttributesOption\CollectionFactory 
     */
    protected $WarrantyFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Dealerconnect\Model\ResourceModel\Warranty\CollectionFactory $WarrantyFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->warrantyFactory = $WarrantyFactory;
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
        $this->_init('Epicor\Dealerconnect\Model\ResourceModel\Warranty');
    }


}
